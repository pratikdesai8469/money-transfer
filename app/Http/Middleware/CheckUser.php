<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //get request from header
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json(['status' => 0, 'message' => 'Please enter token'], 401);
        }
        $user = User::where('token', $token)->first();
        if (!$user) {
            return response()->json(['status' => 0, 'message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
