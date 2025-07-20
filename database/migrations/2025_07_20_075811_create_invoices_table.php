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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // رقم الفاتورة
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // معرف العميل
            $table->date('invoice_date'); // تاريخ الفاتورة
            $table->date('due_date'); // تاريخ الاستحقاق
            $table->decimal('subtotal', 12, 2); // المجموع الفرعي
            $table->decimal('tax_amount', 12, 2)->default(0); // مبلغ الضريبة
            $table->decimal('discount_amount', 12, 2)->default(0); // مبلغ الخصم
            $table->decimal('total_amount', 12, 2); // المبلغ الإجمالي
            $table->decimal('paid_amount', 12, 2)->default(0); // المبلغ المدفوع
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft'); // حالة الفاتورة
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
