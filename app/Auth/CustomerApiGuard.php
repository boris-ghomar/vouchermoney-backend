<?php

namespace App\Auth;

use App\Models\CustomerApiToken;
use App\Models\Permission;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class CustomerApiGuard extends TokenGuard
{
    public function __construct(UserProvider $provider, Request $request)
    {
        parent::__construct($provider, $request);
    }

    public function user(): CustomerApiToken|null
    {
        $token = $this->request->bearerToken();

        if (empty($token)) return null;

        $customerApiToken = CustomerApiToken::findByToken($token);

        if (empty($customerApiToken) || $customerApiToken->is_expired) return null;

        return $customerApiToken;
    }
}
