<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'total_hours',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s'
    ];

    // العلاقة مع جدول الموظفين
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // دالة لحساب إجمالي ساعات العمل
    public function calculateTotalHours()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->check_in);
            $checkOut = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->check_out);

            $totalMinutes = $checkOut->diffInMinutes($checkIn);
            $this->total_hours = round($totalMinutes / 60, 2);
            $this->save();
        }
    }

    // دالة للحصول على وقت الدخول المنسق
    public function getFormattedCheckInAttribute()
    {
        return $this->check_in ? Carbon::parse($this->check_in)->format('H:i') : null;
    }

    // دالة للحصول على وقت الخروج المنسق
    public function getFormattedCheckOutAttribute()
    {
        return $this->check_out ? Carbon::parse($this->check_out)->format('H:i') : null;
    }

    // دالة للحصول على حالة الحضور باللغة العربية
    public function getStatusInArabicAttribute()
    {
        $statusMap = [
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'half_day' => 'نصف يوم'
        ];

        return $statusMap[$this->status] ?? $this->status;
    }
}
