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

function getImage( $imageid, $image, $caption, $count, $square = false)
{
	$path = 'photo/'.$image['filepath'].$image['filename'].'__'.$image['salt'].'.'.$image['suffix'];
	$url = $square ? $path.'!square_middle' : $path.'!middle';
	$large = 'photo/'.$image['filepath'].$image['filename'].'__'.$image['salt'].'.'.$image['suffix'].'!large';
	$numbers = $count > 1 ? '<span class="tag is-white">'.$count.'</span>' : '';

	$template = '';
	$template .= '<figure>';
	// $template .= '<div class="media-image-box" style="height"><img data-sizes="auto" data-srcset="'.attUrl().'/'.$medium.' 660w, '.attUrl().'/'.$large.' 900w" data-image-id="'.$imageid.'" style="height:'.$image['height'].'px;" class="lazyload"></div>';

	if(!$image['animated']) {
		$ratio = $image['width'] / $image['height'];
		$height = $image['height'] * $ratio;
		$template .= '<div class="media-image-box"><img data-sizes="auto" data-src="'.attUrl().'/'.$url.'" data-image-id="'.$imageid.'" class="lazyload">'.$numbers.'</div>';
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
		$result = \DB::table('articles')
			->join('users','articles.authorid', '=', 'users.id')
			// ->join('users_profile', 'articles.authorid', '=', 'users_profile.id')
			->where('articles.id', $article_id)
			->select('articles.id', 'users.username as author', 'users.avatar', 'articles.authorid', 'articles.type', 'articles.subject', 'articles.location', 'articles.cover', 'articles.content', 'articles.attachment', 'articles.tags', 'articles.readtimes', 'articles.favtimes', 'articles.replynum', 'articles.date_post', 'articles.date_update', 'articles.status')
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
										return getImage( $matches[1] , $r[ $matches[1] ] , $matches[2] , null,false);
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

	//最新主题
	public function getArticles($page)
	{
		$articles = \DB::table('articles')
			->join('users','articles.authorid', '=', 'users.id')
			// ->join('attachments', 'articles')
			->where('status', '=', 0)
			->orderBy('date_post', 'desc')
			->select('articles.id', 'users.username as author', 'users.avatar', 'articles.authorid', 'articles.type', 'articles.subject', 'articles.location', 'articles.cover', 'articles.content', 'articles.attachment', 'articles.tags', 'articles.readtimes', 'articles.favtimes', 'articles.replynum', 'articles.date_post', 'articles.date_update', 'articles.status')
			->skip(0)
			->take(5)
			->get();
		
		$articles = json_decode(json_encode($articles), true);

		$images = array();
		$attachment_array = array();
		$author_array = array();

		foreach ($articles as $key => $value) {
			// 判断是否含有多个附件
			$attachments = explode(",",$value['attachment']);

			//计算附件数量
			$attachments_count = count($attachments) ? count($attachments) : 0;

			if($attachments_count > 1 ) {
				foreach ($attachments as $v) {
					$attachment_array[] = $v;
				}
			} else {
				$attachment_array[] = $value['attachment'];
			}
			
			$author_array[] = $value['authorid'];
			$articles[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
			$articles[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
			$articles[$key]['attachments_count'] = $attachments_count;
		}

		// $author_array = array_filter($author_array);
		// $avatars = \DB::table('users_profile')->whereIn('id', $author_array)->get();
		// $avatars = json_decode( json_encode($avatars) , True );

		//从二维数组中选择出key和键值
		// $avatars = array_column($avatars, 'avatar', 'id');

		// foreach ($articles as $key => $value) {
			// $articles[$key]['avatar'] = $avatars[$value['authorid']];
		// }

		if(isset($attachment_array)) {
			$attachment_array = array_filter($attachment_array);
			$images = \DB::table('attachments')->whereIn('id', $attachment_array)->get();
			$images = json_decode( json_encode($images) , True );

			foreach ($articles as $key => $value) {
				$content = preg_replace_callback( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i',
										function ($matches) use ($images, $value) {
											$r = array_filter($images, function($t) use ( $matches ) {
												$t['id'] = $matches[1];//图片id
												// $t['caption'] = $matches[2];
												return $t;
											});

											$r = array_column( $r, null, 'id');
											return getImage( $matches[1] , $r[ $matches[1] ] , null , $value['attachments_count'], true);

											//下面1为preg_replace_callback只替换第一个查找到的图片
										}, $articles[$key]['content'], 1);

				$content = 	preg_replace( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i', '', $content );
				$articles[$key]['content'] = $content;
			}
		}

		return Response::json(
			[
				'success' => true,
				'articles' => $articles
			]
		);

	}

	public function getComment($article_id)
	{
		$comments = \DB::table('comments')
			->join('users', 'comments.authorid', '=', 'users.id')
			->where('status', '=', 0)
			->where('articleid', '=', $article_id)
			->select('comments.id', 'users.username as author', 'users.avatar', 'comments.authorid', 'comments.comment', 'comments.favtimes', 'comments.replynum', 'comments.date_post',  'comments.status')
			->orderBy('date_post', 'asc')
			->skip(0)
			->take(5)
			->get();

		if ( isset($comments ) ) {
			$comments = json_decode(json_encode($comments), true);
			$comments = $comments;
			$author_array = array();
			
			foreach ($comments as $key => $value) {
				$author_array[] = $value['authorid'];
				$comments[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
				$comments[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
				$comments[$key]['avatar'] = attUrl().'/avatar/'.$value['avatar'].'!avatar_small';
			}

			$author_array = array_filter($author_array);
			// $avatars = \DB::table('users_profile')->whereIn('id', $author_array)->get();
			// $avatars = json_decode( json_encode($avatars) , True );

			// $avatars = array_column($avatars, 'avatar', 'id');

			// foreach ($comments as $key => $value) {
				// $comments[$key]['avatar'] = attUrl().'/avatar/'.$avatars[$value['authorid']].'!avatar_medium';
			// }

			return Response::json(
				[
					'success' => true,
					'comments' => $comments
				]
			);
		}

		return Response::json(
			[
				'success' => true,
				'comments' => ''
			]
		);
	}

	public function getProfile()
	{
		$result = \DB::table('users')
			->join('users','users.id','=','users.id')
			->where('id', Auth::user()->id)
			->get();
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
			->join('users','users_profile.id','=','users.id')
			->where('users_profile.id', $userId)
			->select('users.id', 'users.username', 'users.avatar', 'users_profile.banner', 'users_profile.gender', 'users_profile.birthyear', 'users_profile.birthmonth', 'users_profile.birthday', 'users_profile.bloodtype', 'users_profile.height', 'users_profile.weight', 'users_profile.signature', 'users_profile.status' )
			->get();

		if ( !$result ) {
			return Response::json(
				[
					'success' => false,
					'info' => '用户不存在'
				]
			);
		}

		$result = json_decode(json_encode($result[0]), true);

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
			$attachments = explode(",",$value['attachment']);
			$attachments_count = count($attachments);
			if($attachments_count > 1 ) {
				foreach ($attachments as $v) {
					$attachment_array[] = $v;
				}
			} else {
				$attachment_array[] = $value['attachment'];
			}
			
			$articles[$key]['attachments_count'] = $attachments_count;
			$articles[$key]['date_post_title'] = Carbon::createFromTimestamp($value['date_post'])->format('Y年m月d日 H时i分s秒');
			$articles[$key]['date_post'] = Carbon::createFromTimestamp($value['date_post'])->diffForHumans();
		}

		if($attachment_array) { // 附件处理
			$attachment_array = array_filter($attachment_array);
			$images = \DB::table('attachments')->whereIn('id', $attachment_array)->get();
			$images = json_decode( json_encode($images) , True );
			foreach ($articles as $key => $value) {
				$content = preg_replace_callback( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i',
										function ($matches) use ($images, $value) {
											$r = array_filter($images, function($t) use ( $matches ) {
												$t['id'] = $matches[1];//图片id
												$t['caption'] = $matches[2];
												return $t;
											});
											$r = array_column( $r, null, 'id');
											return getImage( $matches[1] , $r[ $matches[1] ] , $matches[2] , $value['attachments_count'] , true);
										}, $articles[$key]['content'], 1 );
				$content = preg_replace( '/\[picid\=([0-9]*)\](.*?)\[\/picid\]/i', '', $content);
				$articles[$key]['content'] = $content;
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
