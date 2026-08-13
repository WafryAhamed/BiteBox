import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '../services/api';
import type { Category, PaginatedData, Product } from '../types';

export function ProductsPage() {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('');
  const [availabilityFilter, setAvailabilityFilter] = useState<string>('');
  const [page, setPage] = useState(1);

  // Modal state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [deleteConfirmId, setDeleteConfirmId] = useState<number | null>(null);

  // Form state
  const [categoryId, setCategoryId] = useState<number | ''>('');
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [image, setImage] = useState('');
  const [price, setPrice] = useState('');
  const [prepTime, setPrepTime] = useState('10');
  const [isAvailable, setIsAvailable] = useState(true);
  const [error, setError] = useState('');

  const categoriesQuery = useQuery({
    queryKey: ['admin-categories'],
    queryFn: () => api.get<{ data: Category[] }>('/categories').then((r) => r.data.data),
  });

  const productsQuery = useQuery({
    queryKey: ['admin-products', search, selectedCategory, availabilityFilter, page],
    queryFn: () =>
      api
        .get<{ data: PaginatedData<Product> }>('/products', {
          params: {
            search: search || undefined,
            category_id: selectedCategory || undefined,
            is_available: availabilityFilter !== '' ? availabilityFilter : undefined,
            page,
            per_page: 10,
          },
        })
        .then((r) => r.data.data),
  });

  const saveMutation = useMutation({
    mutationFn: (data: any) => {
      if (editingProduct) {
        return api.put(`/products/${editingProduct.id}`, data);
      }
      return api.post('/products', data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-products'] });
      closeModal();
    },
    onError: (err: any) => {
      setError(err.response?.data?.message || 'Failed to save product');
    },
  });

  const toggleAvailabilityMutation = useMutation({
    mutationFn: (id: number) => api.patch(`/products/${id}/toggle-availability`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-products'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => api.delete(`/products/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-products'] });
      setDeleteConfirmId(null);
    },
  });

  const openCreateModal = () => {
    setEditingProduct(null);
    setCategoryId(categoriesQuery.data?.[0]?.id || '');
    setName('');
    setDescription('');
    setImage('');
    setPrice('');
    setPrepTime('10');
    setIsAvailable(true);
    setError('');
    setIsModalOpen(true);
  };

  const openEditModal = (product: Product) => {
    setEditingProduct(product);
    setCategoryId(product.category_id);
    setName(product.name);
    setDescription(product.description || '');
    setImage(product.image || '');
    setPrice(product.price.toString());
    setPrepTime(product.preparation_time.toString());
    setIsAvailable(product.is_available);
    setError('');
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setEditingProduct(null);
    setError('');
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !price || !categoryId) {
      setError('Name, category, and price are required');
      return;
    }
    saveMutation.mutate({
      category_id: Number(categoryId),
      name: name.trim(),
      description: description.trim() || undefined,
      image: image.trim() || undefined,
      price: parseFloat(price),
      preparation_time: parseInt(prepTime) || 10,
      is_available: isAvailable,
    });
  };

  const categories = categoriesQuery.data || [];
  const products = productsQuery.data?.items || [];
  const pagination = productsQuery.data?.pagination;

  return (
    <div>
      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="text-2xl font-bold">Products</h1>
          <p className="text-sm text-[var(--color-text-secondary)] mt-1">Manage food products & availability</p>
        </div>
        <button
          onClick={openCreateModal}
          className="px-4 py-2.5 bg-[var(--color-primary-red)] hover:bg-[var(--color-light-red)] text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
        >
          <span>+</span> Add Product
        </button>
      </div>

      {/* Search & Filters */}
      <div className="flex flex-wrap gap-4 mb-6">
        <input
          type="text"
          placeholder="Search products..."
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPage(1); }}
          className="w-64"
        />

        <select
          value={selectedCategory}
          onChange={(e) => { setSelectedCategory(e.target.value); setPage(1); }}
          className="w-48"
        >
          <option value="">All Categories</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.name}
            </option>
          ))}
        </select>

        <select
          value={availabilityFilter}
          onChange={(e) => { setAvailabilityFilter(e.target.value); setPage(1); }}
          className="w-44"
        >
          <option value="">All Statuses</option>
          <option value="true">Available</option>
          <option value="false">Unavailable</option>
        </select>
      </div>

      {productsQuery.isLoading ? (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-8 text-center text-[var(--color-text-muted)]">
          Loading products...
        </div>
      ) : productsQuery.isError ? (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-8 text-center">
          <p className="text-[var(--color-primary-red)] mb-4">Failed to load products</p>
          <button onClick={() => productsQuery.refetch()} className="px-4 py-2 bg-[var(--color-surface-secondary)] rounded-lg text-sm">
            Retry
          </button>
        </div>
      ) : products.length === 0 ? (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-12 text-center text-[var(--color-text-secondary)]">
          No products found.
        </div>
      ) : (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl overflow-hidden">
          <table className="w-full">
            <thead>
              <tr className="border-b border-[var(--color-border)] text-left">
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Product</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Category</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Price</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Prep Time</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Availability</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)] text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {products.map((product) => (
                <tr key={product.id} className="border-b border-[var(--color-border)] last:border-0 hover:bg-[var(--color-surface-secondary)] transition-colors">
                  <td className="p-4">
                    <div className="flex items-center gap-3">
                      {product.image ? (
                        <img src={product.image} alt={product.name} className="w-10 h-10 rounded-lg object-cover bg-[var(--color-surface-secondary)]" />
                      ) : (
                        <div className="w-10 h-10 rounded-lg bg-[var(--color-surface-secondary)] flex items-center justify-center text-xs font-semibold text-[var(--color-text-muted)]">
                          P
                        </div>
                      )}
                      <div>
                        <p className="font-semibold text-sm">{product.name}</p>
                        {product.description && (
                          <p className="text-xs text-[var(--color-text-muted)] max-w-xs truncate">{product.description}</p>
                        )}
                      </div>
                    </div>
                  </td>
                  <td className="p-4 text-sm text-[var(--color-text-secondary)]">
                    {product.category?.name || '-'}
                  </td>
                  <td className="p-4 text-sm text-[var(--color-primary-red)] font-semibold">
                    Rs. {Number(product.price).toLocaleString()}
                  </td>
                  <td className="p-4 text-sm text-[var(--color-text-secondary)]">
                    {product.preparation_time} min
                  </td>
                  <td className="p-4">
                    <button
                      onClick={() => toggleAvailabilityMutation.mutate(product.id)}
                      disabled={toggleAvailabilityMutation.isPending}
                      className={`px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors ${
                        product.is_available
                          ? 'bg-[var(--color-success)]/10 text-[var(--color-success)] hover:bg-[var(--color-success)]/20'
                          : 'bg-[var(--color-primary-red)]/10 text-[var(--color-primary-red)] hover:bg-[var(--color-primary-red)]/20'
                      }`}
                    >
                      {product.is_available ? 'Available' : 'Unavailable'}
                    </button>
                  </td>
                  <td className="p-4 text-right space-x-2">
                    <button
                      onClick={() => openEditModal(product)}
                      className="px-3 py-1.5 bg-[var(--color-surface-secondary)] hover:bg-[var(--color-border)] text-xs rounded-md transition-colors"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => setDeleteConfirmId(product.id)}
                      className="px-3 py-1.5 bg-[var(--color-dark-red)]/20 hover:bg-[var(--color-dark-red)]/40 text-[var(--color-light-red)] text-xs rounded-md transition-colors"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Pagination */}
          {pagination && pagination.last_page > 1 && (
            <div className="p-4 border-t border-[var(--color-border)] flex items-center justify-between">
              <span className="text-xs text-[var(--color-text-secondary)]">
                Showing Page {pagination.current_page} of {pagination.last_page} ({pagination.total} total)
              </span>
              <div className="flex gap-2">
                <button
                  onClick={() => setPage((p) => Math.max(p - 1, 1))}
                  disabled={page === 1}
                  className="px-3 py-1.5 bg-[var(--color-surface-secondary)] disabled:opacity-40 text-xs rounded-md"
                >
                  Previous
                </button>
                <button
                  onClick={() => setPage((p) => Math.min(p + 1, pagination.last_page))}
                  disabled={page === pagination.last_page}
                  className="px-3 py-1.5 bg-[var(--color-surface-secondary)] disabled:opacity-40 text-xs rounded-md"
                >
                  Next
                </button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Create / Edit Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-auto">
            <h2 className="text-xl font-bold mb-4">
              {editingProduct ? 'Edit Product' : 'Create Product'}
            </h2>

            {error && (
              <div className="mb-4 p-3 bg-[var(--color-dark-red)]/20 border border-[var(--color-primary-red)]/30 rounded-lg text-sm text-[var(--color-light-red)]">
                {error}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Name</label>
                <input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="e.g. Classic Smash Burger"
                  required
                  className="w-full"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Category</label>
                <select
                  value={categoryId}
                  onChange={(e) => setCategoryId(e.target.value ? Number(e.target.value) : '')}
                  required
                  className="w-full"
                >
                  <option value="">Select Category</option>
                  {categories.map((cat) => (
                    <option key={cat.id} value={cat.id}>
                      {cat.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Price ($)</label>
                  <input
                    type="number"
                    step="0.01"
                    value={price}
                    onChange={(e) => setPrice(e.target.value)}
                    placeholder="9.99"
                    required
                    className="w-full"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Prep Time (min)</label>
                  <input
                    type="number"
                    value={prepTime}
                    onChange={(e) => setPrepTime(e.target.value)}
                    placeholder="10"
                    className="w-full"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Description</label>
                <textarea
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Product description..."
                  rows={3}
                  className="w-full"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Image URL</label>
                <input
                  type="url"
                  value={image}
                  onChange={(e) => setImage(e.target.value)}
                  placeholder="https://images.unsplash.com/..."
                  className="w-full"
                />
              </div>

              <div className="flex items-center gap-2">
                <input
                  type="checkbox"
                  id="is_available"
                  checked={isAvailable}
                  onChange={(e) => setIsAvailable(e.target.checked)}
                  className="w-4 h-4 rounded border-[var(--color-border)] bg-[var(--color-surface)] text-[var(--color-primary-red)] focus:ring-0"
                />
                <label htmlFor="is_available" className="text-sm text-[var(--color-text-secondary)]">
                  Product is available for ordering
                </label>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-[var(--color-border)]">
                <button
                  type="button"
                  onClick={closeModal}
                  className="px-4 py-2 text-sm text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-secondary)] rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={saveMutation.isPending}
                  className="px-4 py-2 text-sm bg-[var(--color-primary-red)] hover:bg-[var(--color-light-red)] text-white font-semibold rounded-lg disabled:opacity-50 transition-colors"
                >
                  {saveMutation.isPending ? 'Saving...' : 'Save'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Confirmation Modal */}
      {deleteConfirmId !== null && (
        <div className="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl w-full max-w-sm p-6 text-center">
            <h3 className="text-lg font-bold mb-2">Delete Product?</h3>
            <p className="text-sm text-[var(--color-text-secondary)] mb-6">
              Are you sure you want to delete this product? This action cannot be undone.
            </p>
            <div className="flex justify-center gap-3">
              <button
                onClick={() => setDeleteConfirmId(null)}
                className="px-4 py-2 text-sm text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-secondary)] rounded-lg"
              >
                Cancel
              </button>
              <button
                onClick={() => deleteMutation.mutate(deleteConfirmId)}
                disabled={deleteMutation.isPending}
                className="px-4 py-2 text-sm bg-[var(--color-primary-red)] hover:bg-[var(--color-light-red)] text-white font-semibold rounded-lg disabled:opacity-50"
              >
                {deleteMutation.isPending ? 'Deleting...' : 'Delete'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
