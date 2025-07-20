<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'customer_code' => 'CUST001',
                'name' => 'شركة النيل للتجارة',
                'company_name' => 'شركة النيل للتجارة والاستثمار',
                'email' => 'info@niletrading.com',
                'phone' => '02-12345678',
                'address' => 'شارع التحرير، وسط البلد، القاهرة',
                'tax_number' => '123456789',
                'status' => 'active',
                'credit_limit' => 50000.00,
                'notes' => 'عميل مميز منذ 5 سنوات'
            ],
            [
                'customer_code' => 'CUST002',
                'name' => 'مؤسسة الهرم التجارية',
                'company_name' => 'مؤسسة الهرم للمقاولات والتجارة',
                'email' => 'contracts@pyramid-co.com',
                'phone' => '02-87654321',
                'address' => 'الهرم، الجيزة',
                'tax_number' => '987654321',
                'status' => 'active',
                'credit_limit' => 75000.00,
                'notes' => 'متعامل في المقاولات الكبيرة'
            ],
            [
                'customer_code' => 'CUST003',
                'name' => 'شركة الإسكندرية للصناعات',
                'company_name' => 'شركة الإسكندرية للصناعات الغذائية',
                'email' => 'sales@alex-industries.com',
                'phone' => '03-11223344',
                'address' => 'المنطقة الصناعية، الإسكندرية',
                'tax_number' => '456789123',
                'status' => 'active',
                'credit_limit' => 30000.00,
                'notes' => 'شركة متوسطة الحجم'
            ],
            [
                'customer_code' => 'CUST004',
                'name' => 'مكتب الدلتا للاستشارات',
                'company_name' => 'مكتب الدلتا للاستشارات الهندسية',
                'email' => 'info@delta-consultants.com',
                'phone' => '040-5566778',
                'address' => 'طنطا، الغربية',
                'tax_number' => '789123456',
                'status' => 'active',
                'credit_limit' => 25000.00,
                'notes' => 'مكتب استشارات متخصص'
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
