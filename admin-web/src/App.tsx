import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './contexts/AuthContext';
import { Layout } from './components/Layout';
import { LoginPage } from './pages/LoginPage';
import { DashboardPage } from './pages/DashboardPage';
import { CategoriesPage } from './pages/CategoriesPage';
import { ProductsPage } from './pages/ProductsPage';
import { OrdersPage } from './pages/OrdersPage';
import { CustomersPage } from './pages/CustomersPage';
import { CustomerDetailPage } from './pages/CustomerDetailPage';
import { OrderDetailPage } from './pages/OrderDetailPage';

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, isLoading } = useAuth();
  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-screen bg-[var(--color-bg)]">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[var(--color-primary-red)]" />
      </div>
    );
  }
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />;
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/*"
        element={
          <ProtectedRoute>
            <Layout>
              <Routes>
                <Route path="/" element={<Navigate to="/dashboard" replace />} />
                <Route path="/dashboard" element={<DashboardPage />} />
                <Route path="/orders" element={<OrdersPage />} />
                <Route path="/orders/:id" element={<OrderDetailPage />} />
                <Route path="/categories" element={<CategoriesPage />} />
                <Route path="/products" element={<ProductsPage />} />
                <Route path="/customers" element={<CustomersPage />} />
                <Route path="/customers/:id" element={<CustomerDetailPage />} />
              </Routes>
            </Layout>
          </ProtectedRoute>
        }
      />
    </Routes>
  );
}

