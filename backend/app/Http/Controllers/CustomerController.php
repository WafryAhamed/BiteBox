<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $query = User::where('role', UserRole::CUSTOMER)
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total');

        if ($request->filled('search')) {
            $search = mb_strtolower($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderByDesc('created_at');
        $perPage = min((int) ($request->query('per_page') ?? 15), 50);
        $customers = $query->paginate($perPage);

        $items = collect($customers->items())->map(function ($customer) {
            $latestOrder = $customer->orders()->orderByDesc('created_at')->first();

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'created_at' => $customer->created_at->toISOString(),
                'orders_count' => (int) $customer->orders_count,
                'total_spent' => (float) ($customer->total_spent ?? 0.0),
                'latest_order' => $latestOrder ? [
                    'id' => $latestOrder->id,
                    'order_number' => $latestOrder->order_number,
                    'total' => (float) $latestOrder->total,
                    'order_status' => $latestOrder->order_status,
                    'created_at' => $latestOrder->created_at->toISOString(),
                ] : null,
            ];
        });

        return $this->success([
            'items' => $items,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ], 'Customers retrieved successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $customer = User::where('role', UserRole::CUSTOMER)
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->findOrFail($id);

        $orders = $customer->orders()
            ->with(['items.addons', 'address'])
            ->orderByDesc('created_at')
            ->get();

        return $this->success([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'created_at' => $customer->created_at->toISOString(),
                'orders_count' => (int) $customer->orders_count,
                'total_spent' => (float) ($customer->total_spent ?? 0.0),
            ],
            'orders' => OrderResource::collection($orders),
        ], 'Customer details retrieved successfully');
    }
}
