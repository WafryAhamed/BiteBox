<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->user(), $request->validated());

            return $this->created(
                new OrderResource($order),
                'Order placed successfully'
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'order_type', 'date', 'per_page']);
        $orders = $this->orderService->getAll($filters, $request->user());

        return $this->success([
            'items' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 'Orders retrieved successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->findOrFail($id, $request->user());

            return $this->success(
                new OrderResource($order),
                'Order retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->error('Order not found', 404);
        }
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->findOrFail($id, $request->user());
            $updatedOrder = $this->orderService->cancelOrder($order, $request->user());

            return $this->success(
                new OrderResource($updatedOrder),
                'Order cancelled successfully'
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->error('Failed to cancel order', 500);
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return $this->error('Unauthorized to update order status', 403);
        }

        try {
            $order = $this->orderService->findOrFail($id);
            $rawStatus = $request->validated()['order_status'] ?? $request->validated()['status'];
            $newStatus = OrderStatus::from($rawStatus);

            $updatedOrder = $this->orderService->updateStatus($order, $newStatus);

            return $this->success(
                new OrderResource($updatedOrder),
                'Order status updated successfully'
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->error('Failed to update order status', 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        if (!$request->user()?->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $stats = $this->orderService->getDashboardStats();

        return $this->success([
            'today_orders' => $stats['today_orders'],
            'today_revenue' => $stats['today_revenue'],
            'pending_orders' => $stats['pending_orders'],
            'preparing_orders' => $stats['preparing_orders'],
            'completed_orders' => $stats['completed_orders'],
            'top_products' => $stats['top_products'],
            'recent_orders' => OrderResource::collection($stats['recent_orders']),
        ], 'Dashboard stats retrieved successfully');
    }
}
