<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = ['amount', 'payment_date', 'payment_method'];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'date'];
}
