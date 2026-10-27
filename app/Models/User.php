<?php

namespace App\Models;

use Agentoom\Core\Models\User as BaseUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
