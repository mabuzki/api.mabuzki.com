<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Controllers\Controller;
use Auth;
use Redirect, Response;
use Illuminate\Support\Facades\Input;

class SettingController extends Controller
{
    public function profile()
	{
		if(!Auth::user()) return view('auth/login', ['needlogin' => true, 'refresh' => true]);

		// $results = \DB::table('users_profile')->where('id', Auth::user()->id)->first();
		// $results = json_decode(json_encode($results), True);

		// if (!empty($results['gender'])) {
		// 	$results['genderhtml'] = '';
		// }

		// switch ($results['gender']) {
		// 	case 1:
		// 		$results['gendername'] = '男';
		// 		break;

		// 	case 2:
		// 		$results['gendername'] = '女';
		// 		break;

		// 	case 3:
		// 		$results['gendername'] = '保密';
		// 		break;

		// 	case 4:
		// 		$results['gendername'] = '其他';
		// 		break;
			
		// 	default:
		// 		$results['gendername'] = '未设置';
		// 		break;
		// }

		// if(!empty($results['birthday'])) {
		// 	$results['birth'] = $results['birthyear'].'-'.$results['birthmonth'].'-'.$results['birthday'];
		// } else {
		// 	$results['birth'] = '';
		// }
		
		// return view('user', ['user' => User::findOrFail($id)]);

		// return view('setting/profile', ['results' => $results]);
		return view('setting/profile');
	}

	public function updateSettingProfile()
	{
		if(!Auth::user()) {
			return Response::json(
				[
					'success' => false,
					'info' => '你需要登录才能更新资料'
				]
			);
		}

		$signature = Input::get('signature');

		$result = \DB::table('users_profile')
			->where('username', Auth::user()->username)
			->update(['bio' => $signature]);

		return Response::json(
			[
				'success' => true,
				'info' => '修改成功'
			]
		);
	}

	public function exterior()
	{
		if(!Auth::user()) return view('auth/login', ['needlogin' => true, 'refresh' => true]);

		$results = \DB::table('users_profile')->where('id', Auth::user()->id)->first();
		$results = json_decode(json_encode($results), True);

		return view('setting/exterior', ['results' => $results]);
	}

	public function account()
	{
		if(!Auth::user()) return view('auth/login', ['needlogin' => true, 'refresh' => true]);

		$results = \DB::table('users_profile')->where('id', Auth::user()->id)->first();
		$results = json_decode(json_encode($results), True);

		return view('setting/account', ['results' => $results]);
	}
}
