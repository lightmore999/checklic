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
        Schema::create('org_owner_profiles', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем типа org_owner
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Связь с организацией
            $table->foreignId('organization_id')
                  ->unique()
                  ->constrained('organizations')
                  ->onDelete('cascade');
            
            // Менеджер, который создал этого владельца
            $table->foreignId('manager_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            
            $table->timestamps();
            
            // Индексы
            $table->index('user_id');
            $table->index('organization_id');
            $table->index('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_owner_profiles');
    }
};