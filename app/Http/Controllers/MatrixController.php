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

function getImage( $imageid , $image )
{
	$medium = 'photo/'.$image['filepath'].$image['filename'].'__'.$image['salt'].'.'.$image['suffix'].'!middle';
	$large = 'photo/'.$image['filepath'].$image['filename'].'__'.$image['salt'].'.'.$image['suffix'].'!large';

	$template = '';
	$template .= '<figure>';
	// $template .= '<div class="media-image-box"><img data-sizes="auto" data-srcset="'.attUrl().'/'.$medium.' 660w, '.attUrl().'/'.$large.' 900w" data-image-id="'.$imageid.'" style="height:'.$image['height'].'px;" class="lazyload"></div>';

	if(!$image['animated']) {
		$ratio = $image['width'] / 720;
		$height = $image['height'] / $ratio;
		$template .= '<div class="media-image-box"><img data-sizes="auto" data-src="'.attUrl().'/'.$medium.'" data-image-id="'.$imageid.'" style="height:'.$height.'px;" class="lazyload"></div>';
	}
	
	if( $image['caption'] ) {
		$template .= '<figcaption>'.$image['caption'].'</figcaption>';
	}
	
	$template .= '</figure>';
	return $template;
}

class MatrixController extends Controller
{
	public function getArticle($article_id)
	{
		$result = \DB::table('articles')->where('id', $article_id)->first();
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

		$images = \DB::table('photos')->whereIn('id', $attachment_array)->get();
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

		// Carbon::now()->settings([
		// 	'locale' => 'zh',
		// 	'timezone' => 'Asia/Shanghai'
		// ]);

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

		foreach ($articles as $key => $value) {
			$articles[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
			$articles[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
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

		$images = \DB::table('photos')->whereIn('id', $attachment_array)->get();
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

		// Carbon::now()->settings([
		// 	'locale' => 'zh',
		// 	'timezone' => 'Asia/Shanghai'
		// ]);

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

	public function getUserProfile($userId)
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

		foreach ($articles as $key => $value) {
			$articles[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
			$articles[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
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
