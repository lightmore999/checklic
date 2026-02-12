<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToLimitsTable extends Migration
{
    public function up()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('user_id');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
}