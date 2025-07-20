<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'أحمد',
                'last_name' => 'محمد',
                'email' => 'ahmed.mohamed@company.com',
                'phone' => '01012345678',
                'address' => 'القاهرة، مصر',
                'department' => 'تقنية المعلومات',
                'position' => 'مطور برمجيات',
                'basic_salary' => 8000.00,
                'hire_date' => '2024-01-15',
                'status' => 'active'
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'فاطمة',
                'last_name' => 'علي',
                'email' => 'fatma.ali@company.com',
                'phone' => '01123456789',
                'address' => 'الجيزة، مصر',
                'department' => 'المحاسبة',
                'position' => 'محاسب أول',
                'basic_salary' => 7000.00,
                'hire_date' => '2024-02-01',
                'status' => 'active'
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'محمد',
                'last_name' => 'أحمد',
                'email' => 'mohamed.ahmed@company.com',
                'phone' => '01234567890',
                'address' => 'الإسكندرية، مصر',
                'department' => 'المبيعات',
                'position' => 'مدير المبيعات',
                'basic_salary' => 10000.00,
                'hire_date' => '2023-11-10',
                'status' => 'active'
            ],
            [
                'employee_id' => 'EMP004',
                'first_name' => 'سارة',
                'last_name' => 'حسن',
                'email' => 'sara.hassan@company.com',
                'phone' => '01345678901',
                'address' => 'القاهرة، مصر',
                'department' => 'الموارد البشرية',
                'position' => 'أخصائي موارد بشرية',
                'basic_salary' => 6500.00,
                'hire_date' => '2024-03-05',
                'status' => 'active'
            ],
            [
                'employee_id' => 'EMP005',
                'first_name' => 'عمر',
                'last_name' => 'خالد',
                'email' => 'omar.khaled@company.com',
                'phone' => '01456789012',
                'address' => 'المنصورة، مصر',
                'department' => 'التسويق',
                'position' => 'مختص تسويق رقمي',
                'basic_salary' => 5500.00,
                'hire_date' => '2024-04-20',
                'status' => 'active'
            ]
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
