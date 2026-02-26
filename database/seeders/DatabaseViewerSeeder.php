<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseViewerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Electronics', 'description' => 'Gadgets and devices', 'is_active' => true, 'created_at' => now()],
            ['name' => 'Books', 'description' => 'Educational and fiction', 'is_active' => true, 'created_at' => now()],
        ]);

        DB::table('products')->insert([
            [
                'category_name' => 'Electronics',
                'sku' => 'LAP-001',
                'name' => 'MacBook Pro',
                'price' => 1999.99,
                'stock' => 15,
                'attributes' => json_encode(['cpu' => 'M3', 'ram' => '16GB']),
                'release_date' => '2023-11-01',
                'created_at' => now()
            ],
            [
                'category_name' => 'Books',
                'sku' => 'BOOK-77',
                'name' => 'Clean Code',
                'price' => 29.50,
                'stock' => 100,
                'attributes' => json_encode(['author' => 'Robert Martin', 'pages' => 464]),
                'release_date' => '2008-08-01',
                'created_at' => now()
            ],
        ]);

        DB::table('orders')->insert([
            ['order_number' => 'ORD-101', 'total_amount' => 120.50, 'status' => 'completed', 'placed_at' => now(), 'created_at' => now()],
            ['order_number' => 'ORD-102', 'total_amount' => 50.00, 'status' => 'pending', 'placed_at' => now(), 'created_at' => now()],
        ]);

        DB::table('app_settings')->insert([
            ['key' => 'site_name', 'value' => 'My Awesome Store', 'group' => 'general', 'created_at' => now()],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'system', 'created_at' => now()],
        ]);
    }
}
