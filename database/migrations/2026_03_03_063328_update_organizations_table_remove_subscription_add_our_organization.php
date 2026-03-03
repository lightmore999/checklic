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
            // Удаляем поле subscription_ends_at
            $table->dropColumn('subscription_ends_at');

            // Добавляем поле "Наша организация"
            $table->string('our_organization')->nullable()->after('name');
            // Или если нужно длинное название:
            // $table->text('our_organization')->nullable()->after('name');

            // Альтернативные варианты названий:
            // $table->string('company_name')->nullable()->after('name');
            // $table->string('legal_name')->nullable()->after('name');
            // $table->string('full_name')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Возвращаем subscription_ends_at
            $table->timestamp('subscription_ends_at')->nullable();

            // Удаляем добавленное поле
            $table->dropColumn('our_organization');
        });
    }
};
