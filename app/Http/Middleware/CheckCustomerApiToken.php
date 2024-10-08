<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckCustomerApiToken
{
    public function handle($request,Closure $next)
    {
        $user = Auth::guard('customer-api')->user();

        if (!$user) return response()->json([
            "status" => "failed",
            'message' => 'Unauthorized'
        ], 401);
        $request->attributes->set('authenticatedUser', $user);

        return $next($request);
    }
}
