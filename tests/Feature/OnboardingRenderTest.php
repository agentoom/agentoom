<?php

use Agentoom\Core\Livewire\Admin\OnboardingWizard;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('onboarding screen renders with listbox and flux components', function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    $user = User::factory()->create();
    $user->assignRole('superadmin');

    $response = $this->actingAs($user)->get(route('admin.onboarding'));

    $response->assertOk();
});

test('onboarding wizard livewire component mounts and can call syncModels without reflection exceptions', function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    $user = User::factory()->create();
    $user->assignRole('superadmin');

    Livewire::actingAs($user)
        ->test(OnboardingWizard::class)
        ->assertSet('step', 1)
        ->set('providerName', 'openrouter')
        ->set('providerApiKey', 'test-fake-key')
        ->call('syncModels');
});
