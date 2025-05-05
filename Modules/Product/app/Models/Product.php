<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Business\app\Models\Business;
use Modules\Order\app\Models\Order;
use Modules\Category\app\Models\Category;
class Product extends Model
{
    use HasFactory;
    protected $table = 'product';
    protected $fillable = [
        'title',
        'description',
        'price',
        'quantity',
        'image_url',
        'business_account_id',
        'category_id',
        'status',
        'rejection_reason'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DELETED = 'deleted';

    // Scope for approved products only
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // Scope for pending products
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_account_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
