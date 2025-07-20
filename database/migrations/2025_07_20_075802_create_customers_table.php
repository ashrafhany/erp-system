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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique(); // رمز العميل
            $table->string('name'); // اسم العميل
            $table->string('company_name')->nullable(); // اسم الشركة
            $table->string('email')->nullable(); // البريد الإلكتروني
            $table->string('phone')->nullable(); // رقم الهاتف
            $table->text('address')->nullable(); // العنوان
            $table->string('tax_number')->nullable(); // الرقم الضريبي
            $table->enum('status', ['active', 'inactive'])->default('active'); // حالة العميل
            $table->decimal('credit_limit', 12, 2)->default(0); // حد الائتمان
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
