import { useQuery } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';
import type { DashboardStats, OrderStatus } from '../types';

export function DashboardPage() {
  const navigate = useNavigate();

  const { data: stats, isLoading } = useQuery({
    queryKey: ['admin-dashboard-stats'],
    queryFn: () => api.get<{ data: DashboardStats }>('/orders/stats').then((r) => r.data.data),
    refetchInterval: 10000,
  });

  const getStatusBadge = (status: OrderStatus) => {
    switch (status) {
      case 'PENDING':
        return 'bg-[var(--color-warning)]/10 text-[var(--color-warning)]';
      case 'CONFIRMED':
        return 'bg-blue-500/10 text-blue-400';
      case 'PREPARING':
        return 'bg-[var(--color-primary-red)]/10 text-[var(--color-primary-red)]';
      case 'READY':
        return 'bg-[var(--color-success)]/10 text-[var(--color-success)]';
      case 'COMPLETED':
        return 'bg-gray-500/10 text-gray-400';
      case 'CANCELLED':
        return 'bg-red-900/20 text-red-400';
      default:
        return 'bg-gray-500/10 text-gray-400';
    }
  };

  const statCards = [
    {
      label: "Today's Orders",
      value: stats?.today_orders ?? 0,
      color: 'var(--color-primary-red)',
    },
    {
      label: "Today's Revenue",
      value: `Rs. ${(stats?.today_revenue ?? 0).toLocaleString()}`,
      color: 'var(--color-success)',
    },
    {
      label: 'Pending Orders',
      value: stats?.pending_orders ?? 0,
      color: 'var(--color-warning)',
    },
    {
      label: 'Preparing Orders',
      value: stats?.preparing_orders ?? 0,
      color: 'var(--color-primary-red)',
    },
    {
      label: 'Completed Orders',
      value: stats?.completed_orders ?? 0,
      color: 'var(--color-success)',
    },
  ];

  return (
    <div>
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold">Dashboard</h1>
          <p className="text-sm text-[var(--color-text-secondary)] mt-1">
            Real-time BiteBox order metrics and store overview
          </p>
        </div>
      </div>

      {isLoading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div
              key={i}
              className="bg-[var(--color-surface)] rounded-xl p-5 border border-[var(--color-border)] animate-pulse"
            >
              <div className="h-4 bg-[var(--color-surface-secondary)] rounded w-20 mb-4" />
              <div className="h-8 bg-[var(--color-surface-secondary)] rounded w-16" />
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          {statCards.map((stat) => (
            <div
              key={stat.label}
              className="bg-[var(--color-surface)] rounded-xl p-5 border border-[var(--color-border)] hover:border-[var(--color-primary-red)]/30 transition-all"
            >
              <p className="text-xs font-medium text-[var(--color-text-secondary)] mb-3">
                {stat.label}
              </p>
              <p className="text-2xl font-bold" style={{ color: stat.color }}>
                {stat.value}
              </p>
            </div>
          ))}
        </div>
      )}

      {/* Recent Orders */}
      <div className="mt-10">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold">Recent Orders</h2>
          <button
            onClick={() => navigate('/orders')}
            className="text-xs text-[var(--color-primary-red)] font-semibold hover:underline"
          >
            View All Orders →
          </button>
        </div>

        <div className="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">
          {!stats || stats.recent_orders.length === 0 ? (
            <div className="p-8 text-center text-[var(--color-text-muted)] text-sm">
              No recent orders placed today.
            </div>
          ) : (
            <table className="w-full text-left">
              <thead>
                <tr className="border-b border-[var(--color-border)] bg-[var(--color-surface-secondary)] text-xs text-[var(--color-text-secondary)] uppercase">
                  <th className="p-4 font-semibold">Order #</th>
                  <th className="p-4 font-semibold">Customer</th>
                  <th className="p-4 font-semibold">Amount</th>
                  <th className="p-4 font-semibold">Status</th>
                  <th className="p-4 font-semibold">Time</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[var(--color-border)] text-sm">
                {stats.recent_orders.map((order) => (
                  <tr
                    key={order.id}
                    onClick={() => navigate(`/orders/${order.id}`)}
                    className="hover:bg-[var(--color-surface-secondary)]/50 cursor-pointer transition-colors"
                  >
                    <td className="p-4 font-bold text-[var(--color-primary-red)]">
                      #{order.order_number}
                    </td>
                    <td className="p-4 font-medium">{order.user?.name || 'Customer'}</td>
                    <td className="p-4 font-bold">Rs. {Number(order.total).toLocaleString()}</td>
                    <td className="p-4">
                      <span
                        className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${getStatusBadge(
                          order.order_status
                        )}`}
                      >
                        {order.order_status}
                      </span>
                    </td>
                    <td className="p-4 text-xs text-[var(--color-text-muted)]">
                      {new Date(order.created_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>

      {/* Top Products */}
      {stats?.top_products && stats.top_products.length > 0 && (
        <div className="mt-10">
          <h2 className="text-lg font-semibold mb-4">Top Products</h2>
          <div className="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] divide-y divide-[var(--color-border)]">
            {stats.top_products.map((product, i) => {
              const maxQty = stats.top_products![0].total_quantity;
              const pct = maxQty > 0 ? (product.total_quantity / maxQty) * 100 : 0;
              return (
                <div key={product.name} className="flex items-center gap-4 px-6 py-4">
                  <span className="w-6 text-center text-xs font-bold text-[var(--color-text-muted)]">
                    {i + 1}
                  </span>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{product.name}</p>
                    <div className="mt-1.5 h-1.5 rounded-full bg-[var(--color-surface-secondary)] overflow-hidden">
                      <div
                        className="h-full rounded-full bg-[var(--color-primary-red)] transition-all"
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-bold">{product.total_quantity} sold</p>
                    <p className="text-xs text-[var(--color-text-muted)]">
                      Rs. {product.total_revenue.toLocaleString()}
                    </p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
