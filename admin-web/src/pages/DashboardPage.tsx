import { useQuery } from '@tanstack/react-query';
import api from '../services/api';
import type { Category, PaginatedData, Product } from '../types';

export function DashboardPage() {
  const categoriesQuery = useQuery({
    queryKey: ['admin-categories'],
    queryFn: () => api.get<{ data: Category[] }>('/categories').then((r) => r.data.data),
  });

  const productsQuery = useQuery({
    queryKey: ['admin-products-all'],
    queryFn: () =>
      api.get<{ data: PaginatedData<Product> }>('/products', { params: { per_page: 50 } }).then((r) => r.data.data),
  });

  const categories = categoriesQuery.data || [];
  const products = productsQuery.data?.items || [];
  const totalProducts = productsQuery.data?.pagination?.total ?? 0;
  const activeProducts = products.filter((p) => p.is_available).length;
  const unavailableProducts = products.filter((p) => !p.is_available).length;

  const stats = [
    { label: 'Total Products', value: totalProducts, icon: '🍔', color: 'var(--color-primary-red)' },
    { label: 'Active Products', value: activeProducts, icon: '✅', color: 'var(--color-success)' },
    { label: 'Categories', value: categories.length, icon: '📁', color: 'var(--color-warning)' },
    { label: 'Unavailable', value: unavailableProducts, icon: '⏸️', color: 'var(--color-text-muted)' },
  ];

  const isLoading = categoriesQuery.isLoading || productsQuery.isLoading;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-8">Dashboard</h1>

      {isLoading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="bg-[var(--color-surface)] rounded-xl p-6 border border-[var(--color-border)] animate-pulse">
              <div className="h-4 bg-[var(--color-surface-secondary)] rounded w-24 mb-4" />
              <div className="h-8 bg-[var(--color-surface-secondary)] rounded w-16" />
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {stats.map((stat) => (
            <div
              key={stat.label}
              className="bg-[var(--color-surface)] rounded-xl p-6 border border-[var(--color-border)] hover:border-[var(--color-primary-red)]/30 transition-colors"
            >
              <div className="flex items-center justify-between mb-4">
                <span className="text-sm text-[var(--color-text-secondary)]">{stat.label}</span>
                <span className="text-2xl">{stat.icon}</span>
              </div>
              <p className="text-3xl font-bold" style={{ color: stat.color }}>
                {stat.value}
              </p>
            </div>
          ))}
        </div>
      )}

      {/* Recent Products */}
      <div className="mt-10">
        <h2 className="text-lg font-semibold mb-4">Recent Products</h2>
        <div className="bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] overflow-hidden">
          <table className="w-full">
            <thead>
              <tr className="border-b border-[var(--color-border)]">
                <th className="text-left p-4 text-sm text-[var(--color-text-secondary)] font-medium">Name</th>
                <th className="text-left p-4 text-sm text-[var(--color-text-secondary)] font-medium">Category</th>
                <th className="text-left p-4 text-sm text-[var(--color-text-secondary)] font-medium">Price</th>
                <th className="text-left p-4 text-sm text-[var(--color-text-secondary)] font-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              {products.slice(0, 5).map((product) => (
                <tr key={product.id} className="border-b border-[var(--color-border)] last:border-0 hover:bg-[var(--color-surface-secondary)] transition-colors">
                  <td className="p-4 text-sm font-medium">{product.name}</td>
                  <td className="p-4 text-sm text-[var(--color-text-secondary)]">{product.category?.name}</td>
                  <td className="p-4 text-sm text-[var(--color-primary-red)] font-semibold">${product.price.toFixed(2)}</td>
                  <td className="p-4">
                    <span
                      className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${
                        product.is_available
                          ? 'bg-[var(--color-success)]/10 text-[var(--color-success)]'
                          : 'bg-[var(--color-primary-red)]/10 text-[var(--color-primary-red)]'
                      }`}
                    >
                      {product.is_available ? 'Available' : 'Unavailable'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
