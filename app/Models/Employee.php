<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'department',
        'position',
        'basic_salary',
        'hire_date',
        'status'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'basic_salary' => 'decimal:2'
    ];

    // العلاقة مع جدول الحضور
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // العلاقة مع جدول سجلات الرواتب
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    // دالة للحصول على الاسم الكامل
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // دالة للحصول على حضور اليوم
    public function getTodayAttendance()
    {
        return $this->attendances()->whereDate('date', today())->first();
    }
}
