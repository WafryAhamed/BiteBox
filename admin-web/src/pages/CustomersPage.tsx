import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

interface CustomerItem {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  created_at: string;
  orders_count: number;
  total_spent: number;
  latest_order: {
    id: number;
    order_number: string;
    total: number;
    order_status: string;
    created_at: string;
  } | null;
}

interface PaginatedCustomers {
  items: CustomerItem[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export function CustomersPage() {
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery({
    queryKey: ['admin-customers', search, page],
    queryFn: () =>
      api
        .get<{ data: PaginatedCustomers }>('/customers', {
          params: {
            search: search || undefined,
            page,
            per_page: 15,
          },
        })
        .then((r) => r.data.data),
  });

  const customers = data?.items || [];
  const pagination = data?.pagination;

  return (
    <div>
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl font-bold">Customers</h1>
          <p className="text-sm text-[var(--color-text-secondary)] mt-1">
            Manage and view customer accounts
          </p>
        </div>
        {pagination && (
          <span className="text-xs text-[var(--color-text-muted)] self-start">
            {pagination.total} total customers
          </span>
        )}
      </div>

      {/* Search */}
      <div className="mb-6">
        <input
          type="text"
          placeholder="Search by name, email, or phone..."
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
          className="w-full sm:w-80"
        />
      </div>

      {/* Table */}
      <div className="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-[var(--color-border)]">
                <th className="text-left px-6 py-4 text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">
                  Customer
                </th>
                <th className="text-left px-6 py-4 text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">
                  Contact
                </th>
                <th className="text-center px-6 py-4 text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">
                  Orders
                </th>
                <th className="text-right px-6 py-4 text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">
                  Total Spent
                </th>
                <th className="text-left px-6 py-4 text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">
                  Latest Order
                </th>
                <th className="text-left px-6 py-4 text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">
                  Joined
                </th>
              </tr>
            </thead>
            <tbody>
              {isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <tr key={i} className="border-b border-[var(--color-border)] animate-pulse">
                    <td className="px-6 py-4"><div className="h-4 bg-[var(--color-surface-secondary)] rounded w-32" /></td>
                    <td className="px-6 py-4"><div className="h-4 bg-[var(--color-surface-secondary)] rounded w-40" /></td>
                    <td className="px-6 py-4"><div className="h-4 bg-[var(--color-surface-secondary)] rounded w-8 mx-auto" /></td>
                    <td className="px-6 py-4"><div className="h-4 bg-[var(--color-surface-secondary)] rounded w-16 ml-auto" /></td>
                    <td className="px-6 py-4"><div className="h-4 bg-[var(--color-surface-secondary)] rounded w-24" /></td>
                    <td className="px-6 py-4"><div className="h-4 bg-[var(--color-surface-secondary)] rounded w-20" /></td>
                  </tr>
                ))
              ) : customers.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-6 py-16 text-center text-[var(--color-text-muted)]">
                    <div className="text-3xl mb-2">👤</div>
                    <p className="font-medium">No customers found</p>
                    <p className="text-sm mt-1">Try adjusting your search query.</p>
                  </td>
                </tr>
              ) : (
                customers.map((customer) => (
                  <tr
                    key={customer.id}
                    className="border-b border-[var(--color-border)] hover:bg-[var(--color-surface-secondary)]/50 cursor-pointer transition-colors"
                    onClick={() => navigate(`/customers/${customer.id}`)}
                  >
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-full bg-[var(--color-primary-red)]/20 flex items-center justify-center text-sm font-bold text-[var(--color-primary-red)]">
                          {customer.name.charAt(0).toUpperCase()}
                        </div>
                        <span className="font-medium text-sm">{customer.name}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-sm">{customer.email}</div>
                      <div className="text-xs text-[var(--color-text-muted)]">{customer.phone || '—'}</div>
                    </td>
                    <td className="px-6 py-4 text-center">
                      <span className="text-sm font-semibold">{customer.orders_count}</span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <span className="text-sm font-semibold text-[var(--color-success)]">
                        Rs. {customer.total_spent.toLocaleString()}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      {customer.latest_order ? (
                        <span className="text-xs px-2 py-1 rounded border border-[var(--color-border)] bg-[var(--color-surface-secondary)]">
                          {customer.latest_order.order_number}
                        </span>
                      ) : (
                        <span className="text-xs text-[var(--color-text-muted)]">No orders</span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-sm text-[var(--color-text-secondary)]">
                      {new Date(customer.created_at).toLocaleDateString()}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {pagination && pagination.last_page > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t border-[var(--color-border)]">
            <span className="text-xs text-[var(--color-text-muted)]">
              Page {pagination.current_page} of {pagination.last_page}
            </span>
            <div className="flex gap-2">
              <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
                className="px-3 py-1.5 text-sm bg-[var(--color-surface-secondary)] rounded-lg border border-[var(--color-border)] disabled:opacity-40"
              >
                Previous
              </button>
              <button
                onClick={() => setPage((p) => Math.min(pagination.last_page, p + 1))}
                disabled={page === pagination.last_page}
                className="px-3 py-1.5 text-sm bg-[var(--color-surface-secondary)] rounded-lg border border-[var(--color-border)] disabled:opacity-40"
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
