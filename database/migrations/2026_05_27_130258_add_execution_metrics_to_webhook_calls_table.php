<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('webhook_calls', function (Blueprint $table) {
            $table->string('execution_time')->nullable()->after('response_body');
            $table->string('memory_usage')->nullable()->after('execution_time');
        });
    }

    public function down()
    {
        Schema::table('webhook_calls', function (Blueprint $table) {
            $table->dropColumn(['execution_time', 'memory_usage']);
        });
    }
};