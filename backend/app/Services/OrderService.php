<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    public const DEFAULT_DELIVERY_FEE = 200.00;

    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $orderType = OrderType::from($data['order_type']);
            $addressId = null;

            if ($orderType === OrderType::DELIVERY) {
                if (empty($data['address_id'])) {
                    throw new InvalidArgumentException('Delivery address is required for delivery orders.');
                }
                $address = Address::where('id', $data['address_id'])
                    ->where('user_id', $user->id)
                    ->first();
                if (!$address) {
                    throw new InvalidArgumentException('Invalid delivery address selected.');
                }
                $addressId = $address->id;
            }

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new InvalidArgumentException('Cart is empty.');
            }

            $calculatedSubtotal = 0.0;
            $preparedItems = [];

            foreach ($itemsData as $itemInput) {
                $product = Product::find($itemInput['product_id']);
                if (!$product) {
                    throw new InvalidArgumentException("Product #{$itemInput['product_id']} not found.");
                }

                if (!$product->is_available) {
                    throw new RuntimeException("Product '{$product->name}' is currently unavailable.");
                }

                $quantity = (int) $itemInput['quantity'];
                if ($quantity <= 0) {
                    throw new InvalidArgumentException("Invalid quantity for product '{$product->name}'.");
                }

                $baseUnitPrice = (float) $product->price;
                $addonUnitPriceSum = 0.0;
                $preparedAddons = [];

                if (!empty($itemInput['addon_ids'])) {
                    $addons = ProductAddon::whereIn('id', $itemInput['addon_ids'])
                        ->where('product_id', $product->id)
                        ->get();

                    foreach ($itemInput['addon_ids'] as $addonId) {
                        $addon = $addons->firstWhere('id', $addonId);
                        if (!$addon) {
                            throw new InvalidArgumentException("Invalid add-on #{$addonId} for product '{$product->name}'.");
                        }
                        if (!$addon->is_available) {
                            throw new RuntimeException("Add-on '{$addon->name}' is currently unavailable.");
                        }

                        $addonUnitPriceSum += (float) $addon->price;
                        $preparedAddons[] = [
                            'product_addon_id' => $addon->id,
                            'addon_name' => $addon->name,
                            'addon_price' => (float) $addon->price,
                        ];
                    }
                }

                $unitPrice = $baseUnitPrice + $addonUnitPriceSum;
                $itemSubtotal = $unitPrice * $quantity;
                $calculatedSubtotal += $itemSubtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'special_instruction' => $itemInput['special_instruction'] ?? null,
                    'addons' => $preparedAddons,
                ];
            }

            $deliveryFee = ($orderType === OrderType::DELIVERY) ? self::DEFAULT_DELIVERY_FEE : 0.0;
            $total = $calculatedSubtotal + $deliveryFee;

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $addressId,
                'order_number' => Order::generateOrderNumber(),
                'order_type' => $orderType,
                'payment_method' => PaymentMethod::CASH,
                'payment_status' => PaymentStatus::PENDING,
                'order_status' => OrderStatus::PENDING,
                'subtotal' => $calculatedSubtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'special_instruction' => $data['special_instruction'] ?? null,
            ]);

            foreach ($preparedItems as $itemData) {
                $addons = $itemData['addons'];
                unset($itemData['addons']);

                $orderItem = $order->items()->create($itemData);

                foreach ($addons as $addonData) {
                    $orderItem->addons()->create($addonData);
                }
            }

            return $order->load(['user', 'address', 'items.addons']);
        });
    }

    public function getAll(array $filters = [], ?User $user = null): LengthAwarePaginator
    {
        $query = Order::with(['user', 'address', 'items.addons']);

        // Scope to user if non-admin
        if ($user && !$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Search order_number or customer name
        if (!empty($filters['search'])) {
            $search = mb_strtolower($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(order_number) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        // Order type filter
        if (!empty($filters['order_type'])) {
            $query->where('order_type', $filters['order_type']);
        }

        // Date filter
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        $query->orderByDesc('created_at');
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }

    public function findOrFail(int $id, ?User $user = null): Order
    {
        $query = Order::with(['user', 'address', 'items.addons']);

        if ($user && !$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->findOrFail($id);
    }

    public function cancelOrder(Order $order, User $user): Order
    {
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            throw new InvalidArgumentException('Unauthorized to cancel this order.');
        }

        $currentStatus = $order->order_status instanceof OrderStatus
            ? $order->order_status
            : OrderStatus::from($order->order_status);

        if (!$currentStatus->canCustomerCancel() && !$user->isAdmin()) {
            throw new RuntimeException('Order cannot be cancelled at this stage.');
        }

        $order->update([
            'order_status' => OrderStatus::CANCELLED,
        ]);

        return $order->fresh(['user', 'address', 'items.addons']);
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        $currentStatus = $order->order_status instanceof OrderStatus
            ? $order->order_status
            : OrderStatus::from($order->order_status);

        $allowed = $currentStatus->allowedTransitions();
        if (!in_array($newStatus, $allowed, true)) {
            throw new InvalidArgumentException(
                "Cannot transition order status from {$currentStatus->value} to {$newStatus->value}."
            );
        }

        $data = ['order_status' => $newStatus];

        // Auto-mark payment as PAID when completed
        if ($newStatus === OrderStatus::COMPLETED) {
            $data['payment_status'] = PaymentStatus::PAID;
        }

        $order->update($data);

        return $order->fresh(['user', 'address', 'items.addons']);
    }

    public function getDashboardStats(): array
    {
        $today = now()->toDateString();

        $todayOrders = Order::whereDate('created_at', $today);
        $todayCount = (clone $todayOrders)->count();
        $todayRevenue = (clone $todayOrders)->where('order_status', '!=', OrderStatus::CANCELLED->value)->sum('total');

        $pendingCount = Order::where('order_status', OrderStatus::PENDING->value)->count();
        $preparingCount = Order::where('order_status', OrderStatus::PREPARING->value)->count();
        $completedCount = Order::where('order_status', OrderStatus::COMPLETED->value)->count();

        $recentOrders = Order::with(['user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Top products calculation
        $topProducts = \App\Models\OrderItem::select('product_name', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_quantity'), \Illuminate\Support\Facades\DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name' => $item->product_name,
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ]);

        return [
            'today_orders' => $todayCount,
            'today_revenue' => (float) $todayRevenue,
            'pending_orders' => $pendingCount,
            'preparing_orders' => $preparingCount,
            'completed_orders' => $completedCount,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ];
    }
}
