import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export function LoginPage() {
  const { login, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  if (isAuthenticated) {
    navigate('/dashboard', { replace: true });
    return null;
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await login(email, password);
      navigate('/dashboard', { replace: true });
    } catch (err: any) {
      setError(err.message || err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="flex items-center justify-center gap-2 mb-4">
            <span className="w-3 h-3 rounded-full bg-[var(--color-primary-red)]" />
            <span className="text-2xl font-extrabold tracking-widest">
              BITE<span className="text-[var(--color-primary-red)]">BOX</span>
            </span>
          </div>
          <p className="text-sm text-[var(--color-text-muted)] tracking-wider uppercase">Admin Dashboard</p>
        </div>

        <form
          onSubmit={handleSubmit}
          className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl p-8"
        >
          <h1 className="text-xl font-bold mb-6">Sign In</h1>

          {error && (
            <div className="mb-4 p-3 bg-[var(--color-dark-red)]/20 border border-[var(--color-primary-red)]/30 rounded-lg text-sm text-[var(--color-light-red)]">
              {error}
            </div>
          )}

          <div className="mb-4">
            <label className="block text-sm text-[var(--color-text-secondary)] mb-2">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="admin@bitebox.com"
              required
              className="w-full"
            />
          </div>

          <div className="mb-6">
            <label className="block text-sm text-[var(--color-text-secondary)] mb-2">Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Enter your password"
              required
              className="w-full"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full py-3 bg-[var(--color-primary-red)] hover:bg-[var(--color-light-red)] disabled:opacity-50 rounded-lg font-semibold text-white transition-colors"
          >
            {loading ? 'Signing in...' : 'Sign In'}
          </button>
        </form>
      </div>
    </div>
  );
}
