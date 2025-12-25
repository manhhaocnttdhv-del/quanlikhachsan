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
        Schema::table('admins', function (Blueprint $table) {
            // Thay đổi enum role để thêm 'employee'
            $table->enum('role', ['admin', 'manager', 'employee'])->default('manager')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Revert về enum cũ (chỉ admin và manager)
            $table->enum('role', ['admin', 'manager'])->default('manager')->change();
        });
    }
};
