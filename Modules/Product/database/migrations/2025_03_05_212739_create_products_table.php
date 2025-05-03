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
            $table->string('title');
            $table->unsignedBigInteger('business_account_id');
            $table->string('description');
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->string('product_url')->nullable();
            $table->string('category_id');
            $table->integer('quantity');
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
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
