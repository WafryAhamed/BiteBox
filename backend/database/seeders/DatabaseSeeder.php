<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'BiteBox Admin',
            'email' => 'admin@bitebox.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);

        // Create customer user
        User::create([
            'name' => 'John Doe',
            'email' => 'customer@bitebox.com',
            'password' => Hash::make('password'),
            'phone' => '+0987654321',
            'role' => UserRole::CUSTOMER,
            'email_verified_at' => now(),
        ]);

        // Categories
        $burgers = Category::create([
            'name' => 'Burgers',
            'description' => 'Juicy handcrafted burgers made with premium ingredients',
            'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $submarines = Category::create([
            'name' => 'Submarines',
            'description' => 'Fresh submarine sandwiches loaded with your favorites',
            'image' => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $chicken = Category::create([
            'name' => 'Chicken Fry',
            'description' => 'Crispy golden fried chicken made to perfection',
            'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $sides = Category::create([
            'name' => 'Sides',
            'description' => 'Perfect sides to complement your meal',
            'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        $drinks = Category::create([
            'name' => 'Drinks',
            'description' => 'Refreshing beverages to quench your thirst',
            'image' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=300&fit=crop',
            'is_active' => true,
        ]);

        // Burgers
        $classicBurger = Product::create([
            'category_id' => $burgers->id,
            'name' => 'Classic Smash Burger',
            'description' => 'Double smashed beef patties with American cheese, pickles, onions, and our signature sauce on a brioche bun.',
            'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            'price' => 9.99,
            'is_available' => true,
            'preparation_time' => 12,
        ]);

        ProductAddon::create(['product_id' => $classicBurger->id, 'name' => 'Extra Patty', 'price' => 3.50, 'is_available' => true]);
        ProductAddon::create(['product_id' => $classicBurger->id, 'name' => 'Bacon', 'price' => 2.00, 'is_available' => true]);
        ProductAddon::create(['product_id' => $classicBurger->id, 'name' => 'Jalapeños', 'price' => 1.00, 'is_available' => true]);

        $bbqBurger = Product::create([
            'category_id' => $burgers->id,
            'name' => 'BBQ Bacon Burger',
            'description' => 'Smoky BBQ sauce, crispy bacon, cheddar cheese, and onion rings on a toasted bun.',
            'image' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&h=300&fit=crop',
            'price' => 12.99,
            'is_available' => true,
            'preparation_time' => 15,
        ]);

        ProductAddon::create(['product_id' => $bbqBurger->id, 'name' => 'Extra Cheese', 'price' => 1.50, 'is_available' => true]);
        ProductAddon::create(['product_id' => $bbqBurger->id, 'name' => 'Avocado', 'price' => 2.50, 'is_available' => true]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Mushroom Swiss Burger',
            'description' => 'Sautéed mushrooms, melted Swiss cheese, garlic aioli on a pretzel bun.',
            'image' => 'https://images.unsplash.com/photo-1572802419224-296b0aeee15d?w=400&h=300&fit=crop',
            'price' => 11.49,
            'is_available' => true,
            'preparation_time' => 14,
        ]);

        Product::create([
            'category_id' => $burgers->id,
            'name' => 'Spicy Inferno Burger',
            'description' => 'Ghost pepper sauce, pepper jack cheese, jalapeños, and crispy onions. Not for the faint-hearted.',
            'image' => 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?w=400&h=300&fit=crop',
            'price' => 13.49,
            'is_available' => false,
            'preparation_time' => 15,
        ]);

        // Submarines
        $italianSub = Product::create([
            'category_id' => $submarines->id,
            'name' => 'Italian Classic Sub',
            'description' => 'Salami, capicola, provolone, lettuce, tomatoes, onions, oil & vinegar on fresh Italian bread.',
            'image' => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&h=300&fit=crop',
            'price' => 10.99,
            'is_available' => true,
            'preparation_time' => 10,
        ]);

        ProductAddon::create(['product_id' => $italianSub->id, 'name' => 'Extra Meat', 'price' => 3.00, 'is_available' => true]);
        ProductAddon::create(['product_id' => $italianSub->id, 'name' => 'Extra Cheese', 'price' => 1.50, 'is_available' => true]);

        Product::create([
            'category_id' => $submarines->id,
            'name' => 'Chicken Teriyaki Sub',
            'description' => 'Grilled teriyaki chicken, Swiss cheese, lettuce, and spicy mayo on a toasted sub roll.',
            'image' => 'https://images.unsplash.com/photo-1554433607-66b5efe9d304?w=400&h=300&fit=crop',
            'price' => 11.49,
            'is_available' => true,
            'preparation_time' => 12,
        ]);

        Product::create([
            'category_id' => $submarines->id,
            'name' => 'Philly Cheesesteak Sub',
            'description' => 'Thinly sliced steak, melted provolone, sautéed peppers and onions on a hoagie roll.',
            'image' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=400&h=300&fit=crop',
            'price' => 13.99,
            'is_available' => true,
            'preparation_time' => 15,
        ]);

        // Chicken Fry
        $crispyTenders = Product::create([
            'category_id' => $chicken->id,
            'name' => 'Crispy Chicken Tenders',
            'description' => '5 pieces of golden crispy chicken tenders served with your choice of dipping sauce.',
            'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=400&h=300&fit=crop',
            'price' => 8.99,
            'is_available' => true,
            'preparation_time' => 10,
        ]);

        ProductAddon::create(['product_id' => $crispyTenders->id, 'name' => 'BBQ Sauce', 'price' => 0.50, 'is_available' => true]);
        ProductAddon::create(['product_id' => $crispyTenders->id, 'name' => 'Ranch Dip', 'price' => 0.50, 'is_available' => true]);
        ProductAddon::create(['product_id' => $crispyTenders->id, 'name' => 'Buffalo Sauce', 'price' => 0.75, 'is_available' => true]);

        Product::create([
            'category_id' => $chicken->id,
            'name' => 'Spicy Wings Bucket',
            'description' => '8 pieces of spicy buffalo wings tossed in our house-made hot sauce. Served with celery and blue cheese.',
            'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&h=300&fit=crop',
            'price' => 12.99,
            'is_available' => true,
            'preparation_time' => 15,
        ]);

        Product::create([
            'category_id' => $chicken->id,
            'name' => 'Chicken Popcorn Box',
            'description' => 'Bite-sized crispy chicken pieces seasoned with our secret spice blend.',
            'image' => 'https://images.unsplash.com/photo-1585325701956-60dd9c8553bc?w=400&h=300&fit=crop',
            'price' => 6.99,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        // Sides
        Product::create([
            'category_id' => $sides->id,
            'name' => 'Loaded Fries',
            'description' => 'Crispy fries topped with cheese sauce, bacon bits, and green onions.',
            'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
            'price' => 5.99,
            'is_available' => true,
            'preparation_time' => 8,
        ]);

        Product::create([
            'category_id' => $sides->id,
            'name' => 'Onion Rings',
            'description' => 'Beer-battered onion rings served with smoky chipotle mayo.',
            'image' => 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=400&h=300&fit=crop',
            'price' => 4.99,
            'is_available' => true,
            'preparation_time' => 7,
        ]);

        Product::create([
            'category_id' => $sides->id,
            'name' => 'Coleslaw',
            'description' => 'Creamy homestyle coleslaw with a tangy twist.',
            'image' => 'https://images.unsplash.com/photo-1625938145744-533e82e78583?w=400&h=300&fit=crop',
            'price' => 3.49,
            'is_available' => true,
            'preparation_time' => 3,
        ]);

        // Drinks
        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Classic Milkshake',
            'description' => 'Thick and creamy milkshake available in vanilla, chocolate, or strawberry.',
            'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400&h=300&fit=crop',
            'price' => 5.49,
            'is_available' => true,
            'preparation_time' => 5,
        ]);

        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Fresh Lemonade',
            'description' => 'House-made lemonade with fresh lemons and a hint of mint.',
            'image' => 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=400&h=300&fit=crop',
            'price' => 3.99,
            'is_available' => true,
            'preparation_time' => 3,
        ]);

        Product::create([
            'category_id' => $drinks->id,
            'name' => 'Iced Coffee',
            'description' => 'Cold brewed coffee served over ice with your choice of milk.',
            'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400&h=300&fit=crop',
            'price' => 4.49,
            'is_available' => true,
            'preparation_time' => 4,
        ]);
    }
}
