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
        Schema::create('shift_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->date('report_date');
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->decimal('card_amount', 12, 2)->default(0);
            $table->decimal('transfer_amount', 12, 2)->default(0);
            $table->decimal('other_amount', 12, 2)->default(0);
            $table->integer('total_checkouts')->default(0);
            $table->integer('paid_checkouts')->default(0);
            $table->integer('unpaid_checkouts')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();
            
            // Index để tìm kiếm nhanh
            $table->index(['report_date', 'admin_id']);
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_reports');
    }
};
