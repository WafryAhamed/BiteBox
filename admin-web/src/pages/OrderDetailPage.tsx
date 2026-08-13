import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '../services/api';
import type { Order, OrderStatus } from '../types';

const ALLOWED_TRANSITIONS: Record<OrderStatus, OrderStatus[]> = {
  PENDING: ['CONFIRMED', 'CANCELLED'],
  CONFIRMED: ['PREPARING', 'CANCELLED'],
  PREPARING: ['READY'],
  READY: ['COMPLETED'],
  COMPLETED: [],
  CANCELLED: [],
};

export function OrderDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [errorMsg, setErrorMsg] = useState('');

  const { data: order, isLoading, isError } = useQuery({
    queryKey: ['admin-order', id],
    queryFn: () => api.get<{ data: Order }>(`/orders/${id}`).then((r) => r.data.data),
    refetchInterval: 5000,
  });

  const updateStatusMutation = useMutation({
    mutationFn: (newStatus: OrderStatus) =>
      api.patch<{ data: Order }>(`/orders/${id}/status`, { order_status: newStatus }),
    onSuccess: () => {
      setErrorMsg('');
      queryClient.invalidateQueries({ queryKey: ['admin-order', id] });
      queryClient.invalidateQueries({ queryKey: ['admin-orders'] });
    },
    onError: (err: any) => {
      setErrorMsg(err.response?.data?.message || 'Failed to update order status');
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64 text-[var(--color-text-secondary)]">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[var(--color-primary-red)] mr-3" />
        Loading order detail...
      </div>
    );
  }

  if (isError || !order) {
    return (
      <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-8 text-center">
        <p className="text-red-400 mb-4 font-semibold">Order not found or failed to load.</p>
        <button
          onClick={() => navigate('/orders')}
          className="px-4 py-2 bg-[var(--color-surface-secondary)] border border-[var(--color-border)] rounded-lg text-sm"
        >
          ← Back to Orders
        </button>
      </div>
    );
  }

  const allowedNextStatuses = ALLOWED_TRANSITIONS[order.order_status] || [];

  return (
    <div className="max-w-5xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <button
            onClick={() => navigate('/orders')}
            className="text-xs text-[var(--color-text-secondary)] hover:text-white mb-2 flex items-center gap-1"
          >
            ← Back to Orders List
          </button>
          <div className="flex items-center gap-3">
            <h1 className="text-2xl font-bold">Order #{order.order_number}</h1>
            <span
              className={`px-3 py-1 rounded-full text-xs font-bold ${
                order.order_status === 'COMPLETED'
                  ? 'bg-emerald-900/30 text-emerald-400 border border-emerald-800/30'
                  : order.order_status === 'CANCELLED'
                  ? 'bg-red-900/30 text-red-400 border border-red-800/30'
                  : 'bg-[var(--color-primary-red)]/20 text-[var(--color-primary-red)] border border-[var(--color-primary-red)]/30'
              }`}
            >
              {order.order_status}
            </span>
          </div>
          <p className="text-xs text-[var(--color-text-muted)] mt-1">
            Placed on {new Date(order.created_at).toLocaleString()}
          </p>
        </div>

        {/* Action Controls */}
        <div className="flex items-center gap-2">
          {allowedNextStatuses.map((nextStatus) => {
            const isDestructive = nextStatus === 'CANCELLED';
            return (
              <button
                key={nextStatus}
                disabled={updateStatusMutation.isPending}
                onClick={() => updateStatusMutation.mutate(nextStatus)}
                className={`px-4 py-2 rounded-lg text-sm font-semibold transition-all disabled:opacity-50 ${
                  isDestructive
                    ? 'bg-red-900/30 text-red-400 border border-red-800/40 hover:bg-red-900/50'
                    : 'bg-[var(--color-primary-red)] text-white hover:bg-[var(--color-dark-red)]'
                }`}
              >
                Mark as {nextStatus}
              </button>
            );
          })}
        </div>
      </div>

      {errorMsg && (
        <div className="bg-red-900/20 border border-red-800/40 text-red-300 p-4 rounded-xl text-sm mb-6">
          {errorMsg}
        </div>
      )}

      {/* Main Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left 2 Cols: Order Items & Customer/Address */}
        <div className="lg:col-span-2 space-y-6">
          {/* Order Items */}
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-6">
            <h2 className="text-lg font-semibold mb-4 border-b border-[var(--color-border)] pb-3">
              Ordered Items
            </h2>
            <div className="space-y-4">
              {(order.items || []).map((item) => (
                <div
                  key={item.id}
                  className="flex items-start justify-between border-b border-[var(--color-border)] last:border-0 pb-4 last:pb-0"
                >
                  <div>
                    <p className="font-semibold text-sm">
                      {item.quantity}× {item.product_name}
                    </p>
                    {item.addons && item.addons.length > 0 && (
                      <div className="text-xs text-[var(--color-primary-red)] mt-1">
                        + {item.addons.map((a) => `${a.addon_name} (Rs. ${a.addon_price})`).join(', ')}
                      </div>
                    )}
                    {item.special_instruction && (
                      <p className="text-xs text-[var(--color-text-muted)] italic mt-1">
                        Note: "{item.special_instruction}"
                      </p>
                    )}
                  </div>
                  <p className="font-bold text-sm text-right">
                    Rs. {Number(item.subtotal).toLocaleString()}
                  </p>
                </div>
              ))}
            </div>
          </div>

          {/* Special Instructions */}
          {order.special_instruction && (
            <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-6">
              <h2 className="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-2">
                Order Special Instructions
              </h2>
              <p className="text-sm bg-[var(--color-surface-secondary)] p-3 rounded-lg border border-[var(--color-border)] italic">
                "{order.special_instruction}"
              </p>
            </div>
          )}
        </div>

        {/* Right Col: Customer, Address & Financial Breakdown */}
        <div className="space-y-6">
          {/* Customer Card */}
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-6">
            <h2 className="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-4 border-b border-[var(--color-border)] pb-2">
              Customer Details
            </h2>
            <div className="space-y-2 text-sm">
              <p className="font-bold text-base">{order.user?.name || 'Customer'}</p>
              <p className="text-[var(--color-text-secondary)]">{order.user?.email}</p>
              <p className="text-[var(--color-text-secondary)]">{order.user?.phone || 'No phone provided'}</p>
            </div>
          </div>

          {/* Delivery Address */}
          {order.address && (
            <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-6">
              <h2 className="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-4 border-b border-[var(--color-border)] pb-2">
                Delivery Address
              </h2>
              <div className="space-y-1 text-sm">
                <p className="font-semibold">{order.address.label} • {order.address.full_name}</p>
                <p className="text-[var(--color-text-secondary)]">{order.address.phone}</p>
                <p className="text-[var(--color-text-secondary)]">{order.address.address_line}, {order.address.city}</p>
              </div>
            </div>
          )}

          {/* Payment & Financial Summary */}
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-6">
            <h2 className="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-4 border-b border-[var(--color-border)] pb-2">
              Financial Summary
            </h2>
            <div className="space-y-2 text-sm mb-4">
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Order Type</span>
                <span className="font-semibold">{order.order_type}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Payment Method</span>
                <span className="font-semibold">{order.payment_method}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Payment Status</span>
                <span className="font-semibold">{order.payment_status}</span>
              </div>
              <hr className="border-[var(--color-border)] my-2" />
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Subtotal</span>
                <span>Rs. {Number(order.subtotal).toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Delivery Fee</span>
                <span>Rs. {Number(order.delivery_fee).toLocaleString()}</span>
              </div>
              <hr className="border-[var(--color-border)] my-2" />
              <div className="flex justify-between text-base font-bold">
                <span>Total Amount</span>
                <span className="text-[var(--color-primary-red)]">
                  Rs. {Number(order.total).toLocaleString()}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
