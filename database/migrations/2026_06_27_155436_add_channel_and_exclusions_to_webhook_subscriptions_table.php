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
        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->boolean('exclude_agents')->default(false)->after('pipeline_id');
            $table->boolean('exclude_pipelines')->default(false)->after('exclude_agents');
            $table->string('channel')->nullable()->after('exclude_pipelines');
            $table->json('channel_config')->nullable()->after('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_subscriptions', function (Blueprint $table) {
            $table->dropColumn('channel_config');
            $table->dropColumn('channel');
            $table->dropColumn('exclude_pipelines');
            $table->dropColumn('exclude_agents');
        });
    }
};
