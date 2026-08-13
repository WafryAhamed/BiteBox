<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing seed data if needed
        OrderItem::truncate();
        Order::truncate();
        Address::truncate();
        User::truncate();
        Category::truncate();
        Product::truncate();
        ProductAddon::truncate();

        // Exactly 2 Admin Users (Sri Lankan)
        User::create([
            'name' => 'Nuwan Perera',
            'email' => 'nuwan.perera@bitebox.lk',
            'password' => Hash::make('password'),
            'phone' => '+94 77 245 6813',
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sachini Fernando',
            'email' => 'sachini.fernando@bitebox.lk',
            'password' => Hash::make('password'),
            'phone' => '+94 71 638 4527',
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);

        // Exactly 2 Customer Users (Sri Lankan)
        $kasun = User::create([
            'name' => 'Kasun Perera',
            'email' => 'kasun.perera@bitebox.lk',
            'password' => Hash::make('password'),
            'phone' => '+94 77 245 6813',
            'role' => UserRole::CUSTOMER,
            'email_verified_at' => now(),
        ]);

        $hiruni = User::create([
            'name' => 'Hiruni Fernando',
            'email' => 'hiruni.fernando@bitebox.lk',
            'password' => Hash::make('password'),
            'phone' => '+94 71 638 4527',
            'role' => UserRole::CUSTOMER,
            'email_verified_at' => now(),
        ]);

        // Categories
        $burgers = Category::create([
            'name' => 'Burgers',
            'description' => 'Juicy handcrafted burgers made with fresh Sri Lankan ingredients',
            'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $submarines = Category::create([
            'name' => 'Submarines',
            'description' => 'Fresh submarine sandwiches loaded with your favorites',
            'image' => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $sides = Category::create([
            'name' => 'Fries & Sides',
            'description' => 'Crispy loaded fries and delicious side options',
            'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $wraps = Category::create([
            'name' => 'Wraps',
            'description' => 'Toasted rolls filled with crispy chicken and fresh salad',
            'image' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $drinks = Category::create([
            'name' => 'Drinks',
            'description' => 'Refreshing chilled Sri Lankan beverages',
            'image' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        // BURGERS (Rs. 890 - 1,490)
        $classicBurger = Product::create([
            'category_id' => $burgers->id,
            'name' => 'Classic Chicken Burger',
            'description' => 'Juicy crispy chicken fillet with fresh lettuce, tomato and creamy garlic sauce in a soft toasted bun.',
            'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            'price' => 890.00,
            'is_available' => true,
            'preparation_time' => 12,
        ]);

        ProductAddon::create(['product_id' => $classicBurger->id, 'name' => 'Extra Cheese Slice', 'price' => 150.00, 'is_available' => true]);
        ProductAddon::create(['product_id' => $classicBurger->id, 'name' => 'Extra Sauce', 'price' => 100.00, 'is_available' => true]);

        $crispyBurger = Product::create([
            'category_id' => $burgers->id,
            'name' => 'Crispy Chicken Burger',
            'description' => 'Golden crispy chicken fillet layered with fresh lettuce, cheese and BiteBox special sauce.',
            'image' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&h=300&fit=crop',
            'price' => 1090.00,
            'is_available' => true,
            'preparation_time' => 15,
        ]);

        ProductAddon::create(['product_id' => $crispyBurger->id, 'name' => 'Extra Cheese', 'price' => 150.00, 'is_available' => true]);
        ProductAddon::create(['product_id' => $crispyBurger->id, 'name' => 'Caramelized Onions', 'price' => 120.00, 'is_available' => true]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Spicy Chicken Burger',
            'description' => 'Spicy seasoned chicken fillet topped with jalapeños, pepper jack cheese, and spicy mayo.',
            'image' => 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?w=400&h=300&fit=crop',
            'price' => 1150.00,
            'is_available' => true,
            'preparation_time' => 14,
        ]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Cheesy Chicken Burger',
            'description' => 'Double melted cheddar cheese draped over a tender grilled chicken patty with fresh greens.',
            'image' => 'https://images.unsplash.com/photo-1572802419224-296b0aeee15d?w=400&h=300&fit=crop',
            'price' => 1190.00,
            'is_available' => true,
            'preparation_time' => 12,
        ]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Double Chicken Burger',
            'description' => 'Two crispy chicken fillets stacked with double cheese, pickles, lettuce, and signature sauce.',
            'image' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=400&h=300&fit=crop',
            'price' => 1490.00,
            'is_available' => true,
            'preparation_time' => 15,
        ]);

        // SUBMARINES (Rs. 950 - 1,150)
        $crispySub = Product::create([
            'category_id' => $submarines->id,
            'name' => 'Crispy Chicken Submarine',
            'description' => 'Crispy chicken tenders, lettuce, tomatoes, and garlic mayo stuffed into a toasted 8-inch sub roll.',
            'image' => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&h=300&fit=crop',
            'price' => 950.00,
            'is_available' => true,
            'preparation_time' => 10,
        ]);

        ProductAddon::create(['product_id' => $crispySub->id, 'name' => 'Extra Cheese', 'price' => 150.00, 'is_available' => true]);

        Product::create([
            'category_id' => $submarines->id,
            'name' => 'Spicy Chicken Submarine',
            'description' => 'Crispy chicken, fresh salad and spicy sauce packed into a toasted submarine roll.',
            'image' => 'https://images.unsplash.com/photo-1554433607-66b5efe9d304?w=400&h=300&fit=crop',
            'price' => 1050.00,
            'is_available' => true,
            'preparation_time' => 12,
        ]);

        Product::create([
            'category_id' => $submarines->id,
            'name' => 'Cheesy Chicken Submarine',
            'description' => 'Warm toasted sub stuffed with seasoned chicken strips, melted mozzarella, and mayo.',
            'image' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=400&h=300&fit=crop',
            'price' => 1150.00,
            'is_available' => true,
            'preparation_time' => 14,
        ]);

        // FRIES & SIDES (Rs. 350 - 1,350)
        Product::create([
            'category_id' => $sides->id,
            'name' => 'French Fries',
            'description' => 'Golden crispy salted potato fries served piping hot.',
            'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
            'price' => 350.00,
            'is_available' => true,
            'preparation_time' => 6,
        ]);

        Product::create([
            'category_id' => $sides->id,
            'name' => 'Cheese Fries',
            'description' => 'Golden french fries smothered in warm creamy cheddar cheese sauce.',
            'image' => 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=400&h=300&fit=crop',
            'price' => 750.00,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        Product::create([
            'category_id' => $sides->id,
            'name' => 'Chicken Nuggets',
            'description' => '6 pieces of golden tender chicken nuggets served with garlic mayo dip.',
            'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=400&h=300&fit=crop',
            'price' => 850.00,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        Product::create([
            'category_id' => $sides->id,
            'name' => 'Spicy Chicken Bites',
            'description' => 'Bite-sized crispy chicken pieces tossed in hot pepper seasoning.',
            'image' => 'https://images.unsplash.com/photo-1585325701956-60dd9c8553bc?w=400&h=300&fit=crop',
            'price' => 950.00,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        Product::create([
            'category_id' => $sides->id,
            'name' => 'Loaded Chicken Fries',
            'description' => 'Crispy fries topped with seasoned chicken pieces, cheese and BiteBox special sauce.',
            'image' => 'https://images.unsplash.com/photo-1585325701956-60dd9c8553bc?w=400&h=300&fit=crop',
            'price' => 1350.00,
            'is_available' => true,
            'preparation_time' => 10,
        ]);

        // WRAPS (Rs. 950 - 1,050)
        Product::create([
            'category_id' => $wraps->id,
            'name' => 'Crispy Chicken Wrap',
            'description' => 'Crispy chicken strips, shredded lettuce, and garlic sauce wrapped in a soft tortilla.',
            'image' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=400&h=300&fit=crop',
            'price' => 950.00,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        Product::create([
            'category_id' => $wraps->id,
            'name' => 'Spicy Chicken Wrap',
            'description' => 'Spicy crispy chicken with fresh onion, tomato, and chilli sauce in a warm tortilla wrap.',
            'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop',
            'price' => 1050.00,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        // DRINKS (Rs. 250 - 450)
        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Coca-Cola (400ml)',
            'description' => 'Chilled 400ml PET bottle of Coca-Cola.',
            'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&h=300&fit=crop',
            'price' => 250.00,
            'is_available' => true,
            'preparation_time' => 2,
        ]);

        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Sprite (400ml)',
            'description' => 'Chilled 400ml PET bottle of Sprite.',
            'image' => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=400&h=300&fit=crop',
            'price' => 250.00,
            'is_available' => true,
            'preparation_time' => 2,
        ]);

        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Ginger Beer (EGB 400ml)',
            'description' => 'Classic Sri Lankan Elephant House Ginger Beer.',
            'image' => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=300&fit=crop',
            'price' => 300.00,
            'is_available' => true,
            'preparation_time' => 2,
        ]);

        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Iced Milo',
            'description' => 'Rich creamy chilled chocolate malt drink topped with extra Milo powder.',
            'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400&h=300&fit=crop',
            'price' => 450.00,
            'is_available' => true,
            'preparation_time' => 4,
        ]);

        // Demo Addresses for Sri Lankan Customers
        $kasunAddress = Address::create([
            'user_id' => $kasun->id,
            'label' => 'Home',
            'full_name' => 'Kasun Perera',
            'phone' => '+94 77 245 6813',
            'address_line' => 'No. 18, Duplication Road',
            'city' => 'Colombo 04',
            'notes' => 'Near Bambalapitiya junction',
            'is_default' => true,
        ]);

        $hiruniAddress = Address::create([
            'user_id' => $hiruni->id,
            'label' => 'Home',
            'full_name' => 'Hiruni Fernando',
            'phone' => '+94 71 638 4527',
            'address_line' => 'No. 24, Lewis Place',
            'city' => 'Negombo',
            'notes' => 'Near Beach Road',
            'is_default' => true,
        ]);

        // Demo Order 1: Kasun Perera
        // Crispy Chicken Burger (1090) + French Fries (350) + Coca-Cola (250) = Total Rs. 1,690
        $order1 = Order::create([
            'user_id' => $kasun->id,
            'address_id' => $kasunAddress->id,
            'order_number' => 'BB100001',
            'order_type' => OrderType::DELIVERY,
            'payment_method' => PaymentMethod::CASH,
            'payment_status' => PaymentStatus::PENDING,
            'order_status' => OrderStatus::PENDING,
            'subtotal' => 1690.00,
            'delivery_fee' => 0.00,
            'total' => 1690.00,
            'special_instruction' => 'Extra sauce please',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $crispyBurger->id,
            'product_name' => 'Crispy Chicken Burger',
            'unit_price' => 1090.00,
            'quantity' => 1,
            'subtotal' => 1090.00,
        ]);

        $friesProduct = Product::where('name', 'French Fries')->first();
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $friesProduct->id,
            'product_name' => 'French Fries',
            'unit_price' => 350.00,
            'quantity' => 1,
            'subtotal' => 350.00,
        ]);

        $cokeProduct = Product::where('name', 'Coca-Cola (400ml)')->first();
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $cokeProduct->id,
            'product_name' => 'Coca-Cola (400ml)',
            'unit_price' => 250.00,
            'quantity' => 1,
            'subtotal' => 250.00,
        ]);

        // Demo Order 2: Hiruni Fernando
        // Crispy Chicken Submarine (950) + Loaded Chicken Fries (1350) = Total Rs. 2,300
        $order2 = Order::create([
            'user_id' => $hiruni->id,
            'address_id' => $hiruniAddress->id,
            'order_number' => 'BB100002',
            'order_type' => OrderType::DELIVERY,
            'payment_method' => PaymentMethod::CASH,
            'payment_status' => PaymentStatus::PAID,
            'order_status' => OrderStatus::PREPARING,
            'subtotal' => 2300.00,
            'delivery_fee' => 0.00,
            'total' => 2300.00,
            'special_instruction' => 'Less spicy',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $crispySub->id,
            'product_name' => 'Crispy Chicken Submarine',
            'unit_price' => 950.00,
            'quantity' => 1,
            'subtotal' => 950.00,
        ]);

        $loadedFriesProduct = Product::where('name', 'Loaded Chicken Fries')->first();
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $loadedFriesProduct->id,
            'product_name' => 'Loaded Chicken Fries',
            'unit_price' => 1350.00,
            'quantity' => 1,
            'subtotal' => 1350.00,
        ]);
    }
}
