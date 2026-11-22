<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'admins';

    protected $fillable = ['nama', 'email', 'password', 'role'];

    protected $hidden = ['password'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }
}
