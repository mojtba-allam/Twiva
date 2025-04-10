<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Products extends Model
{
    use HasFactory;

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

    public function businessAccount(): BelongsTo
    {
        return $this->belongsTo(BusinessAccount::class, 'business_account_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}
