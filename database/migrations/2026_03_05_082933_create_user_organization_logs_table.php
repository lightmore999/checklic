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
        Schema::create('user_organization_logs', function (Blueprint $table) {
            $table->id();
            
            // Кто совершил действие (админ, менеджер, система)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Какая сущность была изменена (полиморфная связь)
            $table->string('entity_type'); // user, organization, manager, org_owner, org_member
            $table->unsignedBigInteger('entity_id');
            
            // Тип действия
            $table->enum('action', ['create', 'update', 'delete', 'restore', 'login', 'logout', 'status_change']);
            
            // Данные до и после
            $table->json('old_data')->nullable();  // что было до изменения
            $table->json('new_data')->nullable();  // что стало после
            
            // Техническая информация
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Для группировки связанных действий (например, при создании пользователя и его профиля)
            $table->uuid('batch_id')->nullable()->index();
            
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_organization_logs');
    }
};