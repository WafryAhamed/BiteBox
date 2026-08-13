import { useParams, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import api from '../services/api';
import type { Order, OrderStatus } from '../types';

interface CustomerDetail {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  created_at: string;
  orders_count: number;
  total_spent: number;
}

export function CustomerDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-customer', id],
    queryFn: () =>
      api
        .get<{ data: { customer: CustomerDetail; orders: Order[] } }>(`/customers/${id}`)
        .then((r) => r.data.data),
    enabled: !!id,
  });

  const customer = data?.customer;
  const orders = data?.orders || [];

  const getStatusBadge = (status: OrderStatus) => {
    switch (status) {
      case 'PENDING':
        return 'bg-[var(--color-warning)]/10 text-[var(--color-warning)] border-[var(--color-warning)]/30';
      case 'CONFIRMED':
        return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
      case 'PREPARING':
        return 'bg-[var(--color-primary-red)]/10 text-[var(--color-primary-red)] border-[var(--color-primary-red)]/30';
      case 'READY':
        return 'bg-[var(--color-success)]/10 text-[var(--color-success)] border-[var(--color-success)]/30';
      case 'COMPLETED':
        return 'bg-gray-500/10 text-gray-400 border-gray-500/30';
      case 'CANCELLED':
        return 'bg-red-900/20 text-red-400 border-red-800/30';
      default:
        return 'bg-gray-500/10 text-gray-400';
    }
  };

  if (isLoading) {
    return (
      <div className="animate-pulse space-y-6">
        <div className="h-6 bg-[var(--color-surface-secondary)] rounded w-48" />
        <div className="grid grid-cols-4 gap-4">
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="bg-[var(--color-surface)] rounded-xl p-6 border border-[var(--color-border)]">
              <div className="h-4 bg-[var(--color-surface-secondary)] rounded w-20 mb-3" />
              <div className="h-6 bg-[var(--color-surface-secondary)] rounded w-24" />
            </div>
          ))}
        </div>
      </div>
    );
  }

  if (!customer) {
    return (
      <div className="text-center py-16 text-[var(--color-text-muted)]">
        <p className="text-xl font-medium">Customer not found</p>
        <button onClick={() => navigate('/customers')} className="mt-4 text-sm text-[var(--color-primary-red)]">
          ← Back to customers
        </button>
      </div>
    );
  }

  return (
    <div>
      {/* Header */}
      <div className="flex items-center gap-4 mb-8">
        <button
          onClick={() => navigate('/customers')}
          className="px-3 py-2 bg-[var(--color-surface)] border border-[var(--color-border)] rounded-lg text-sm hover:border-[var(--color-primary-red)] transition-colors"
        >
          ← Back
        </button>
        <div>
          <h1 className="text-2xl font-bold">{customer.name}</h1>
          <p className="text-sm text-[var(--color-text-secondary)]">{customer.email}</p>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div className="bg-[var(--color-surface)] rounded-xl p-5 border border-[var(--color-border)]">
          <p className="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-2">Phone</p>
          <p className="text-lg font-semibold">{customer.phone || '—'}</p>
        </div>
        <div className="bg-[var(--color-surface)] rounded-xl p-5 border border-[var(--color-border)]">
          <p className="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-2">Total Orders</p>
          <p className="text-lg font-semibold">{customer.orders_count}</p>
        </div>
        <div className="bg-[var(--color-surface)] rounded-xl p-5 border border-[var(--color-border)]">
          <p className="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-2">Total Spent</p>
          <p className="text-lg font-semibold text-[var(--color-success)]">Rs. {customer.total_spent.toLocaleString()}</p>
        </div>
        <div className="bg-[var(--color-surface)] rounded-xl p-5 border border-[var(--color-border)]">
          <p className="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-2">Member Since</p>
          <p className="text-lg font-semibold">{new Date(customer.created_at).toLocaleDateString()}</p>
        </div>
      </div>

      {/* Order History */}
      <div className="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">
        <div className="px-6 py-4 border-b border-[var(--color-border)]">
          <h2 className="font-semibold">Order History</h2>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-[var(--color-border)]">
                <th className="text-left px-6 py-3 text-xs font-semibold text-[var(--color-text-muted)] uppercase">Order #</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-[var(--color-text-muted)] uppercase">Type</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-[var(--color-text-muted)] uppercase">Status</th>
                <th className="text-right px-6 py-3 text-xs font-semibold text-[var(--color-text-muted)] uppercase">Total</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-[var(--color-text-muted)] uppercase">Date</th>
              </tr>
            </thead>
            <tbody>
              {orders.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-6 py-12 text-center text-[var(--color-text-muted)]">
                    This customer has no orders yet.
                  </td>
                </tr>
              ) : (
                orders.map((order) => (
                  <tr
                    key={order.id}
                    className="border-b border-[var(--color-border)] hover:bg-[var(--color-surface-secondary)]/50 cursor-pointer transition-colors"
                    onClick={() => navigate(`/orders/${order.id}`)}
                  >
                    <td className="px-6 py-4">
                      <span className="font-mono text-sm font-semibold text-[var(--color-primary-red)]">
                        {order.order_number}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-xs px-2 py-1 rounded bg-[var(--color-surface-secondary)] border border-[var(--color-border)]">
                        {order.order_type}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <span className={`text-xs px-2 py-1 rounded border font-medium ${getStatusBadge(order.order_status)}`}>
                        ● {order.order_status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right font-semibold text-sm">
                      Rs. {Number(order.total).toLocaleString()}
                    </td>
                    <td className="px-6 py-4 text-sm text-[var(--color-text-secondary)]">
                      {new Date(order.created_at).toLocaleString()}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
