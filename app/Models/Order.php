<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'products_list',
        'total_quantity',
        'total_price',
        'status'
    ];

    public function Products(): HasMany
    {
        return $this->hasMany(Products::class,'product_id');
    }
    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
