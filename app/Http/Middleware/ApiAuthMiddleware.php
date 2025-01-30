<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');



        $authenticate = true; // auth is true
        // if token doesnt exist, auth is false
        if (!$token) {
            $authenticate = false;
        }
        // get user where token, then get first 
        $user = User::where('token', $token)->first();
        if (!$user) {
            $authenticate = false;
        } else {
            Auth::login($user);
        }

        // Auth::user();
        // if user authenticated go to next controller
        if ($authenticate) {
            return $next($request);
        } else {
            return response()->json([
                "errors" =>  [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ])->setStatusCode(401);
        }
    }
}
