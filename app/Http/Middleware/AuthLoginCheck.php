<?php

namespace App\Http\Middleware;

use Closure;

use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthLoginCheck extends BaseMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'info' => 'Token失效, 请重新登录'
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'info' => 'Token过期, 请重新登录'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'info' => 'Token不存在, 账号信息已失效, 请重新登录'
                ]);
            }
        }
        return $next($request);
    }
}
