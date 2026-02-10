<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelegatedLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delegated_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('limit_id')->constrained('limits')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Уникальный индекс, чтобы один пользователь не мог получить один лимит дважды
            $table->unique(['user_id', 'limit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delegated_limits');
    }
}