<?php

namespace App\Models;

use Agentoom\Core\Models\User as BaseUser;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return \Illuminate\Support\Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => \Illuminate\Support\Str::of($name)->substr(0, 1))
            ->implode('');
    }
}
