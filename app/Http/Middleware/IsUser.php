<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsUser
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
        if (!in_array('user', $userRoles)) {
            return response()->json([
                'message' => "You don't have User roles"
            ], 403);
        }
        return $next($request);
    }
}
