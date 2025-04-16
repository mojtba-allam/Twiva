<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // Creates notifiable_type and notifiable_id
            $table->string('type'); // Type of notification (e.g., 'product_approved', 'new_order')
            $table->string('title'); // Notification title
            $table->text('message'); // Notification message
            $table->json('data')->nullable(); // Additional data
            $table->boolean('read')->default(false); // Read status
            $table->timestamp('read_at')->nullable(); // When it was read
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};