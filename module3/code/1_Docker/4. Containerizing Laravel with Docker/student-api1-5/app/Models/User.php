<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // HasApiTokens adds createToken(), tokens(), and currentAccessToken()
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    // Never include these in JSON responses
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // hash automatically when saved
        ];
    }
}
