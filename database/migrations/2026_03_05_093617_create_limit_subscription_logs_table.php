<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('limit_subscription_logs', function (Blueprint $table) {
            $table->id();
            
            // Кто совершил действие
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Тип сущности (limit, delegated_limit, subscription)
            $table->string('entity_type'); 
            $table->unsignedBigInteger('entity_id');
            
            // Тип действия
            $table->enum('action', [
                'create', 'update', 'delete', 
                'activate', 'suspend', 'cancel', 'extend',
                'use_quantity', 'return_quantity', 'delegate'
            ]);
            
            // Данные до и после
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            
            // Специфичные для лимитов поля
            $table->integer('quantity_change')->nullable(); // на сколько изменилось количество
            $table->integer('old_quantity')->nullable();    // старое количество
            $table->integer('new_quantity')->nullable();    // новое количество
            
            // Для подписок
            $table->timestamp('old_ends_at')->nullable();
            $table->timestamp('new_ends_at')->nullable();
            
            // Техническая информация
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->uuid('batch_id')->nullable()->index();
            
            $table->timestamps();
            
            // Индексы
            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limit_subscription_logs');
    }
};