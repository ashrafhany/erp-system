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

    // اسم مختصر للعرض (تستخدمه بعض الصفحات)
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    // الحرف الأول من الاسمين للأفاتار؛ mb_substr لأن الحروف العربية متعددة البايت،
    // والفاصل غير المرئي (ZWNJ) يمنع اتصال الحرفين ببعضهما في الخط العربي
    public function getInitialsAttribute(): string
    {
        return mb_substr((string) $this->first_name, 0, 1) . "\u{200C}" . mb_substr((string) $this->last_name, 0, 1);
    }

    // دالة للحصول على حضور اليوم
    public function getTodayAttendance()
    {
        return $this->attendances()->whereDate('date', today())->first();
    }
}
