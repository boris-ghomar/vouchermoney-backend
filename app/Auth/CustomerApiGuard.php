<?php

namespace App\Auth;

use App\Models\CustomerApiToken;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class CustomerApiGuard extends TokenGuard
{
    public function __construct(UserProvider $provider, Request $request)
    {
        parent::__construct($provider, $request);
    }

    public function user()
    {
        $token = $this->request->bearerToken();
        if (!$token) return null;

        $hashedToken = hash('sha256', $token);
        $customerApiToken = CustomerApiToken::query()->where('token', $hashedToken)->first();

        if (!$customerApiToken || $customerApiToken->isExpired()) return null;
        return $customerApiToken;
    }
}
