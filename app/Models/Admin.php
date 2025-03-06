<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Admin extends Model
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory;
    public function Products(): HasMany
    {
        return $this->hasMany(Products::class, 'admin_id');
    }
    public function Categoreis(): HasMany
    {
        return $this->hasMany(Categories::class,'admin_id');
    }
}
