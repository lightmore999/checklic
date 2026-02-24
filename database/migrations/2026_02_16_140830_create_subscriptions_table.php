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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Внешний ключ на пользователя (владельца организации)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Дата начала подписки
            $table->timestamp('starts_at')->nullable();
            
            // Дата окончания подписки
            $table->timestamp('ends_at')->nullable();
            
            // Статус подписки: active, suspended, expired, cancelled, pending
            $table->string('status')->default('pending');
            
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index('ends_at');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};