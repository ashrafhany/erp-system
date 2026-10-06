<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\WebAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebAccessSeeder::class);
        $this->actingAs(User::findOrFail(1));
    }

    private function employee(string $id, string $first, string $last, string $department = 'المالية'): Employee
    {
        return Employee::create([
            'employee_id' => $id, 'first_name' => $first, 'last_name' => $last, 'email' => $id.'@example.com',
            'department' => $department, 'position' => 'محاسب', 'basic_salary' => 1000, 'hire_date' => now()->toDateString(), 'status' => 'active',
        ]);
    }

    public function test_dashboard_shows_charts_attendance_breakdown_and_low_stock(): void
    {
        $ahmed = $this->employee('E-1', 'أحمد', 'سالم');
        $mona = $this->employee('E-2', 'منى', 'علي');
        $this->employee('E-3', 'كريم', 'حسن');
        Attendance::create(['employee_id' => $ahmed->id, 'date' => today(), 'status' => 'present']);
        Attendance::create(['employee_id' => $mona->id, 'date' => today(), 'status' => 'late']);

        $customer = Customer::create(['customer_code' => 'C-1', 'name' => 'شركة النور', 'status' => 'active']);
        $invoice = Invoice::create([
            'invoice_number' => 'INV-1', 'customer_id' => $customer->id, 'invoice_date' => today(), 'due_date' => today()->addDays(10),
            'subtotal' => 500, 'tax_amount' => 0, 'discount_amount' => 0, 'total_amount' => 500, 'paid_amount' => 200, 'status' => 'sent',
        ]);
        $invoice->payments()->create(['amount' => 200, 'payment_date' => today(), 'payment_method' => 'cash']);

        Product::create(['name' => 'منتج ناقص', 'sku' => 'LOW-1', 'price' => 10, 'cost' => 5, 'category' => 'عام', 'status' => 'active']);

        $response = $this->get(route('dashboard'))->assertOk();

        $response->assertSee('id="salesChart"', false)
            ->assertSee('id="invoiceChart"', false)
            ->assertSee('INV-1')
            ->assertSee('منتج ناقص')
            ->assertSee('لم يُسجَّل')
            ->assertSee('title="1 منتجات مخزونها منخفض"', false) // رقم التنبيه في القائمة
            ->assertDontSee('فواتير متأخرة"', false)
            ->assertViewHas('attendanceRate', 67); // حاضر + متأخر من 3 موظفين

        // المتأخر لا يُحسب غائبًا، والموظف الثالث "لم يُسجَّل"
        $breakdown = collect($response->viewData('attendanceToday'))->pluck('value', 'label');
        $this->assertSame(1, $breakdown['حاضر']);
        $this->assertSame(1, $breakdown['متأخر']);
        $this->assertSame(0, $breakdown['غائب']);
        $this->assertSame(1, $breakdown['لم يُسجَّل']);

        $this->assertSame(200.0, $response->viewData('salesChart')['collected']->last());
        $this->assertSame(500.0, $response->viewData('salesChart')['sales']->last());
    }

    public function test_employee_initials_are_valid_utf8_and_search_filters_list(): void
    {
        $this->employee('E-1', 'أحمد', 'سالم', 'المالية');
        $this->employee('E-2', 'منى', 'علي', 'المبيعات');

        $this->get('/employees')->assertOk()->assertSee("أ\u{200C}س")->assertSee("م\u{200C}ع");
        $this->assertTrue(mb_check_encoding($this->get('/employees')->getContent(), 'UTF-8'));

        $this->get('/employees?search=أحمد سالم')->assertOk()->assertSee('E-1@example.com')->assertDontSee('E-2@example.com');
        $this->get('/employees?department=المبيعات')->assertOk()->assertSee('E-2@example.com')->assertDontSee('E-1@example.com');
        $this->get('/employees?search=غير-موجود')->assertOk()->assertSee('لا توجد نتائج مطابقة');
    }

    public function test_status_cards_count_all_records_not_only_current_page(): void
    {
        foreach (range(1, 17) as $i) {
            $employee = $this->employee('E-'.$i, 'موظف', (string) $i);
            PayrollRecord::create([
                'employee_id' => $employee->id, 'payroll_month' => now()->format('Y-m'), 'basic_salary' => 1000,
                'gross_salary' => 1000, 'net_salary' => 1000, 'status' => 'draft',
            ]);
        }

        $this->get('/payroll')->assertOk()->assertViewHas('statusCounts', fn ($counts) => $counts['draft'] === 17);
    }
}
