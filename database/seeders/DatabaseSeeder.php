<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EmployeeSeeder::class,
            CustomerSeeder::class,
            InvoiceSeeder::class,
        ]);

        // إنشاء مستخدم تجريبي للنظام
        User::factory()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@erp.com',
        ]);
    }
}
