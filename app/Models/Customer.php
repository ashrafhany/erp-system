<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'tax_number',
        'status',
        'credit_limit',
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2'
    ];

    // العلاقة مع جدول الفواتير
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // دالة للحصول على إجمالي المبالغ المستحقة
    public function getTotalOutstandingAmount(): float
    {
        return $this->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total_amount') - $this->invoices()->sum('paid_amount');
    }

    // دالة للتحقق من حد الائتمان
    public function isWithinCreditLimit(): bool
    {
        return $this->getTotalOutstandingAmount() <= $this->credit_limit;
    }
}
