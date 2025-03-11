<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Products extends Model
{
    protected $fillable = ['title', 'description', 'price', 'quantity', 'image_url','admin_id','category_id'];
    /** @use HasFactory<\Database\Factories\ProductsFactory> */
    use HasFactory;
    /**
     * Get all of the Products for the Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'foreign_key', 'local_key');
    }
    public function Orders(): HasMany
    {
        return $this->hasMany(Order::class, 'foreign_key');
    }
    public function Categories(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'foreign_key');
    }
}
