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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // رقم الموظف
            $table->string('first_name'); // الاسم الأول
            $table->string('last_name'); // اسم العائلة
            $table->string('email')->unique(); // البريد الإلكتروني
            $table->string('phone')->nullable(); // رقم الهاتف
            $table->text('address')->nullable(); // العنوان
            $table->string('department'); // القسم
            $table->string('position'); // المنصب
            $table->decimal('basic_salary', 10, 2); // الراتب الأساسي
            $table->date('hire_date'); // تاريخ التوظيف
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active'); // حالة الموظف
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
