<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    // العلاقة مع جدول الفواتير
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // دالة لحساب السعر الإجمالي عند الحفظ
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            $item->invoice->calculateTotal();
        });

        static::deleted(function ($item) {
            $item->invoice->calculateTotal();
        });
    }
}
