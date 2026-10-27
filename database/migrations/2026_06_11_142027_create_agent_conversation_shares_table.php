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
        if (! Schema::hasColumn('agent_conversations', 'is_private')) {
            Schema::table('agent_conversations', function (Blueprint $table) {
                $table->boolean('is_private')->default(true)->after('allow_memory_analysis');
            });
        }

        if (! Schema::hasTable('agent_conversation_shares')) {
            Schema::create('agent_conversation_shares', function (Blueprint $table) {
                $table->id();
                $table->string('conversation_id', 36);
                $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('shared_with_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->foreign('conversation_id')->references('id')->on('agent_conversations')->cascadeOnDelete();
                $table->unique(
                    ['conversation_id', 'shared_with_user_id'],
                    'agent_conversation_shares_conversation_user_unique'
                );
                $table->index(['shared_with_user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_conversation_shares');

        if (Schema::hasColumn('agent_conversations', 'is_private')) {
            Schema::table('agent_conversations', function (Blueprint $table) {
                $table->dropColumn('is_private');
            });
        }
    }
};
