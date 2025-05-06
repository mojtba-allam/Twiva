<?php

namespace Modules\Category\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\app\Models\Product;
use Modules\Admin\app\Models\Admin;
use Modules\Category\database\factories\CategoryFactory;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriesFactory> */
    use HasFactory;
    protected $table = 'categories';
    public function Product(): HasMany
    {
        return $this->hasMany(Product::class,'category_id');
    }
    public function Admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class,'admin_id');
    }

    protected static function newFactory()
    {
        return \Modules\Category\database\factories\CategoryFactory::new();
        // return CategoryFactory::new();
    }
}
