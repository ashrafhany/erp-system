<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use App\Models\User;
use Database\Seeders\WebAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebCrudWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebAccessSeeder::class);
        $this->actingAs(User::findOrFail(1));
    }

    public function test_department_permissions_are_seeded_and_crud_updates_employee_department(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'departments.view', 'guard_name' => 'admin']);
        $this->get('/roles/1/edit')->assertSee('departments.view');
        $permissions = User::findOrFail(1)->getAllPermissions()->pluck('name')->all();
        $this->put('/roles/1', ['name' => 'super-admin', 'permissions' => $permissions])->assertRedirect('/roles');
        $this->post('/departments', ['name' => 'الجودة'])->assertRedirect('/departments');
        $department = Department::where('name', 'الجودة')->firstOrFail();
        $employee = $this->employee(['department' => 'الجودة']);

        $this->put('/departments/'.$department->id, ['name' => 'ضمان الجودة'])->assertRedirect('/departments');
        $this->assertSame('ضمان الجودة', $employee->fresh()->department);
        $this->delete('/departments/'.$department->id)->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
        $this->get('/payroll/create')->assertSee($employee->full_name.' - ضمان الجودة')->assertSee('data-salary="1000.00"', false)->assertSee('ج.م');
    }

    public function test_invoice_items_payments_and_state_guards(): void
    {
        $customer = Customer::create(['customer_code' => 'C-100', 'name' => 'عميل', 'status' => 'active']);
        $this->post('/invoices', [
            'customer_id' => $customer->id,
            'invoice_date' => '2026-10-05',
            'due_date' => '2026-10-20',
            'tax_amount' => 10,
            'discount_amount' => 5,
            'items' => [['description' => 'خدمة', 'quantity' => 2, 'unit_price' => 50]],
        ])->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('105.00', $invoice->fresh()->total_amount);
        $this->assertSame(1, $invoice->items()->count());
        $this->get('/invoices/create?duplicate='.$invoice->id)
            ->assertOk()
            ->assertViewHas('initialItems', fn ($items) => $items[0]['description'] === 'خدمة');

        $this->put('/invoices/'.$invoice->id, [
            'customer_id' => $customer->id,
            'invoice_date' => '2026-10-05',
            'due_date' => '2026-10-20',
            'items' => [['id' => $invoice->items()->first()->id, 'description' => 'خدمة معدلة', 'quantity' => 3, 'unit_price' => 50]],
        ])->assertRedirect('/invoices');
        $this->assertSame('150.00', $invoice->fresh()->total_amount);

        $this->post('/invoices/'.$invoice->id.'/send')->assertRedirect();
        $this->post('/invoices/'.$invoice->id.'/payment', [
            'payment_amount' => 50,
            'payment_date' => '2026-10-05',
            'payment_method' => 'cash',
        ])->assertRedirect();
        $this->assertSame('50.00', $invoice->fresh()->paid_amount);
        $this->assertDatabaseHas('invoice_payments', ['invoice_id' => $invoice->id, 'amount' => 50]);
        $this->get('/invoices/'.$invoice->id)->assertOk()->assertSee('مدفوعة جزئيًا')->assertSee('100.00');
        $this->post('/invoices/'.$invoice->id.'/payment', [
            'payment_amount' => 100,
            'payment_date' => '2026-10-06',
            'payment_method' => 'bank_transfer',
        ])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->get('/invoices/'.$invoice->id.'/edit')->assertOk()->assertSee('البنود والمبالغ مقفولة');
        $this->put('/invoices/'.$invoice->id, [
            'customer_id' => $customer->id,
            'invoice_date' => '2026-10-05',
            'due_date' => '2026-10-20',
            'notes' => 'ملاحظة بعد الدفع',
        ])->assertRedirect('/invoices');
        $this->assertSame('150.00', $invoice->fresh()->total_amount);
        $this->assertSame('ملاحظة بعد الدفع', $invoice->fresh()->notes);

        $this->get('/invoices/'.$invoice->id)->assertOk()->assertSee('تصحيح المبلغ المدفوع');
        $this->post('/invoices/'.$invoice->id.'/reconcile', [
            'paid_amount' => 40,
            'reason' => 'تصحيح دفعة مسجلة بالخطأ',
        ])->assertRedirect();
        $this->assertSame('40.00', $invoice->fresh()->paid_amount);
        $this->assertSame('sent', $invoice->fresh()->status);
        $this->assertDatabaseHas('invoice_payment_adjustments', ['invoice_id' => $invoice->id, 'old_amount' => 150, 'new_amount' => 40]);
    }

    public function test_approved_payroll_cannot_be_changed_by_direct_request(): void
    {
        $employee = $this->employee();
        $payroll = PayrollRecord::create([
            'employee_id' => $employee->id, 'payroll_month' => '2026-10', 'basic_salary' => 1000,
            'gross_salary' => 1000, 'net_salary' => 1000, 'status' => 'draft',
        ]);
        $this->post('/payroll/approve/'.$payroll->id)->assertRedirect();
        $this->assertNull($payroll->fresh()->payment_date);
        $this->delete('/payroll/'.$payroll->id)->assertForbidden();
        $this->get('/payroll/'.$payroll->id.'/edit')->assertForbidden();
        $this->post('/payroll/pay/'.$payroll->id, ['payment_date' => '2026-10-06'])->assertRedirect();
        $this->assertSame('paid', $payroll->fresh()->status);
        $this->delete('/employees/'.$employee->id)->assertSessionHas('error');
    }

    public function test_attendance_keeps_fractional_hours(): void
    {
        $employee = $this->employee();
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => '2026-10-05',
            'check_in' => '09:00:00',
            'check_out' => '17:30:00',
            'status' => 'present',
        ]);
        $attendance->calculateTotalHours();
        $this->assertSame('8.50', $attendance->fresh()->total_hours);
    }

    public function test_legacy_overpayment_shows_review_instead_of_negative_payment_limit(): void
    {
        $customer = Customer::create(['customer_code' => 'C-200', 'name' => 'عميل قديم', 'status' => 'active']);
        $invoice = Invoice::create([
            'invoice_number' => 'INV-LEGACY', 'customer_id' => $customer->id,
            'invoice_date' => '2026-10-01', 'due_date' => '2026-10-20',
            'subtotal' => 100, 'tax_amount' => 0, 'discount_amount' => 0,
            'total_amount' => 100, 'paid_amount' => 200, 'status' => 'sent',
        ]);

        $this->get('/invoices')->assertOk()->assertSee('فواتير ببيانات دفع غير متسقة');
        $this->get('/invoices/'.$invoice->id)->assertOk()->assertSee('تصحيح المبلغ المدفوع')->assertDontSee('name="payment_amount"', false);
        $this->post('/invoices/'.$invoice->id.'/payment', [
            'payment_amount' => 10, 'payment_date' => '2026-10-05', 'payment_method' => 'cash',
        ])->assertSessionHas('error');
        $this->post('/invoices/'.$invoice->id.'/reconcile', ['paid_amount' => 50, 'reason' => 'تصحيح بيانات قديمة'])->assertRedirect();
        $this->assertSame('50.00', $invoice->fresh()->paid_amount);
    }

    private function employee(array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'employee_id' => 'E-100', 'first_name' => 'أحمد', 'last_name' => 'محمد',
            'email' => 'employee@example.test', 'department' => 'المحاسبة', 'position' => 'موظف',
            'basic_salary' => 1000, 'hire_date' => '2026-01-01', 'status' => 'active',
        ], $attributes));
    }
}
