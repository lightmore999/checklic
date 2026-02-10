<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            
            // Основные поля
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('report_type_id')->constrained('report_types')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled
            
            // Данные запроса (поисковые поля)
            $table->string('last_name')->nullable(); // Фамилия
            $table->string('first_name')->nullable(); // Имя
            $table->string('patronymic')->nullable(); // Отчество
            $table->date('birth_date')->nullable(); // Дата рождения
            $table->string('region')->nullable(); // Регион проживания
            $table->string('passport_series', 4)->nullable(); // Серия паспорта
            $table->string('passport_number', 6)->nullable(); // Номер паспорта
            $table->date('passport_date')->nullable(); // Дата выдачи паспорта
            $table->string('vehicle_number')->nullable(); // Номер транспортного средства
            $table->string('cadastral_number')->nullable(); // Кадастровый номер
            $table->string('property_type')->nullable(); // Тип недвижимости
            
            // Результат
            $table->json('response_data')->nullable();
            
            // Системные поля
            $table->integer('quantity_used')->default(1); // Количество использованных лимитов
            $table->foreignId('limit_id')->nullable()->constrained('limits')->onDelete('set null'); // Связь с лимитом
            $table->foreignId('delegated_limit_id')->nullable()->constrained('delegated_limits')->onDelete('set null'); // Или с делегированным лимитом
            
            // Индексы для поиска
            $table->index(['last_name', 'first_name', 'patronymic']);
            $table->index('passport_series');
            $table->index('passport_number');
            $table->index('vehicle_number');
            $table->index('cadastral_number');
            $table->index('status');
            $table->index(['user_id', 'created_at']);
            
            $table->timestamps();
            $table->timestamp('processed_at')->nullable();
        });

        // Добавляем виртуальные поля через DB::statement (для PostgreSQL)
        DB::statement('ALTER TABLE reports ADD COLUMN passport_full VARCHAR(255) GENERATED ALWAYS AS (passport_series || \' \' || passport_number) STORED');
        DB::statement('ALTER TABLE reports ADD COLUMN full_name VARCHAR(255) GENERATED ALWAYS AS (last_name || \' \' || first_name || \' \' || patronymic) STORED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};