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
        Schema::table('limits', function (Blueprint $table) {
            // Делаем user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('limits', function (Blueprint $table) {
            // Возвращаем обратно NOT NULL (это может вызвать ошибку, если есть записи с NULL)
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};