import { useState, type ReactNode } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const navItems = [
  { path: '/dashboard', label: 'Dashboard', icon: '📊' },
  { path: '/orders', label: 'Orders', icon: '🧾' },
  { path: '/categories', label: 'Categories', icon: '📁' },
  { path: '/products', label: 'Products', icon: '🍔' },
  { path: '/customers', label: 'Customers', icon: '👥' },
];

export function Layout({ children }: { children: ReactNode }) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [showLogoutModal, setShowLogoutModal] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);

  const confirmLogout = async () => {
    setLoggingOut(true);
    try {
      await logout();
      navigate('/login');
    } finally {
      setLoggingOut(false);
      setShowLogoutModal(false);
    }
  };

  return (
    <div className="flex h-screen bg-[var(--color-bg)]">
      {/* Sidebar */}
      <aside className="w-64 bg-[var(--color-surface)] border-r border-[var(--color-border)] flex flex-col">
        <div className="p-6 border-b border-[var(--color-border)]">
          <img src="/biteboxlogo.png" alt="BiteBox Logo" className="h-8 w-auto object-contain" />
          <p className="text-xs text-[var(--color-text-muted)] mt-1.5 tracking-wider uppercase font-semibold">Admin Panel</p>
        </div>

        <nav className="flex-1 p-4 space-y-1">
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              className={({ isActive }) =>
                `flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                  isActive
                    ? 'bg-[var(--color-primary-red)] text-white'
                    : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-secondary)] hover:text-white'
                }`
              }
            >
              <span>{item.icon}</span>
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="p-4 border-t border-[var(--color-border)]">
          <div className="flex items-center gap-3 mb-3 px-2">
            <div className="w-8 h-8 rounded-full bg-[var(--color-primary-red)] flex items-center justify-center text-sm font-bold text-white">
              {user?.name?.charAt(0) || 'A'}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium truncate text-white">{user?.name}</p>
              <p className="text-xs text-[var(--color-text-muted)] truncate">{user?.email}</p>
            </div>
          </div>
          <button
            onClick={() => setShowLogoutModal(true)}
            className="w-full px-4 py-2 text-sm text-[var(--color-primary-red)] hover:bg-[var(--color-surface-secondary)] rounded-lg transition-colors font-medium cursor-pointer flex items-center justify-center gap-2"
          >
            <span>🚪</span> Log out
          </button>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex-1 overflow-auto p-8">
        {children}
      </main>

      {/* Logout Confirmation Modal */}
      {showLogoutModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4">
          <div className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <h3 className="text-lg font-bold text-white mb-2">Confirm Logout</h3>
            <p className="text-sm text-[var(--color-text-secondary)] mb-6">
              Are you sure you want to log out of the admin panel?
            </p>
            <div className="flex justify-end gap-3">
              <button
                disabled={loggingOut}
                onClick={() => setShowLogoutModal(false)}
                className="px-4 py-2 bg-[var(--color-surface-secondary)] border border-[var(--color-border)] rounded-xl text-sm font-medium text-white hover:bg-[var(--color-border)] transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                disabled={loggingOut}
                onClick={confirmLogout}
                className="px-4 py-2 bg-[var(--color-primary-red)] hover:bg-[var(--color-dark-red)] text-white rounded-xl text-sm font-bold transition-all shadow-md cursor-pointer disabled:opacity-50"
              >
                {loggingOut ? 'Logging out...' : 'Log out'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

