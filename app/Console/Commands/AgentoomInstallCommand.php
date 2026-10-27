<?php

namespace App\Console\Commands;

use Agentoom\Core\Console\Commands\AgentoomInstall as BaseInstallCommand;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentoomInstallCommand extends BaseInstallCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agentoom:install {--email= : Admin email} {--password= : Admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Agentoom system securely (roles, superadmin, languages, tools)';

    /**
     * Create or verify superadmin with secure random generation or environment variables.
     */
    protected function createSuperAdmin(): void
    {
        $this->info('Configuring superadmin account...');
        $userModel = config('auth.providers.users.model');

        // Check if any user already has the superadmin role
        $existingSuperAdmin = $userModel::whereHas('roles', function ($query) {
            $query->where('name', 'superadmin');
        })->first();

        if ($existingSuperAdmin) {
            $this->info("Superadmin account already exists ({$existingSuperAdmin->email}). Existing credentials preserved.");
            return;
        }

        // Determine email: CLI option -> env('ADMIN_EMAIL') -> default
        $email = $this->option('email') ?: (env('ADMIN_EMAIL') ?: 'admin@agentoom.local');

        // Determine password: CLI option -> env('ADMIN_PASSWORD') -> secure random password
        $providedPassword = $this->option('password') ?: env('ADMIN_PASSWORD');
        $isGenerated = false;

        if (! empty($providedPassword)) {
            $password = $providedPassword;
        } else {
            $password = Str::password(20);
            $isGenerated = true;
        }

        $user = $userModel::query()->where('email', $email)->first();

        if (! $user) {
            $user = $userModel::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->line("Superadmin user created: {$email}");
        } else {
            $this->line("User already exists: {$email}");
        }

        if (! $user->hasRole('superadmin')) {
            $user->assignRole('superadmin');
            $this->line("Assigned 'superadmin' role to {$email}");
        }

        if ($isGenerated) {
            $this->newLine();
            $this->warn('================================================================================');
            $this->warn('🔐 AGENTOOM SUPERADMIN CREDENTIALS GENERATED (INITIAL SETUP)');
            $this->warn('================================================================================');
            $this->line(" Email:    <info>{$email}</info>");
            $this->line(" Password: <info>{$password}</info>");
            $this->warn('--------------------------------------------------------------------------------');
            $this->warn('⚠️  Save these credentials immediately! This banner is displayed only once.');
            $this->warn('================================================================================');
            $this->newLine();
        } else {
            $this->info("Superadmin configured with provided credentials ({$email}).");
        }
    }
}
