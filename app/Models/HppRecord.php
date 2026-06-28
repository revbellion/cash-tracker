<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HppRecord extends Model
{
    protected $fillable = [
        'date',
        'product_category_id',
        'product_id',
        'income_id',
        'receipt_id',
        'qty',
        'hpp_amount',
        'fifo_batches',
        'selling_amount',
        'profit_amount',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'fifo_batches' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
