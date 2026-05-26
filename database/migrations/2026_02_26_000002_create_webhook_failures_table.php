<?php
// database/migrations/2026_02_26_000002_create_webhook_failures_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('webhook_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_call_id')->constrained('webhook_calls')->onDelete('cascade');
            $table->text('error_message');
            $table->json('error_context')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_failures');
    }
};