<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title')->required();
            $table->string('admin_id')->required();
            $table->string('description')->required();
            $table->string('price')->required();
            $table->string('image_url')->nullable();
            $table->string('product_url')->nullable();
            $table->string('category_id')->required();
            $table->string('quantity')->required();
            $table->string('created_at')->required();
            $table->string('updated_at')->required();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
