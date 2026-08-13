export interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: 'customer' | 'admin';
  created_at: string;
}

export interface Category {
  id: number;
  name: string;
  description: string | null;
  image: string | null;
  is_active: boolean;
  products_count?: number;
  created_at: string;
  updated_at: string;
}

export interface ProductAddon {
  id: number;
  product_id: number;
  name: string;
  price: number;
  is_available: boolean;
}

export interface Product {
  id: number;
  category_id: number;
  name: string;
  description: string | null;
  image: string | null;
  price: number;
  is_available: boolean;
  preparation_time: number;
  category?: Category;
  addons?: ProductAddon[];
  created_at: string;
  updated_at: string;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface PaginatedData<T> {
  items: T[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface AuthData {
  user: User;
  token: string;
}

export * from './order';

