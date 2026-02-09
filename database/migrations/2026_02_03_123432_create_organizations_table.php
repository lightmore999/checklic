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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // название организации должно быть уникальным
            $table->foreignId('manager_id')
                  ->constrained('users')
                  ->onDelete('restrict'); // менеджер, который создал организацию
            
            // Поля для подписки/срока действия
            $table->timestamp('subscription_ends_at')->nullable(); // когда истекает доступ
            $table->enum('status', ['active', 'suspended', 'expired'])
                  ->default('active');
            
            $table->timestamps();
            
            // Индексы
            $table->index('manager_id');
            $table->index('status');
            $table->index(['status', 'subscription_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};