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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // معرف الموظف
            $table->date('date'); // تاريخ الحضور
            $table->time('check_in')->nullable(); // وقت الدخول
            $table->time('check_out')->nullable(); // وقت الخروج
            $table->integer('total_hours')->nullable(); // إجمالي ساعات العمل
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present'); // حالة الحضور
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();

            $table->unique(['employee_id', 'date']); // فهرس فريد لمنع تكرار تسجيل الحضور في نفس اليوم
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
