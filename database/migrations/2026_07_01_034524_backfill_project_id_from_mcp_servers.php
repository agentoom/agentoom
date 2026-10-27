<?php

use Agentoom\Core\Models\Agent;
use Agentoom\Core\Models\McpServer;
use Agentoom\Core\Models\Pipeline;
use Agentoom\Core\Models\Project;
use Agentoom\Core\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Backfill project_id on MCP servers and their dependent entities.
     */
    public function up(): void
    {
        $systemUser = User::firstOrCreate(
            ['email' => 'system@agentoom.internal'],
            [
                'name' => 'System',
                'password' => bcrypt(Str::random(32)),
                'is_system' => true,
            ]
        );

        // 1. Create projects for MCP servers that don't have one yet.
        McpServer::whereNull('project_id')->each(function (McpServer $server) use ($systemUser) {
            $project = Project::create([
                'name' => "MCP: {$server->name}",
                'is_active' => true,
                'user_id' => $systemUser->id,
            ]);
            $server->update(['project_id' => $project->id]);
        });

        // 2. Backfill project_id on pipelines, schedules, and agents from their MCP server.
        Pipeline::whereNotNull('mcp_server_id')
            ->whereNull('project_id')
            ->each(function (Pipeline $pipeline) {
                $server = McpServer::find($pipeline->mcp_server_id);
                if ($server && $server->project_id) {
                    $pipeline->update(['project_id' => $server->project_id]);
                }
            });

        Schedule::whereNotNull('mcp_server_id')
            ->whereNull('project_id')
            ->each(function (Schedule $schedule) {
                $server = McpServer::find($schedule->mcp_server_id);
                if ($server && $server->project_id) {
                    $schedule->update(['project_id' => $server->project_id]);
                }
            });

        Agent::whereNotNull('mcp_server_id')
            ->whereNull('project_id')
            ->each(function (Agent $agent) {
                $server = McpServer::find($agent->mcp_server_id);
                if ($server && $server->project_id) {
                    $agent->update(['project_id' => $server->project_id]);
                }
            });
    }

    /**
     * No structural changes to roll back.
     */
    public function down(): void
    {
        // This migration is a data backfill; there is no structural reversal.
    }
};
