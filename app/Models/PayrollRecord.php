<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_month',
        'basic_salary',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'allowances',
        'deductions',
        'gross_salary',
        'tax_amount',
        'net_salary',
        'status',
        'payment_date',
        'notes'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date'
    ];

    // العلاقة مع جدول الموظفين
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // دالة لحساب الراتب الإجمالي والصافي
    public function calculateSalary()
    {
        $this->overtime_amount = $this->overtime_hours * $this->overtime_rate;
        $this->gross_salary = $this->basic_salary + $this->overtime_amount + $this->allowances;
        $this->net_salary = $this->gross_salary - $this->deductions - $this->tax_amount;
        $this->save();
    }

    // دالة للحصول على اسم الشهر بالعربية
    public function getFormattedMonthAttribute(): string
    {
        $months = [
            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
            '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
            '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
        ];

        [$year, $month] = explode('-', $this->payroll_month);
        return $months[$month] . ' ' . $year;
    }
}
