<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userRoles = auth()->payload()->get('roles');
        if (!in_array('owner', $userRoles)) {
            return response()->json([
                'message' => "You don't have Owner roles"
            ], 403);
        }
        return $next($request);
    }
}
