<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

// use Exception;
// use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class AuthLoginCheck extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            // $response = $next($request);
            // $response->header('Access-Control-Expose-Headers', 'Authorization');
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'needlogin' => true,
                    'message' => 'token失效',
                    'info' => '请重新登录'
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                // dd(JWTAuth::invalidate($token));//Token has expired
                // dd(JWTAuth::getPayload($token));//Token has expired
                // dd(JWTAuth::invalidate(JWTAuth::parseToken()));//Token has expired
                $auth = JWTAuth::parseToken();
                $token = $auth->setRequest($request)->getToken();
                try {
                    $newToken = JWTAuth::refresh($token);// 刷新用户的 token
                    //$response->headers->set('Authorization', 'Bearer '.$newToken);
                    return response([
                        'status' => 'success'
                    ])->header('Access-Control-Expose-Headers', 'Authorization')->header('Authorization', $newToken);
                    // 使用一次性登录以保证此次请求的成功
                    //Auth::guard('api')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
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
