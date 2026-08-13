import type { User } from './index';

export type OrderType = 'PICKUP' | 'DELIVERY';
export type PaymentMethod = 'CASH';
export type PaymentStatus = 'PENDING' | 'PAID';
export type OrderStatus = 'PENDING' | 'CONFIRMED' | 'PREPARING' | 'READY' | 'COMPLETED' | 'CANCELLED';

export interface Address {
  id: number;
  user_id: number;
  label: string;
  full_name: string;
  phone: string;
  address_line: string;
  city: string;
  notes?: string | null;
  is_default: boolean;
  created_at: string;
  updated_at: string;
}

export interface OrderItemAddon {
  id: number;
  order_item_id: number;
  product_addon_id: number;
  addon_name: string;
  addon_price: number;
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product_name: string;
  unit_price: number;
  quantity: number;
  subtotal: number;
  special_instruction?: string | null;
  addons?: OrderItemAddon[];
}

export interface Order {
  id: number;
  user_id: number;
  address_id?: number | null;
  order_number: string;
  order_type: OrderType;
  payment_method: PaymentMethod;
  payment_status: PaymentStatus;
  order_status: OrderStatus;
  subtotal: number;
  delivery_fee: number;
  total: number;
  special_instruction?: string | null;
  user?: User;
  address?: Address | null;
  items?: OrderItem[];
  created_at: string;
  updated_at: string;
}

export interface DashboardStats {
  today_orders: number;
  today_revenue: number;
  pending_orders: number;
  preparing_orders: number;
  completed_orders: number;
  recent_orders: Order[];
}
