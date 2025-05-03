<?php

namespace Modules\Admin\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Modules\Product\app\Models\Product;
use Modules\Category\app\Models\Category;
class Admin extends Authenticatable
{
    protected $hidden = ['password', 'created_at', 'updated_at','remember_token'];

    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory;
    use HasApiTokens;
    public function Product(): HasMany
    {
        return $this->hasMany(Product::class, 'admin_id');
    }
    public function Categoreis(): HasMany
    {
        return $this->hasMany(Category::class,'admin_id');
    }
}
