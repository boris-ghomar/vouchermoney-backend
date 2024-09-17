<?php

namespace App\Services;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function createUser(UserRequest $request)
    {

        $validated = $request->validated();


        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
