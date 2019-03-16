<?php

namespace App\Http\Middleware;

use Closure;

use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthLoginCheck extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'message' => 'token失效',
                    'info' => '请重新登录'
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException){
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'message' => 'token失效',//已签发新token
                    'info' => '请重新登录'
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                try {
                    $token = JWTAuth::getToken();
                    dd($e);
                    $newToken = JWTAuth::refresh($token);// 刷新用户的 token
                    dd($newToken);
                    return response([
                        'status' => 'success'
                    ])
                    ->header('Authorization', $newToken);
                //    // 使用一次性登录以保证此次请求的成功
                //     Auth::guard('api')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
                } catch (JWTException $exception) {
                    // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                    // throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
                }
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'info' => 'Token过期, 请重新登录'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'message' => 'Token不存在, 账号信息已失效, ',
                    'info' => '请重新登录'
                ]);
            }
            
        }
        return $next($request);
    }
}
