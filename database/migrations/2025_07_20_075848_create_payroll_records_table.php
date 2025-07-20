<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // معرف الموظف
            $table->string('payroll_month'); // شهر الراتب (YYYY-MM)
            $table->decimal('basic_salary', 10, 2); // الراتب الأساسي
            $table->decimal('overtime_hours', 5, 2)->default(0); // ساعات إضافية
            $table->decimal('overtime_rate', 10, 2)->default(0); // معدل الساعة الإضافية
            $table->decimal('overtime_amount', 10, 2)->default(0); // مبلغ الساعات الإضافية
            $table->decimal('allowances', 10, 2)->default(0); // البدلات
            $table->decimal('deductions', 10, 2)->default(0); // الخصومات
            $table->decimal('gross_salary', 10, 2); // الراتب الإجمالي
            $table->decimal('tax_amount', 10, 2)->default(0); // مبلغ الضريبة
            $table->decimal('net_salary', 10, 2); // الراتب الصافي
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft'); // حالة الراتب
            $table->date('payment_date')->nullable(); // تاريخ الدفع
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();

            $table->unique(['employee_id', 'payroll_month']); // فهرس فريد لمنع تكرار راتب نفس الشهر
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
