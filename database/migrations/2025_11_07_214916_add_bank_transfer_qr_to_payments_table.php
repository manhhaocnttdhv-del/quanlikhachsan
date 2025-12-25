<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL không hỗ trợ ALTER ENUM trực tiếp, cần sử dụng raw SQL
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'credit_card', 'bank_transfer', 'momo', 'vnpay', 'bank_transfer_qr') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa bank_transfer_qr khỏi enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'credit_card', 'bank_transfer', 'momo', 'vnpay') DEFAULT 'cash'");
    }
};
