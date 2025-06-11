<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    public function admin(User $user)
    {
        return $user->is_admin; // Asume que hay un campo is_admin en la tabla users
    }
}