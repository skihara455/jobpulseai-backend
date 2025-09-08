<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool   { return $user->isAdmin(); }
    public function view(User $user, Role $r): bool { return $user->isAdmin(); }
    public function create(User $user): bool    { return $user->isAdmin(); }
    public function update(User $user, Role $r): bool { return $user->isAdmin(); }
    public function delete(User $user, Role $r): bool { return $user->isAdmin(); }
}

