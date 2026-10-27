<?php

namespace App\Console\Commands;

use Agentoom\Core\Console\Commands\AgentoomInstall as BaseInstallCommand;
use Agentoom\Core\Models\Configuration;
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
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting installation of the Agentoom system...');

        // 1. Create roles
        $this->createRoles();

        // 2. Create superadmin user and authenticate
        $this->createSuperAdmin();

        // 3. Add languages
        $this->addLanguages();

        // 4. Create system resources
        $this->createSystemResources();

        // 5. Ensure onboarding is shown for fresh installations
        Configuration::setValue('onboarding_complete', '0');
        $this->line('Onboarding wizard enabled.');

        $this->info('Installation completed successfully!');

        return 0;
    }

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
            auth()->login($existingSuperAdmin);

            return;
        }

        // Determine email: CLI option -> environment -> default
        $emailOption = $this->option('email');
        $envEmail = getenv('ADMIN_EMAIL');
        $email = is_string($emailOption) && $emailOption !== ''
            ? $emailOption
            : (is_string($envEmail) && $envEmail !== '' ? $envEmail : 'admin@agentoom.local');

        // Determine password: CLI option -> environment -> secure random password
        $passwordOption = $this->option('password');
        $envPassword = getenv('ADMIN_PASSWORD');
        $providedPassword = is_string($passwordOption) && $passwordOption !== ''
            ? $passwordOption
            : (is_string($envPassword) && $envPassword !== '' ? $envPassword : null);

        $isGenerated = false;

        if ($providedPassword !== null) {
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
                'password' => Hash::make((string) $password),
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

        // Authenticate superadmin in CLI context so created system resources belong to this user
        auth()->login($user);

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
