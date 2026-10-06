<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use App\Models\User;
use Database\Seeders\WebAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsAndPrintWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebAccessSeeder::class);
        $this->actingAs(User::findOrFail(1));
    }

    private function employee(): Employee
    {
        return Employee::create([
            'employee_id' => 'E-1', 'first_name' => 'أحمد', 'last_name' => 'سالم', 'email' => 'a@example.com',
            'department' => 'المالية', 'position' => 'محاسب', 'basic_salary' => 1000, 'hire_date' => '2026-01-01', 'status' => 'active',
        ]);
    }

    private function invoice(array $overrides = []): Invoice
    {
        $customer = Customer::firstOrCreate(['customer_code' => 'C-1'], ['name' => 'شركة النور', 'status' => 'active']);
        $invoice = Invoice::create($overrides + [
            'invoice_number' => 'INV-'.uniqid(), 'customer_id' => $customer->id,
            'invoice_date' => '2026-10-01', 'due_date' => '2026-10-20',
            'subtotal' => 100, 'tax_amount' => 0, 'discount_amount' => 0,
            'total_amount' => 100, 'paid_amount' => 0, 'status' => 'sent',
        ]);
        $invoice->items()->create(['description' => 'خدمة استشارية', 'quantity' => 1, 'unit_price' => 100, 'total_price' => 100]);

        return $invoice;
    }

    public function test_employee_name_is_shown_on_payroll_pages(): void
    {
        $payroll = PayrollRecord::create([
            'employee_id' => $this->employee()->id, 'payroll_month' => '2026-10', 'basic_salary' => 1000,
            'allowances' => 200, 'gross_salary' => 1200, 'net_salary' => 1200, 'status' => 'draft',
        ]);

        $this->get('/payroll/'.$payroll->id)->assertOk()->assertSee('سجل راتب: أحمد سالم')->assertSee('مسودة');
        $this->get('/payroll/'.$payroll->id.'/print')->assertOk()->assertSee('كشف راتب')->assertSee('أحمد سالم')->assertSee('1,200.00');
    }

    public function test_invoice_print_page(): void
    {
        $invoice = $this->invoice(['paid_amount' => 40]);

        $this->get('/invoices/'.$invoice->id)->assertOk()->assertSee(route('invoices.print', $invoice));
        $this->get('/invoices/'.$invoice->id.'/print')->assertOk()
            ->assertSee($invoice->invoice_number)->assertSee('شركة النور')->assertSee('خدمة استشارية')->assertSee('60.00');
    }

    public function test_reports_show_correct_totals(): void
    {
        $employee = $this->employee();
        PayrollRecord::create([
            'employee_id' => $employee->id, 'payroll_month' => '2026-10', 'basic_salary' => 1000,
            'gross_salary' => 1000, 'deductions' => 100, 'net_salary' => 900, 'status' => 'approved',
        ]);
        foreach (['2026-10-01' => 'present', '2026-10-02' => 'late', '2026-10-03' => 'absent', '2026-11-01' => 'present'] as $date => $status) {
            Attendance::create(['employee_id' => $employee->id, 'date' => $date, 'status' => $status, 'total_hours' => $status === 'absent' ? null : 8]);
        }
        $this->invoice(['due_date' => '2020-01-01', 'paid_amount' => 30]);
        $this->invoice(['due_date' => now()->addMonth()->toDateString()]);
        $this->invoice(['status' => 'paid', 'paid_amount' => 100]);
        $this->invoice(['status' => 'draft']);

        $this->get('/reports')->assertOk()->assertSee('كشف الرواتب الشهري')->assertSee('مديونيات العملاء');
        $this->get('/reports/payroll?month=2026-10')->assertOk()->assertSee('أحمد سالم')->assertSee('900.00');
        $this->get('/reports/payroll?month=2026-09')->assertOk()->assertSee('لا توجد سجلات رواتب');

        $this->get('/reports/attendance?month=2026-10')->assertOk()
            ->assertViewHas('stats', fn ($stats) => (int) $stats[$employee->id]->present_days === 1
                && (int) $stats[$employee->id]->late_days === 1
                && (int) $stats[$employee->id]->absent_days === 1
                && (float) $stats[$employee->id]->hours === 16.0);

        $this->get('/reports/receivables')->assertOk()
            ->assertViewHas('totalRemaining', fn ($total) => (float) $total === 170.0)
            ->assertViewHas('totalOverdue', fn ($total) => (float) $total === 70.0);

        $this->get('/reports/sales?from=2026-10-01&to=2026-10-31')->assertOk()
            ->assertViewHas('totals', fn ($totals) => $totals['count'] === 3 && (float) $totals['total'] === 300.0 && (float) $totals['paid'] === 130.0);
        $this->get('/reports/sales?from=2026-10-31&to=2026-10-01')->assertSessionHasErrors('to');
    }

    public function test_reports_require_both_report_and_section_permissions(): void
    {
        $role = Role::create(['name' => 'reports-only', 'guard_name' => 'admin']);
        $role->givePermissionTo(['reports.view', 'invoices.view']);
        $user = User::create(['name' => 'تقارير', 'email' => 'r@example.com', 'password' => Hash::make('secret123')]);
        $user->assignRole($role);
        $this->actingAs($user);

        $this->get('/reports')->assertOk()->assertSee('مديونيات العملاء')->assertDontSee('كشف الرواتب الشهري');
        $this->get('/reports/sales')->assertOk();
        $this->get('/reports/payroll')->assertForbidden();
        $this->get('/reports/attendance')->assertForbidden();

        $role->revokePermissionTo('reports.view');
        $this->get('/reports/sales')->assertForbidden();
    }
}
