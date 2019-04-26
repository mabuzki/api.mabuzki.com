<?php

namespace App\Http\Controllers;

// use JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     * 要求附带email和password（数据来源users表）
     * 
     * @return void
     */
    public function __construct()
    {
        // 这里额外注意了：官方文档样例中只除外了『login』
        // 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
        // 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
        // 不过刷新一次作废
        $this->middleware('auth:api', ['except' => ['login']]);
        // 另外关于上面的中间件，官方文档写的是『auth:api』
        // 但是我推荐用 『jwt.auth』，效果是一样的，但是有更加丰富的报错信息返回
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        if (!request('email') || !request('password')) {
        	return response()->json([
                'success' => false,
                'info' => '表单不完整'
            ]);
        }

        $credentials = request(['email', 'password']);
        
        if (! $results = \DB::table('users')->where('email', request(['email']))->first() ) {
			return response()->json([
                'success' => false,
                'info' => '用户不存在'
            ]);
		}

        if ($token = auth('api')->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json([
            'success' => false,
            'info' => '邮箱/密码不正确！'
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        // JWTAuth::setToken(JWTAuth::getToken())->invalidate();

        return response()->json([
            'success' => true,
            'info' => '账号已登出'
        ]);
    }

    /**
     * Refresh a token.
     * 刷新token，如果开启黑名单，以前的token便会失效。
     * 值得注意的是用上面的getToken再获取一次Token并不算做刷新，两次获得的Token是并行的，即两个都可用。
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();

        $profile = \DB::table('users_profile')
            ->where('id', $user['id'])
            ->first();

        $profile = json_decode(json_encode($profile), true);

        return response()->json([
            'success' => true,
            'info' => '登陆成功',
            'userid' => $user['id'],
            'avatar' => $profile['avatar'],
            'username' => $user['username'],
            'needverify' => $user['email_verified_at'] ? false : true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 1
        ]);
    }
}