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
		
		$comment = Input::get('comment');
		$comment_tmp = str_replace(array(" ","　","\t","\n","\r"), "", $comment);
		$comment_tmp = str_replace(array("&nbsp;",chr(194).chr(160)), "", $comment_tmp);
		$comment_tmp = str_replace(array("<p>","</p>","<span>","</span>","<div>","</div>"), "", $comment_tmp);

		

		if( strlen( $comment_tmp ) < 10 ) {
			return Response::json(
				[
					'success' => false,
					'info' => '内容太短'
				]
			);
		}

		$content_tmp = null;
		$article_id = (int) Input::get('articleid');

		$comment = strip_tags($comment, '<p>');

		$comment_id = \DB::table('comments')->insertGetId(
			[
				'articleid' => $article_id,
				'authorid' => $user['id'],
				'comment' => $comment,
				'favtimes' => 0,
				'replynum' => 0,
				'date_post' => time()
			]
		);

		if ( $comment_id ) {

			\DB::table('articles')
				->where('id', $article_id)
				->increment('replynum', 1);

			return Response::json(
				[
					'success' => 1,
					'comment_id' => $comment_id,
					'info' => '发表评论成功'
				]
			);
			
		}
    }
}
