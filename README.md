# BiteBox 🍔

A full-stack food ordering platform with a React Native mobile app, React/Vite admin dashboard, and Laravel REST API backend.

## Architecture

```
BiteBox/
├── backend/        # Laravel 12 REST API (PHP 8.2+, SQLite)
├── mobile/         # React Native + Expo (TypeScript)
└── admin-web/      # React + Vite + TailwindCSS (TypeScript)
```

## Features

### Customer Mobile App
- **Auth** — Register, login, logout with JWT (Sanctum)
- **Menu** — Browse categories, search products, view details & add-ons
- **Cart** — Add/remove items, customise add-ons, special instructions
- **Checkout** — Delivery or pickup, saved addresses, cash payment
- **Orders** — Track order status, view history, cancel pending orders
- **Favorites** — Toggle favorite products for quick access
- **Profile** — Edit name, phone; manage saved addresses

### Admin Dashboard
- **Dashboard** — Today's orders/revenue, pending/preparing counts, recent orders, top products
- **Orders** — List, filter by status/type/date/search, view detail, advance status workflow
- **Categories** — CRUD with image upload, toggle active/inactive
- **Products** — CRUD with images, add-ons, pricing, availability toggle
- **Customers** — View customer list with order stats, detail view with full order history

### Backend API
- RESTful JSON API with versioned routes (`/api/v1/...`)
- Server-side price calculation — clients cannot tamper totals
- Order status workflow: `PENDING → CONFIRMED → PREPARING → READY → COMPLETED`
- Role-based access: customer vs admin middleware
- Request validation with FormRequest classes
- 52 automated Feature + Unit tests

---

## Prerequisites

| Tool         | Version   |
|-------------|-----------|
| PHP          | 8.2+      |
| Composer     | 2.x       |
| Node.js      | 18+       |
| npm          | 9+        |
| Expo CLI     | (via npx) |

---

## Quick Start

### 1. Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite   # SQLite — no DB server needed
php artisan migrate --seed
php artisan serve                 # http://127.0.0.1:8000
```

The seeder creates:
- **Admin** — `admin@bitebox.com` / `password`
- Sample categories, products and add-ons

### 2. Admin Dashboard

```bash
cd admin-web
npm install
npm run dev    # http://localhost:5173
```

Login with the admin credentials above.

### 3. Mobile App

```bash
cd mobile
npm install
npx expo start --web    # http://localhost:8081 (web preview)
# or
npx expo start          # scan QR with Expo Go for device testing
```

---

## API Endpoints

### Public
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/categories` | List active categories |
| GET | `/api/v1/products` | List available products |
| GET | `/api/v1/products/{id}` | Product detail with add-ons |
| POST | `/api/v1/auth/register` | Customer registration |
| POST | `/api/v1/auth/login` | Login (returns token) |

### Customer (auth required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/auth/me` | Current user profile |
| PUT | `/api/v1/auth/profile` | Update name/phone |
| POST | `/api/v1/auth/logout` | Logout |
| GET/POST | `/api/v1/addresses` | List / create addresses |
| PUT/DELETE | `/api/v1/addresses/{id}` | Update / delete address |
| POST | `/api/v1/orders` | Place order |
| GET | `/api/v1/orders` | My orders |
| GET | `/api/v1/orders/{id}` | Order detail |
| POST | `/api/v1/orders/{id}/cancel` | Cancel pending order |
| GET | `/api/v1/favorites` | List favorites |
| POST | `/api/v1/favorites/{productId}/toggle` | Toggle favorite |

### Admin (auth + admin role)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST/PUT/DELETE | `/api/v1/admin/categories/...` | Category CRUD |
| POST/PUT/DELETE | `/api/v1/admin/products/...` | Product CRUD |
| PUT | `/api/v1/admin/orders/{id}/status` | Update order status |
| GET | `/api/v1/admin/orders/stats` | Dashboard statistics |
| GET | `/api/v1/admin/customers` | Customer list |
| GET | `/api/v1/admin/customers/{id}` | Customer detail + orders |

---

## Testing

```bash
cd backend
php artisan test          # 52 tests, 149 assertions
```

```bash
cd admin-web
npx tsc --noEmit          # TypeScript type check

cd mobile
npx tsc --noEmit          # TypeScript type check
```

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2, Sanctum, SQLite |
| Mobile | React Native, Expo SDK 53, TypeScript, Zustand |
| Admin | React 19, Vite, TailwindCSS 4, React Query |
| Navigation | React Navigation 7 (mobile) / React Router 7 (admin) |

---

## Project Structure

### Backend
```
backend/
├── app/
│   ├── Enums/          # OrderStatus, OrderType, PaymentMethod, etc.
│   ├── Http/
│   │   ├── Controllers/ # Auth, Category, Product, Order, Address, Customer, Favorite
│   │   ├── Middleware/  # AdminMiddleware
│   │   └── Requests/   # Form validation requests
│   ├── Models/         # User, Category, Product, ProductAddon, Order, OrderItem, Address, Favorite
│   └── Services/       # AuthService, OrderService
├── database/
│   ├── migrations/     # All table schemas
│   └── seeders/        # Demo data seeder
├── routes/api.php      # API route definitions
└── tests/Feature/      # 52 automated tests
```

### Mobile
```
mobile/src/
├── components/         # Reusable UI (Button, Input, CategoryCard, etc.)
├── contexts/           # AuthContext (global auth state)
├── features/
│   ├── auth/           # Login, Register, ForgotPassword screens
│   ├── cart/           # CartScreen, CheckoutScreen, OrderConfirmation, cartStore
│   ├── menu/           # MenuScreen, ProductDetailScreen
│   ├── orders/         # OrdersScreen, OrderDetailScreen
│   └── profile/        # ProfileScreen, EditProfile, AddressList, AddressForm, Favorites
├── navigation/         # AppNavigator with tab + stack navigators
├── services/           # API client + service modules
├── theme/              # Colors, typography, spacing tokens
└── types/              # TypeScript interfaces
```

### Admin Dashboard
```
admin-web/src/
├── components/         # Layout (sidebar + main area)
├── contexts/           # AuthContext
├── pages/              # Dashboard, Orders, OrderDetail, Categories, Products, Customers, CustomerDetail
├── services/           # Axios API client
└── types/              # TypeScript interfaces
```