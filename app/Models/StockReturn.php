<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReturn extends Model
{
    protected $fillable = [
        'type',
        'receipt_id',
        'product_id',
        'qty',
        'price',
        'total',
        'reason',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'qty' => 'integer',
            'price' => 'integer',
            'total' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeSales($query)
    {
        return $query->where('type', 'sales');
    }

    public function scopePurchase($query)
    {
        return $query->where('type', 'purchase');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'sales' ? 'Retur Jual' : 'Retur Beli';
    }

    public function getTypeBadgeAttribute(): string
    {
        if ($this->type === 'sales') {
            return '<span class="badge bg-warning text-dark">Retur Jual</span>';
        }
        return '<span class="badge bg-info text-dark">Retur Beli</span>';
    }
}
