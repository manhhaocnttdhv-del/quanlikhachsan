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
        Schema::create('booking_additional_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('service_name'); // Tên dịch vụ: nước, mì tôm, giặt đồ...
            $table->integer('quantity')->default(1); // Số lượng
            $table->decimal('unit_price', 10, 2); // Giá đơn vị
            $table->decimal('total_price', 10, 2); // Tổng tiền = quantity * unit_price
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_additional_charges');
    }
};
