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
        Schema::table('organizations', function (Blueprint $table) {
            // Добавляем поле ИНН
            $table->string('inn', 12)->nullable()->after('name');
            
            // Добавляем поле максимальное количество сотрудников
            $table->integer('max_employees')->nullable()->default(null)->after('status');
            
            // Добавляем индекс для ИНН на случай поиска
            $table->index('inn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['inn']);
            $table->dropColumn(['inn', 'max_employees']);
        });
    }
};