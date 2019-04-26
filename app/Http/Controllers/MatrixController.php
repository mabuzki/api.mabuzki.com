<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Redirect, Response;
use File;
use Auth;

use Carbon\Carbon;//个性化时间

function attUrl() {
	return config('app.CDN') ? '//'.config('app.CDN') : env('APP_URL');
}

function getImage( $imageid , $image , $caption )
{
	$medium = 'photo/'.$image['filepath'].$image['filename'].'__'.$image['salt'].'.'.$image['suffix'].'!middle';
	$large = 'photo/'.$image['filepath'].$image['filename'].'__'.$image['salt'].'.'.$image['suffix'].'!large';

	$template = '';
	$template .= '<figure>';
	// $template .= '<div class="media-image-box"><img data-sizes="auto" data-srcset="'.attUrl().'/'.$medium.' 660w, '.attUrl().'/'.$large.' 900w" data-image-id="'.$imageid.'" style="height:'.$image['height'].'px;" class="lazyload"></div>';

	if(!$image['animated']) {
		$ratio = $image['width'] / $image['height'];
		$height = $image['height'] * $ratio;
		$template .= '<div class="media-image-box"><img data-sizes="auto" data-src="'.attUrl().'/'.$medium.'" data-image-id="'.$imageid.'" class="lazyload"></div>';
	}
	
	if( $caption ) {
		$template .= '<figcaption>'.$caption.'</figcaption>';
	}
	
	$template .= '</figure>';
	return $template;
}

function getAvatar($userid, $avatars) {

}

class MatrixController extends Controller
{
	public function getArticle($article_id)
	{
		// $result = \DB::table('articles')->where('id', $article_id)->first();
		$result = \DB::table('users_profile')
			->leftjoin('articles','users_profile.id', '=', 'articles.authorid')
			->where('articles.id', $article_id)
			->get();

		if ( !$result ) {
			return Response::json(
				[
					'success' => false,
					'info' => '文章不存在'
				]
			);
		}

		$result = json_decode( json_encode($result) , True );
		$result = $result[0];

		if ( $result['status'] === -1 ) {
			return Response::json(
				[
					'success' => false,
					'info' => '文章已被管理员删除'
				]
			);
		}

		$attachment = explode(',',$result['attachment']);
		
		foreach ($attachment as $value) {
			$attachment_array[] = $value;
		}

		$images = \DB::table('attachments')->whereIn('id', $attachment_array)->get();
		$images = json_decode( json_encode($images) , True );

		$result['content'] = html_entity_decode( $result['content'] , ENT_QUOTES, 'UTF-8');

		$result['content'] = preg_replace_callback( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i',
									function ($matches) use ($images) {
										$r = array_filter($images, function($t) use ( $matches ) {
											$t['id'] = $matches[1];//图片id
											$t['caption'] = $matches[2];
											return $t;
										});

										$r = array_column( $r, null, 'id');
										return getImage( $matches[1] , $r[ $matches[1] ] , $matches[2] );
									}, $result['content'] );

		$result['date_post_title'] = Carbon::createFromTimestamp($result['date_post'])->format('Y年m月d日 H时i分s秒');

		$result['date_post'] = Carbon::createFromTimestamp($result['date_post'])->diffForHumans();
		
		return Response::json(
				[
					'success' => true,
					'result' => $result
				]
			);
	}

