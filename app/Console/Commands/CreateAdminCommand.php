<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agentoom:create-admin 
                            {--name= : The name of the administrator}
                            {--email= : The email address for the administrator}
                            {--password= : The administrator password}
                            {--role=superadmin : The role to assign (superadmin or admin)}
                            {--force : Force overwrite password if user already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or reset an Agentoom administrator account securely';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name');
        if (! $name && $this->input->isInteractive()) {
            $name = $this->ask('Administrator name', 'Administrator');
        }
        $name = is_string($name) && $name !== '' ? $name : 'Administrator';

        $email = $this->option('email');
        if (! $email && $this->input->isInteractive()) {
            $email = $this->ask('Administrator email address');
        }
        if (! $email) {
            $envEmail = getenv('ADMIN_EMAIL');
            $email = is_string($envEmail) && $envEmail !== '' ? $envEmail : 'admin@agentoom.local';
        }

        $password = $this->option('password');
        $isGenerated = false;
        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Administrator password (leave blank to auto-generate)');
        }

        if (empty($password)) {
            $password = Str::password(20);
            $isGenerated = true;
        }

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->first();

        if ($user) {
            if (! $this->option('force') && ! ($this->input->isInteractive() && $this->confirm("User [{$email}] already exists. Reset password?", false))) {
                $this->warn("User [{$email}] already exists. Password was not changed. Use --force to update.");

                return 0;
            }
            $user->name = $name;
            $user->password = Hash::make((string) $password);
            $user->save();
            $this->info("User [{$email}] updated successfully.");
        } else {
            $user = $userModel::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make((string) $password),
                'email_verified_at' => now(),
            ]);
            $this->info("User [{$email}] created successfully.");
        }

        $role = $this->option('role') ?: 'superadmin';
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
            $this->info("Assigned [{$role}] role to [{$email}].");
        }

        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', (string) $email],
                ['Role', $role],
                ['Password', $isGenerated ? (string) $password : '(hidden as entered)'],
            ]
        );

        if ($isGenerated) {
            $this->warn('⚠️  Please store this generated password in a safe place!');
        }

        return 0;
    }
}
