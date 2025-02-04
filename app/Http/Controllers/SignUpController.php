<?php

namespace App\Http\Controllers;

use App\Helpers\Interfaces\ResponseCodesInterface;
use Illuminate\Http\Request;
use Response;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;

use JWTAuth;

require(__DIR__ . './../../../vendor/autoload.php');
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use LasseRafn\StringScript;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use \Colors\RandomColor;

function get_avatar($id) {
	$id = abs(intval($id));
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
    $dir3 = substr($id, 5, 2);
    $rand = str_random(random_int(20,30));
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($id, -2)."_".$rand.".png";
}

class SignUpController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected $fillable = ['username'];

    /**
     * 自定义用户名, 即不使用默认的电子邮件认证
     *
     * @return string
     */
    public function username() {
        return 'email';
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // return Validator::make($data, [
        //     'username' => 'required|string|max:20|unique:users',
        //     'password' => 'required|string|min:6|max:16',
        //     // 'phone_number' => 'required|string|min:6|max:16|unique:users',
        //     'telphone' => 'required|string|min:6|max:16',
        //     'secchk' => 'required|string|max:6',
        // ]);

        $rules = [
            'email'         => 'required|string|email|max:50|unique:users',
            'username'      => 'required|string|min:4|max:20|unique:users',
            // 'telphone'      => 'required|string|min:6|max:16|unique:users',
            'password'      => 'required|string|min:6|max:16',
            // 'secchk'        => 'required|string|max:6',
        ];
        
        $message = [
            'username.required'      => '必须输入用户名',
            'username.alpha_num'     => '用户名只能为字母或数字',
            'username.min'           => '用户名长度不能少于 4 字符',
            'username.max'           => '用户名长度不能超过 20 字符',
            'username.unique'        => '用户名已经存在，请更换注册用户名',
            'email.required'         => '必须输入电子邮箱地址',
            'email.email'            => '不是正确的电子邮箱地址',
            'email.max'              => '电子邮箱地址长度不能超过 50 字符',
            'email.unique'           => '电子邮箱地址已经存在，请更换邮箱地址',
            'telphone.required'      => '必须输入手机号码',
            'telphone.unique'        => '手机号码已经存在，请更换新手机号',
            'password.required'      => '必须输入密码',
            'password.min'           => '密码长度最少 6 字符',
            'password.max'           => '密码长度最长 16 字符',
            'secchk.required'        => '必须输入验证码',
            'I_agree.required'       => '必须同意服务条款',
        ];
        return Validator::make($data, $rules, $message);
    }

    /**
     * 实现用户注册
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function register(Request $request){
        $errors = $this->validator($request->all())->errors();
        if (!empty($errors->all())) {
            return Response::json(
				[
					'success' => false,
                    'extra' => $errors->toArray()
				]
			);
            // return $this->sendFailedResponse($errors->toArray(), self::HTTP_CODE_BAD_REQUEST);
        } else {
            event(new Registered($user = $this->create($request->all())));
            $token = JWTAuth::fromUser($user);

            $profile = \DB::table('users')
            ->where('id', $user['id'])
            ->first();

            $profile = json_decode(json_encode($profile), true);

            return Response::json(
				[
					'success' => true,
                    'info' => '注册成功, 正在引导登入',
                    'userid' => $user['id'],
                    'username' => $user['username'],
                    'avatar' => $profile['avatar'],
                    'needverify' => true,
                    'token' => $token
				]
			);
        }
        
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */

    
    protected function create(array $data)
    {
        $user = User::create([
            'username' => $data['username'],
            'telphone' => $data['telphone'],
            'password' => bcrypt($data['password']),
            'email' => $data['email'],
            'register_time' => time(),
            'register_ip' => ip2long(request()->ip()),
        ]);

        if($user->wasRecentlyCreated){
            $avatar = get_avatar($user->id);
            $resource = 'avatar/'.$avatar;
            $background = RandomColor::one(array('format'=>'hex','hue'=>array('blue', 'purple','red') ));

            $IA = new InitialAvatar();
            $new_avatar = $IA
                ->autoFont()
                ->name($user->username)
                ->background($background)
                ->size(180)
                ->fontSize(0.35)
                ->generate()
                ->stream('png', 100);

            $Image = new ImageManager();
            $path = public_path().'/uploads/avatars/'.$avatar;
            $Image->make($new_avatar)->resize(180, 180)->save($path, 100);
            $result = Storage::put( $resource, file_get_contents( $path ) );
            \DB::table('users')
                ->where('id', $user['id'])
                ->update(
                    ['avatar' => $avatar]
                );
            \DB::table('users_profile')
                ->insert(
                    ['id' => $user->id]
                );
            if($result) {
                @unlink( $path );
            }
        }

        return $user;
    }
}
