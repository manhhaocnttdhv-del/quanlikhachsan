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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->string('room_type'); // Standard, Deluxe, Suite, VIP
            $table->integer('capacity'); // Số người tối đa
            $table->decimal('price_per_night', 10, 2);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // Tiện nghi: TV, WiFi, máy lạnh, etc.
            $table->string('image')->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
