<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	// public function __construct()
	// {
	//     $this->middleware('auth');
	// }

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return view('user');
	}

	public function show($id)
	{

		if (User::find($id)) {
			$results = \DB::table('users_profile')->where('id', $id)->first();
			$results = json_decode(json_encode($results), True);
			return view('user', ['user' => true, 'results' => $results]);
		}

		return view('user', ['user' => false]);

		// return view('user', ['user' => User::findOrFail($id)]);
	}

}
