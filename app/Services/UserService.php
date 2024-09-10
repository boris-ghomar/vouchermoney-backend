<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function create(): User
    {
        $user = new User();

        return $user;
    }
}
