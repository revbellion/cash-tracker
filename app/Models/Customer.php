<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
