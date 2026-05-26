<?php
// database/migrations/2026_02_26_000001_add_status_to_webhook_calls_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('webhook_calls', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('exception');
            $table->integer('retry_count')->default(0)->after('status');
            $table->timestamp('processed_at')->nullable()->after('retry_count');
            $table->integer('response_code')->nullable()->after('processed_at');
            $table->json('response_body')->nullable()->after('response_code');
        });
    }

    public function down()
    {
        Schema::table('webhook_calls', function (Blueprint $table) {
            $table->dropColumn(['status', 'retry_count', 'processed_at', 'response_code', 'response_body']);
        });
    }
};