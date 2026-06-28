<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    protected $fillable = [
        'product_id', 'type', 'qty', 'remaining_qty', 'price', 'account_id',
        'description', 'date', 'expired_at', 'income_id', 'receipt_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

}
