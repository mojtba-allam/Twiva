<?php

namespace Modules\Order\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Product\app\Models\Product;
use Modules\User\app\Models\User;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'products_list',
        'total_quantity',
        'total_price',
        'status',
        'deleted_products'
    ];

    public function Product(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id');
    }
    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function newFactory()
    {
        return \Modules\Order\database\factories\OrderFactory::new();
    }
}
