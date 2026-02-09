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
        Schema::create('limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('report_type_id')->constrained('report_types')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->date('date_created');
            $table->timestamps();
            
            // Уникальный ключ, чтобы у пользователя не было дубликатов лимитов для одного типа отчета
            $table->unique(['user_id', 'report_type_id', 'date_created']);
            
            // Индексы для быстрого поиска
            $table->index('user_id');
            $table->index('report_type_id');
            $table->index('date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limits');
    }
};