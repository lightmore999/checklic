<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Удаляем projects
        Schema::dropIfExists('projects');
        
        // Удаляем target_stats
        Schema::dropIfExists('target_stats');
        
        // Опционально: удаляем failed_jobs если не нужны
        // Schema::dropIfExists('failed_jobs');
    }

    public function down(): void
    {
        // Восстановление (если понадобится)
        // Можно оставить пустым или добавить код для создания таблиц обратно
    }
};