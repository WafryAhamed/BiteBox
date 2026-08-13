import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '../services/api';
import type { Category } from '../types';

export function CategoriesPage() {
  const queryClient = useQueryClient();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [deleteConfirmId, setDeleteConfirmId] = useState<number | null>(null);

  // Form state
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [image, setImage] = useState('');
  const [isActive, setIsActive] = useState(true);
  const [error, setError] = useState('');

  const { data: categories = [], isLoading, isError, refetch } = useQuery({
    queryKey: ['admin-categories'],
    queryFn: () => api.get<{ data: Category[] }>('/categories').then((r) => r.data.data),
  });

  const saveMutation = useMutation({
    mutationFn: (data: { name: string; description?: string; image?: string; is_active?: boolean }) => {
      if (editingCategory) {
        return api.put(`/categories/${editingCategory.id}`, data);
      }
      return api.post('/categories', data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-categories'] });
      closeModal();
    },
    onError: (err: any) => {
      setError(err.response?.data?.message || 'Failed to save category');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => api.delete(`/categories/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-categories'] });
      setDeleteConfirmId(null);
    },
  });

  const openCreateModal = () => {
    setEditingCategory(null);
    setName('');
    setDescription('');
    setImage('');
    setIsActive(true);
    setError('');
    setIsModalOpen(true);
  };

  const openEditModal = (category: Category) => {
    setEditingCategory(category);
    setName(category.name);
    setDescription(category.description || '');
    setImage(category.image || '');
    setIsActive(category.is_active);
    setError('');
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setEditingCategory(null);
    setError('');
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim()) {
      setError('Category name is required');
      return;
    }
    saveMutation.mutate({
      name: name.trim(),
      description: description.trim() || undefined,
      image: image.trim() || undefined,
      is_active: isActive,
    });
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="text-2xl font-bold">Categories</h1>
          <p className="text-sm text-[var(--color-text-secondary)] mt-1">Manage product categories</p>
        </div>
        <button
          onClick={openCreateModal}
          className="px-4 py-2.5 bg-[var(--color-primary-red)] hover:bg-[var(--color-light-red)] text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2"
        >
          <span>+</span> Add Category
        </button>
      </div>

      {isLoading ? (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-8 text-center text-[var(--color-text-muted)]">
          Loading categories...
        </div>
      ) : isError ? (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-8 text-center">
          <p className="text-[var(--color-primary-red)] mb-4">Failed to load categories</p>
          <button onClick={() => refetch()} className="px-4 py-2 bg-[var(--color-surface-secondary)] rounded-lg text-sm">
            Retry
          </button>
        </div>
      ) : categories.length === 0 ? (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl p-12 text-center text-[var(--color-text-secondary)]">
          No categories found. Click "Add Category" to create your first category.
        </div>
      ) : (
        <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-xl overflow-hidden">
          <table className="w-full">
            <thead>
              <tr className="border-b border-[var(--color-border)] text-left">
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Category</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Description</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Products</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Status</th>
                <th className="p-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)] text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {categories.map((category) => (
                <tr key={category.id} className="border-b border-[var(--color-border)] last:border-0 hover:bg-[var(--color-surface-secondary)] transition-colors">
                  <td className="p-4">
                    <div className="flex items-center gap-3">
                      {category.image ? (
                        <img src={category.image} alt={category.name} className="w-10 h-10 rounded-lg object-cover bg-[var(--color-surface-secondary)]" />
                      ) : (
                        <div className="w-10 h-10 rounded-lg bg-[var(--color-surface-secondary)] flex items-center justify-center text-xs font-semibold text-[var(--color-text-muted)]">
                          C
                        </div>
                      )}
                      <span className="font-semibold text-sm">{category.name}</span>
                    </div>
                  </td>
                  <td className="p-4 text-sm text-[var(--color-text-secondary)] max-w-xs truncate">
                    {category.description || '-'}
                  </td>
                  <td className="p-4 text-sm text-[var(--color-text-secondary)]">
                    {category.products_count ?? '-'}
                  </td>
                  <td className="p-4">
                    <span
                      className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${
                        category.is_active
                          ? 'bg-[var(--color-success)]/10 text-[var(--color-success)]'
                          : 'bg-[var(--color-primary-red)]/10 text-[var(--color-primary-red)]'
                      }`}
                    >
                      {category.is_active ? 'Active' : 'Inactive'}
                    </span>
                  </td>
                  <td className="p-4 text-right space-x-2">
                    <button
                      onClick={() => openEditModal(category)}
                      className="px-3 py-1.5 bg-[var(--color-surface-secondary)] hover:bg-[var(--color-border)] text-xs rounded-md transition-colors"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => setDeleteConfirmId(category.id)}
                      className="px-3 py-1.5 bg-[var(--color-dark-red)]/20 hover:bg-[var(--color-dark-red)]/40 text-[var(--color-light-red)] text-xs rounded-md transition-colors"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Create / Edit Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl w-full max-w-md p-6">
            <h2 className="text-xl font-bold mb-4">
              {editingCategory ? 'Edit Category' : 'Create Category'}
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
                  placeholder="e.g. Burgers"
                  required
                  className="w-full"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Description</label>
                <textarea
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Category description..."
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
                  id="is_active"
                  checked={isActive}
                  onChange={(e) => setIsActive(e.target.checked)}
                  className="w-4 h-4 rounded border-[var(--color-border)] bg-[var(--color-surface)] text-[var(--color-primary-red)] focus:ring-0"
                />
                <label htmlFor="is_active" className="text-sm text-[var(--color-text-secondary)]">
                  Active category
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
            <h3 className="text-lg font-bold mb-2">Delete Category?</h3>
            <p className="text-sm text-[var(--color-text-secondary)] mb-6">
              Are you sure you want to delete this category? Products linked to this category will also be deleted.
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
