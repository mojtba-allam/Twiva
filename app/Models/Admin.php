<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
class Admin extends Authenticatable
{
    protected $hidden = ['password', 'created_at', 'updated_at','remember_token'];

    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory;
    use HasApiTokens;
    public function Products(): HasMany
    {
        return $this->hasMany(Products::class, 'admin_id');
    }
    public function Categoreis(): HasMany
    {
        return $this->hasMany(Categories::class,'admin_id');
    }
}
