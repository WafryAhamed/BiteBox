import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { getErrorMessage } from '../utils/errorUtils';

export function LoginPage() {
  const { login, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState<{ email?: string; password?: string }>({});
  const [loading, setLoading] = useState(false);

  if (isAuthenticated) {
    navigate('/dashboard', { replace: true });
    return null;
  }

  const validate = () => {
    const errs: typeof fieldErrors = {};
    if (!email.trim()) {
      errs.email = 'Please enter your email address.';
    } else if (!/\S+@\S+\.\S+/.test(email.trim())) {
      errs.email = 'Please enter a valid email address.';
    }

    if (!password) {
      errs.password = 'Please enter your password.';
    }

    setFieldErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    if (!validate()) return;

    setLoading(true);
    try {
      await login(email.trim(), password);
      navigate('/dashboard', { replace: true });
    } catch (err: any) {
      setError(getErrorMessage(err));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[var(--color-bg)] flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <img src="/biteboxlogo.png" alt="BiteBox Logo" className="h-12 w-auto object-contain mx-auto mb-3" />
          <p className="text-sm text-[var(--color-text-muted)] tracking-wider uppercase font-semibold">Admin Dashboard</p>
        </div>

        <form
          onSubmit={handleSubmit}
          className="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl p-8 shadow-xl"
        >
          <h1 className="text-xl font-bold mb-6 text-white">Sign In</h1>

          {error && (
            <div className="mb-6 p-3.5 bg-[var(--color-dark-red)]/20 border border-[var(--color-primary-red)]/40 rounded-xl text-sm text-[var(--color-light-red)] font-medium">
              {error}
            </div>
          )}

          <div className="mb-4">
            <label className="block text-sm text-[var(--color-text-secondary)] mb-2 font-medium">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => {
                setEmail(e.target.value);
                setFieldErrors((prev) => ({ ...prev, email: undefined }));
                if (error) setError('');
              }}
              placeholder="nuwan.perera@bitebox.lk"
              className={`w-full px-4 py-3 bg-[var(--color-surface-secondary)] border rounded-xl text-white placeholder-[var(--color-text-muted)] focus:outline-none focus:border-[var(--color-primary-red)] transition-colors ${
                fieldErrors.email ? 'border-[var(--color-primary-red)]' : 'border-[var(--color-border)]'
              }`}
            />
            {fieldErrors.email && (
              <p className="text-xs text-[var(--color-primary-red)] mt-1.5">{fieldErrors.email}</p>
            )}
          </div>

          <div className="mb-6">
            <label className="block text-sm text-[var(--color-text-secondary)] mb-2 font-medium">Password</label>
            <div className="relative">
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => {
                  setPassword(e.target.value);
                  setFieldErrors((prev) => ({ ...prev, password: undefined }));
                  if (error) setError('');
                }}
                placeholder="Enter your password"
                className={`w-full px-4 py-3 pr-12 bg-[var(--color-surface-secondary)] border rounded-xl text-white placeholder-[var(--color-text-muted)] focus:outline-none focus:border-[var(--color-primary-red)] transition-colors ${
                  fieldErrors.password ? 'border-[var(--color-primary-red)]' : 'border-[var(--color-border)]'
                }`}
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                aria-label={showPassword ? 'Hide password' : 'Show password'}
                title={showPassword ? 'Hide password' : 'Show password'}
                className="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-[var(--color-text-muted)] hover:text-white transition-colors focus:outline-none"
              >
                {showPassword ? (
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                ) : (
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.025 10.025 0 013.68-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21f-3-3m-3.95-3.95L3 3" />
                  </svg>
                )}
              </button>
            </div>
            {fieldErrors.password && (
              <p className="text-xs text-[var(--color-primary-red)] mt-1.5">{fieldErrors.password}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full py-3.5 bg-[var(--color-primary-red)] hover:bg-[var(--color-light-red)] disabled:opacity-50 rounded-xl font-bold text-white transition-all shadow-md cursor-pointer"
          >
            {loading ? 'Signing in...' : 'Sign In'}
          </button>
        </form>
      </div>
    </div>
  );
}

