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
        Schema::table('users', function (Blueprint $table) {
            // Добавляем поле role с enum значениями
            $table->enum('role', ['admin', 'manager', 'org_owner', 'org_member'])
                  ->default('org_member')
                  ->after('password');
            
            // Добавляем поле is_active
            $table->boolean('is_active')
                  ->default(true)
                  ->after('role');
            
            // Добавляем индекс для оптимизации запросов по роли и активности
            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем индекс
            $table->dropIndex(['role', 'is_active']);
            
            // Удаляем поля
            $table->dropColumn(['role', 'is_active']);
        });
    }
};