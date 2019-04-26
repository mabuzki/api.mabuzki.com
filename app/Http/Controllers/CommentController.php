<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Redirect, Response;
use Illuminate\Support\Facades\Input;

class CommentController extends Controller
{
    //
    public function post() {
        header("Content-Type: text/html;charset=utf-8");

        $user = auth('api')->user();
        
        if ( !$user['email_verified_at'] ) {
			return Response::json(
				[
					'success' => false,
					'info' => '未认证账号不能发表意见'
				]
			);
        }
        
        if( !Input::get('comment') ) {
			return Response::json(
				[
					'success' => false,
					'info' => '至少要写点东西'
				]
			);
        }
        
    }
}
