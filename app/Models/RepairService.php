<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairService extends Model
{
    protected $fillable = [
        'date',
        'customer_name',
        'customer_phone',
        'device_type',
        'device_model',
        'issue_description',
        'service_fee',
        'sparepart_cost',
        'sparepart_description',
        'total',
        'account_id',
        'income_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'service_fee' => 'integer',
            'sparepart_cost' => 'integer',
            'total' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
