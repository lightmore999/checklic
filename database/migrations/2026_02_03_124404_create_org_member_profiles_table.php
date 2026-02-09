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
        Schema::create('org_member_profiles', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем типа org_member
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Связь с организацией
            $table->foreignId('organization_id')
                  ->constrained('organizations')
                  ->onDelete('cascade');
            
            // Владелец организации (boss) - кто создал этого сотрудника
            $table->foreignId('boss_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            
            // Менеджер, который в итоге контролирует эту организацию
            $table->foreignId('manager_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            
            // Дополнительные поля для сотрудника
            $table->string('position')->nullable(); // должность
            $table->string('department')->nullable(); // отдел
            $table->boolean('is_active')->default(true); // активен ли сотрудник
            
            $table->timestamps();
            
            // Индексы для оптимизации
            $table->index('user_id');
            $table->index('organization_id');
            $table->index('boss_id');
            $table->index('manager_id');
            $table->index(['organization_id', 'is_active']);
            $table->index(['boss_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_member_profiles');
    }
};