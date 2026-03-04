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
        Schema::table('reports', function (Blueprint $table) {
            // Поля для отслеживания статусов API запросов
            $table->json('api_statuses')->nullable()->after('status')->comment('Статусы каждого API запроса (pending/completed/failed)');
            
            // Поля для хранения сырых ответов от API
            $table->json('api_responses')->nullable()->after('api_statuses')->comment('Сырые ответы от каждого API');
            
            // Поле для технической информации (IP, user-agent, время выполнения и т.д.)
            $table->json('meta_data')->nullable()->after('processed_data')->comment('Технические данные: IP, user-agent, тайминги, ошибки');
        });

        // ИСПРАВЛЕНО: Добавляем индексы с проверкой существования
        Schema::table('reports', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('reports');
            
            // Индекс для status
            if (!array_key_exists('reports_status_index', $indexesFound)) {
                $table->index('status');
            }
            
            // Индекс для created_at
            if (!array_key_exists('reports_created_at_index', $indexesFound)) {
                $table->index('created_at');
            }
            
            // Индекс для user_id + status
            if (!array_key_exists('reports_user_id_status_index', $indexesFound)) {
                $table->index(['user_id', 'status']);
            }
            
            // Индекс для report_type_id + status
            if (!array_key_exists('reports_report_type_id_status_index', $indexesFound)) {
                $table->index(['report_type_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['api_statuses', 'api_responses', 'meta_data']);
            
            // Удаляем индексы, если они существуют
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('reports');
            
            if (array_key_exists('reports_status_index', $indexesFound)) {
                $table->dropIndex('reports_status_index');
            }
            
            if (array_key_exists('reports_created_at_index', $indexesFound)) {
                $table->dropIndex('reports_created_at_index');
            }
            
            if (array_key_exists('reports_user_id_status_index', $indexesFound)) {
                $table->dropIndex('reports_user_id_status_index');
            }
            
            if (array_key_exists('reports_report_type_id_status_index', $indexesFound)) {
                $table->dropIndex('reports_report_type_id_status_index');
            }
        });
    }
};