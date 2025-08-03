<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity_change',
        'type', // purchase, sale, adjustment, return
        'reference', // invoice_id, purchase_id, etc.
        'notes',
        'unit_cost',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_change' => 'integer',
        'unit_cost' => 'float',
    ];

    /**
     * Get the product that owns the inventory record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include stock additions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdditions($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    /**
     * Scope a query to only include stock reductions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReductions($query)
    {
        return $query->where('quantity_change', '<', 0);
    }
}
