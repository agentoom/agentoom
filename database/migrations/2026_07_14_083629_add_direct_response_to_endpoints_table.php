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
        Schema::table('endpoints', function (Blueprint $table) {
            $table->boolean('direct_response_enabled')->default(false)->after('force_json');
            $table->text('direct_response_body')->nullable()->after('direct_response_enabled');
            $table->integer('direct_response_code')->default(200)->after('direct_response_body');
            $table->string('direct_response_content_type')->default('application/json')->after('direct_response_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('endpoints', function (Blueprint $table) {
            $table->dropColumn([
                'direct_response_enabled',
                'direct_response_body',
                'direct_response_code',
                'direct_response_content_type',
            ]);
        });
    }
};
