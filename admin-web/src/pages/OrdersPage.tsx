import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';
import type { Order, OrderStatus, PaginatedData } from '../types';

export function OrdersPage() {
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<string>('');
  const [date, setDate] = useState<string>('');
  const [page, setPage] = useState(1);

  const { data, isLoading, refetch } = useQuery({
    queryKey: ['admin-orders', search, status, date, page],
    queryFn: () =>
      api
        .get<{ data: PaginatedData<Order> }>('/orders', {
          params: {
            search: search || undefined,
            status: status || undefined,
            date: date || undefined,
            page,
            per_page: 15,
          },
        })
        .then((r) => r.data.data),
    refetchInterval: 10000, // Refetch every 10 seconds for real-time order monitoring
  });

  const orders = data?.items || [];
  const pagination = data?.pagination;

  const getStatusBadge = (orderStatus: OrderStatus) => {
    switch (orderStatus) {
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

  return (
    <div>
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl font-bold">Order Management</h1>
          <p className="text-sm text-[var(--color-text-secondary)] mt-1">
            Monitor and update live customer orders
          </p>
        </div>
        <button
          onClick={() => refetch()}
          className="px-4 py-2 bg-[var(--color-surface)] border border-[var(--color-border)] rounded-lg text-sm hover:border-[var(--color-primary-red)] transition-colors flex items-center gap-2 self-start"
        >
          Refresh Orders
        </button>
      </div>

      {/* Filters */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div>
          <input
            type="text"
            placeholder="Search order # or customer name..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            className="w-full"
          />
        </div>

        <div>
          <select
            value={status}
            onChange={(e) => {
              setStatus(e.target.value);
              setPage(1);
            }}
            className="w-full"
          >
            <option value="">All Statuses</option>
            <option value="PENDING">PENDING</option>
            <option value="CONFIRMED">CONFIRMED</option>
            <option value="PREPARING">PREPARING</option>
            <option value="READY">READY</option>
            <option value="COMPLETED">COMPLETED</option>
            <option value="CANCELLED">CANCELLED</option>
          </select>
        </div>

        <div>
          <input
            type="date"
            value={date}
            onChange={(e) => {
              setDate(e.target.value);
              setPage(1);
            }}
            className="w-full"
          />
        </div>
      </div>

      {/* Table */}
      <div className="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-[var(--color-text-secondary)]">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[var(--color-primary-red)] mb-2" />
            <p>Loading orders...</p>
          </div>
        ) : orders.length === 0 ? (
          <div className="p-12 text-center text-[var(--color-text-muted)]">
            <p className="text-lg font-medium text-[var(--color-text-primary)]">No orders found</p>
            <p className="text-sm mt-1">Try resetting your filters or check back later.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className="border-b border-[var(--color-border)] bg-[var(--color-surface-secondary)] text-xs text-[var(--color-text-secondary)] uppercase tracking-wider">
                  <th className="p-4 font-semibold">Order #</th>
                  <th className="p-4 font-semibold">Customer</th>
                  <th className="p-4 font-semibold">Type</th>
                  <th className="p-4 font-semibold">Total</th>
                  <th className="p-4 font-semibold">Payment</th>
                  <th className="p-4 font-semibold">Status</th>
                  <th className="p-4 font-semibold">Created At</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[var(--color-border)]">
                {orders.map((order) => {
                  const createdAt = new Date(order.created_at).toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  });

                  return (
                    <tr
                      key={order.id}
                      onClick={() => navigate(`/orders/${order.id}`)}
                      className="hover:bg-[var(--color-surface-secondary)]/50 cursor-pointer transition-colors"
                    >
                      <td className="p-4 font-bold text-[var(--color-primary-red)]">
                        #{order.order_number}
                      </td>
                      <td className="p-4">
                        <div className="font-medium text-sm">{order.user?.name || 'Customer'}</div>
                        <div className="text-xs text-[var(--color-text-muted)]">{order.user?.email}</div>
                      </td>
                      <td className="p-4 text-sm font-medium">
                        <span
                          className={`inline-flex items-center px-2 py-0.5 rounded text-xs ${
                            order.order_type === 'DELIVERY'
                              ? 'bg-purple-900/30 text-purple-300'
                              : 'bg-emerald-900/30 text-emerald-300'
                          }`}
                        >
                          {order.order_type}
                        </span>
                      </td>
                      <td className="p-4 font-bold text-sm">
                        Rs. {Number(order.total).toLocaleString()}
                      </td>
                      <td className="p-4 text-xs text-[var(--color-text-secondary)]">
                        {order.payment_method} ({order.payment_status})
                      </td>
                      <td className="p-4">
                        <span
                          className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border ${getStatusBadge(
                            order.order_status
                          )}`}
                        >
                          {order.order_status}
                        </span>
                      </td>
                      <td className="p-4 text-xs text-[var(--color-text-muted)]">
                        {createdAt}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination */}
        {pagination && pagination.last_page > 1 && (
          <div className="flex items-center justify-between p-4 border-t border-[var(--color-border)]">
            <span className="text-xs text-[var(--color-text-secondary)]">
              Showing page {pagination.current_page} of {pagination.last_page} ({pagination.total} total)
            </span>
            <div className="flex gap-2">
              <button
                disabled={page <= 1}
                onClick={() => setPage((p) => p - 1)}
                className="px-3 py-1 bg-[var(--color-surface-secondary)] border border-[var(--color-border)] rounded text-xs disabled:opacity-50"
              >
                Previous
              </button>
              <button
                disabled={page >= pagination.last_page}
                onClick={() => setPage((p) => p + 1)}
                className="px-3 py-1 bg-[var(--color-surface-secondary)] border border-[var(--color-border)] rounded text-xs disabled:opacity-50"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
