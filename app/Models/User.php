<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    public function Orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
