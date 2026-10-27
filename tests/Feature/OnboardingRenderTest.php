<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('onboarding screen renders with listbox and flux components', function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    $user = User::factory()->create();
    $user->assignRole('superadmin');

    $response = $this->actingAs($user)->get(route('admin.onboarding'));

    $response->assertOk();
});
