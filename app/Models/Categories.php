<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Categories extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriesFactory> */
    use HasFactory;
    public function Products(): HasMany
    {
        return $this->hasMany(Products::class,'category_id');
    }
    public function Admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class,'admin_id');
    }
}
