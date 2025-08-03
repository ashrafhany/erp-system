<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop - Business Edition',
                'description' => 'High performance laptop for business use with i7 processor, 16GB RAM and 512GB SSD',
                'sku' => 'TECH-LT-001',
                'price' => 1299.99,
                'cost' => 899.99,
                'category' => 'Electronics',
                'tax_rate' => 10.0,
                'status' => 'active',
                'initial_stock' => 25
            ],
            [
                'name' => 'Office Desk - Premium',
                'description' => 'Ergonomic office desk with adjustable height and cable management',
                'sku' => 'FURN-DSK-001',
                'price' => 399.99,
                'cost' => 250.00,
                'category' => 'Furniture',
                'tax_rate' => 8.5,
                'status' => 'active',
                'initial_stock' => 15
            ],
            [
                'name' => 'Business Card Paper - Premium',
                'description' => 'High quality 300gsm paper for business cards, pack of 500 sheets',
                'sku' => 'STAT-PRP-001',
                'price' => 29.99,
                'cost' => 15.00,
                'category' => 'Stationery',
                'tax_rate' => 5.0,
                'status' => 'active',
                'initial_stock' => 100
            ],
            [
                'name' => 'Wireless Mouse - Ergonomic',
                'description' => 'Wireless ergonomic mouse with programmable buttons and long battery life',
                'sku' => 'TECH-MSE-001',
                'price' => 49.99,
                'cost' => 25.00,
                'category' => 'Electronics',
                'tax_rate' => 10.0,
                'status' => 'active',
                'initial_stock' => 50
            ],
            [
                'name' => 'Office Chair - Executive',
                'description' => 'Premium executive office chair with leather upholstery and lumbar support',
                'sku' => 'FURN-CHR-001',
                'price' => 299.99,
                'cost' => 180.00,
                'category' => 'Furniture',
                'tax_rate' => 8.5,
                'status' => 'active',
                'initial_stock' => 10
            ],
            [
                'name' => 'Cloud Backup Service - Monthly',
                'description' => 'Monthly subscription for cloud backup service with 1TB storage',
                'sku' => 'SERV-BKP-001',
                'price' => 19.99,
                'cost' => 5.00,
                'category' => 'Services',
                'tax_rate' => 10.0,
                'status' => 'active',
                'initial_stock' => 100
            ],
            [
                'name' => 'Security Software - Annual License',
                'description' => 'Annual license for business security software, 10 devices',
                'sku' => 'SOFT-SEC-001',
                'price' => 149.99,
                'cost' => 75.00,
                'category' => 'Software',
                'tax_rate' => 10.0,
                'status' => 'active',
                'initial_stock' => 30
            ],
            [
                'name' => 'Laser Printer - Monochrome',
                'description' => 'Fast monochrome laser printer for office use with duplex printing',
                'sku' => 'TECH-PRT-001',
                'price' => 249.99,
                'cost' => 150.00,
                'category' => 'Electronics',
                'tax_rate' => 10.0,
                'status' => 'active',
                'initial_stock' => 8
            ],
            [
                'name' => 'Meeting Room Display - 55"',
                'description' => '55-inch 4K display for meeting rooms with wireless screen sharing',
                'sku' => 'TECH-DSP-001',
                'price' => 899.99,
                'cost' => 650.00,
                'category' => 'Electronics',
                'tax_rate' => 10.0,
                'status' => 'active',
                'initial_stock' => 5
            ],
            [
                'name' => 'Filing Cabinet - 4 Drawer',
                'description' => '4-drawer metal filing cabinet with lock',
                'sku' => 'FURN-CAB-001',
                'price' => 199.99,
                'cost' => 120.00,
                'category' => 'Furniture',
                'tax_rate' => 8.5,
                'status' => 'active',
                'initial_stock' => 12
            ],
        ];

        foreach ($products as $productData) {
            $initialStock = $productData['initial_stock'];
            unset($productData['initial_stock']);

            $product = Product::create($productData);

            // Add initial inventory
            Inventory::create([
                'product_id' => $product->id,
                'quantity_change' => $initialStock,
                'type' => 'initial',
                'notes' => 'Initial inventory',
                'unit_cost' => $product->cost
            ]);
        }
    }
}
