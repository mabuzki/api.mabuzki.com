<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Controllers\Controller;
use Auth;
use Redirect, Response;
use Illuminate\Support\Facades\Input;

require( __DIR__ . './../../../vendor/autoload.php' );
use ConsoleTVs\Profanity\Facades\Profanity;//过滤黄暴字符

class SettingController extends Controller
{
    public function profile()
	{
		// if(!Auth::user()) return view('auth/login', ['needlogin' => true, 'refresh' => true]);

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

	public function updateSettingProfile(Request $request)
	{
		$signature = $request::input('signature');

		$user = auth('api')->user();

		if( !Profanity::blocker( $signature )->clean() ) {
			return Response::json(
				[
					'success' => false,
					'info' => '请确保内容合法'
				]
			);
		}

		$result = \DB::table('users_profile')
			->where('id', $user['id'])
			->update(['signature' => $signature]);
		
		if( $result ) {
			return Response::json(
				[
					'success' => true,
					'info' => '修改成功'
				]
			);
		}

		return Response::json(
			[
				'success' => true,
				'info' => '内容无修改'
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
