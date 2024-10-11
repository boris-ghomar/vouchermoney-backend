<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCustomerApiToken
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request,Closure $next): mixed
    {
        $user = Auth::guard('token')->user();

        if (empty($user)) return response()->json([
            "status" => "failed",
            'message' => 'Unauthorized'
        ], 401);

        $request->setUserResolver(fn() => $user);

        auth()->setUser($user);

        return $next($request);
    }
}