	public function getArticles()
	{
		$articles = \DB::table('articles')
			->where('status', '=', 0)
			->orderBy('date_post', 'desc')
			->skip(0)
			->take(5)
			->get();
		
		$articles = json_decode(json_encode($articles), true);

		$images = array();
		$attachment_array = array();
		$author_array = array();

		foreach ($articles as $key => $value) {
			$attachment_array[] = $value['attachment'];
			$author_array[] = $value['authorid'];
			$articles[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
			$articles[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
		}

		$author_array = array_filter($author_array);
		$avatars = \DB::table('users_profile')->whereIn('id', $author_array)->get();
		$avatars = json_decode( json_encode($avatars) , True );
		//从二维数组中选择出key和键值
		$avatars = array_column($avatars, 'avatar', 'id');

		foreach ($articles as $key => $value) {
			
			$articles[$key]['avatar'] = $avatars[$value['authorid']];
		}

		if(isset($attachment_array)) {
			$attachment_array = array_filter($attachment_array);
			$images = \DB::table('attachments')->whereIn('id', $attachment_array)->get();
			$images = json_decode( json_encode($images) , True );
			foreach ($articles as $key => $value) {
				$articles[$key]['content'] = preg_replace_callback( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i',
										function ($matches) use ($images) {
											$r = array_filter($images, function($t) use ( $matches ) {
												$t['id'] = $matches[1];//图片id
												$t['caption'] = $matches[2];
												return $t;
											});
	
											$r = array_column( $r, null, 'id');
											return getImage( $matches[1] , $r[ $matches[1] ] , $matches[2] );
										}, $articles[$key]['content'] );
			}
		}
		

		return Response::json(
			[
				'success' => true,
				'articles' => $articles
			]
		);

		if ( !$result ) {
			abort(404);
		}
		$result = json_decode( json_encode($result) , True );

		if ( $result['status'] === -1 ) {
			return Response::json(
				[
					'success' => false,
					'info' => '文章已被管理员删除'
				]
			);
		}

		$attachment = explode(',',$result['attachment']);
		
		foreach ($attachment as $value) {
			$attachment_array[] = $value;
		}

		$images = \DB::table('attachments')->whereIn('id', $attachment_array)->get();
		$images = json_decode( json_encode($images) , True );

		$result['content'] = html_entity_decode( $result['content'] , ENT_QUOTES, 'UTF-8');

		$result['content'] = preg_replace_callback( '/\:PiCiD#([0-9]*)\:/i',
									function ($matches) use ($images){
										$r = array_filter($images, function($t) use ( $matches ) {
											return $t['id'] == $matches[1];
										});

										$r = array_column( $r, null, 'id');
										return getImage( $matches[1] , $r[ $matches[1] ] );
									}, $result['content'] );
									
		$result['date_post_title'] = Carbon::createFromTimestamp($result['date_post'])->format('Y年m月d日 H时i分s秒');

		$result['date_post'] = Carbon::createFromTimestamp($result['date_post'])->diffForHumans();
		
		return Response::json(
				[
					'success' => true,
					'result' => $result
				]
			);
	}

	public function getComment($article_id)
	{
		return Response::json(
			[
				'success' => true,
				'info' => 'test'
			]
		);
	}

	public function getProfile()
	{
		$result = \DB::table('users_profile')->where('id', Auth::user()->id)->first();
		if ( !$result ) {
			abort(404);
		}
		$result = json_decode( json_encode($result) , True );

		if ( $result['status'] === -1 ) {
			return Response::json(
				[
					'success' => false,
					'info' => '用户已被禁止登录'
				]
			);
		}

		return Response::json(
			[
				'success' => true,
				'result' => $result
			]
		);
	}

	public function getAccount()
	{
		$result['telphone'] = Auth::user()->phone_number;
		$result['email'] = Auth::user()->email;

		return Response::json(
			[
				'success' => true,
				'result' => $result
			]
		);
	}

	public function getUserProfile($userId) // 获取单个用户首页信息
	{
		$result = \DB::table('users_profile')
			->where('id', $userId)
			->first();

		if ( !$result ) {
			return Response::json(
				[
					'success' => false,
					'info' => '用户不存在'
				]
			);
		}

		$result = json_decode(json_encode($result), true);

		if ( $result['status'] === -1 ) {
			return Response::json(
				[
					'success' => false,
					'info' => '用户已经封禁'
				]
			);	
		}

		$articles = \DB::table('articles')
			->where('authorid', $userId)
			->orderBy('date_post', 'desc')
			->skip(0)
			->take(5)
			->get();

		$articles = json_decode(json_encode($articles), true);

		$images = array();
		$attachment_array = array();

		foreach ($articles as $key => $value) {
			$attachment_array[] = $value['attachment'];
			$articles[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
			$articles[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
		}

		if($attachment_array) { // 附件处理
			$attachment_array = array_filter($attachment_array);
			$images = \DB::table('attachments')->whereIn('id', $attachment_array)->get();
			$images = json_decode( json_encode($images) , True );

			foreach ($articles as $key => $value) {
				$articles[$key]['content'] = preg_replace_callback( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i',
										function ($matches) use ($images) {
											$r = array_filter($images, function($t) use ( $matches ) {
												$t['id'] = $matches[1];//图片id
												$t['caption'] = $matches[2];
												return $t;
											});

											$r = array_column( $r, null, 'id');
											return getImage( $matches[1] , $r[ $matches[1] ] , $matches[2] );
										}, $articles[$key]['content'] );
			}
		}

		
		return Response::json(
			[
				'success' => true,
				'userinfo' => $result,
				'articles' => $articles
			]
		);
	}

	public function getUserArticle($userId, $page)
	{
		$per = 5;
		$skip = ( $page - 1 ) * 5;

		$result = \DB::table('users_profile')->where('id', $userId)->first();

		$result = \DB::table('articles')
			->where('authorid', $userId)
			->orderBy('date_post', 'desc')
			->skip($skip)
			->take($per)
			->get();
		
		if ($result->isEmpty()) {
			return Response::json(
				[
					'success' => true,
					'result' =>  null
				]
			);
		}

		return Response::json(
			[
				'success' => true,
				'result' =>  $result
			]
		);
	}
}
