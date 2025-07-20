<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'status',
        'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    // العلاقة مع جدول العملاء
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // العلاقة مع جدول عناصر الفاتورة
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // دالة للحصول على المبلغ المتبقي
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    // دالة للتحقق من حالة الدفع
    public function getPaymentStatusAttribute(): string
    {
        if ($this->paid_amount == 0) {
            return 'غير مدفوع';
        } elseif ($this->paid_amount < $this->total_amount) {
            return 'مدفوع جزئياً';
        } else {
            return 'مدفوع بالكامل';
        }
    }

    // دالة لحساب إجمالي الفاتورة
    public function calculateTotal()
    {
        $this->subtotal = $this->items()->sum('total_price');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }
}
