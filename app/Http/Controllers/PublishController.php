<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Redirect, Response;
use Illuminate\Support\Facades\Input;
use File;
use Carbon\Carbon;

require( __DIR__ . './../../../vendor/autoload.php' );
use ConsoleTVs\Profanity\Facades\Profanity;
// use Intervention\Image\ImageManager;

class PublishController extends Controller
{
	public function publish() {
		header("Content-Type: text/html;charset=utf-8");

		$user = auth('api')->user();
		
		if ( !$user['email_verified_at'] ) {
			return Response::json(
				[
					'success' => false,
					'info' => '未认证账号不能发表文章'
				]
			);
		}

		if( !Input::get('content') ) {
			return Response::json(
				[
					'success' => false,
					'info' => '至少要写点东西'
				]
			);
		}

		$subject = addslashes( htmlspecialchars ( str_limit( trim( Input::get('subject') ), 80 ) ) );
		// trim 去首尾空; str_limit 限制长度 80; htmlspecialchars 转换<>; 
		
		if( !Profanity::blocker( $subject )->clean() ) {
			return Response::json(
				[
					'success' => false,
					'info' => '请更换一个标题'
				]
			);
		}

		$content = Input::get('content');
		$content_tmp = str_replace(array(" ","　","\t","\n","\r"), "", $content);
		$content_tmp = str_replace(array("&nbsp;",chr(194).chr(160)), "", $content_tmp);
		$content_tmp = str_replace(array("<p>","</p>","<span>","</span>","<div>","</div>"), "", $content_tmp);
		if( strlen( $content_tmp ) < 10 ) {
			return Response::json(
				[
					'success' => false,
					'info' => '文章内容太短'
				]
			);
		}

		$content_tmp = null;

		if( strlen( $subject ) < 1 ) $subject = date("Y-m-d H:i:s",time());
		$image_id = Input::get('image_id');

		$type = (int) Input::get('type');
		$action = (int) Input::get('action');
		$tags = array();
		$tags = Input::get('tags');
		// $tags = json_decode($tags);

		$dom = new \DomDocument();
		libxml_use_internal_errors(true);
		// $dom->loadHTML('<?xml encoding="UTF-8">' . $content[0]);
		$dom->loadHTML('<?xml encoding="UTF-8">' . $content);

		$finder = new \DomXPath($dom);
		$nodes = $finder->query('//figure');
		$tmp = null;
		// $attachment['node'] = array();
		$attachment['image'] = array();
		// $attachment['filename'] = array();

		if( $nodes->length ) {
			foreach ($nodes as $key => $node) {
				$img = $node->getElementsByTagName('img')->item(0);
				$caption = $node->getElementsByTagName('figcaption')->item(0)->textContent;
				if ( $imageid = $img->attributes->getNamedItem('data-image-id') ) {
					$value = (string)$imageid->value;
					if ( $caption ) {
						$newelement = $dom->createTextNode('[picid='.$value.']'.$caption.'[/picid]');
					} else {
						$newelement = $dom->createTextNode('[picid='.$value.'][/picid]');
					}
					$node->parentNode->replaceChild( $newelement , $node );
					// $node->parentNode->removeChild( $newelement , $node );
					array_push( $attachment['image'], $value );
				}
			}
		}

		// $br = $finder->query('//br[@data-mce-bogus="1"]');
		// dd($br);
		// $test = $finder->query('//br[@data-mce-bogus="1"]');
		// if ( $br -> length ) {
		// 	dd($br->parentNode);
		// 	$br->parentNode->parentNode->removeChild( $br->parentNode );
		// }

		// dd($content);

		// if( $fakes->length ) {
		// 	foreach ($fakes as $fake) {
		// 		$fake->parentNode->removeChild( $fake );
		// 	}
		// }
		
		$content = '';
		if ( $dom->documentElement ) {
			$tmp = $dom->documentElement->getElementsByTagName('body')->item(0)->childNodes;
			foreach ($tmp as $child) {
				$content .= $child->ownerDocument->saveXML( $child );
			}
		}

		
		// $content = addslashes( $content );
		// $content = addslashes( htmlentities($content, ENT_QUOTES, 'UTF-8') );
		// dd($content);

		@$tags = isset($tags) ? implode(",",$tags) : '';
		$attachment = implode(",",$attachment['image']);

		$cover = isset($cover) ? $cover : '';

		$article_id = \DB::table('articles')->insertGetId(
			[
				'author' => $user['username'],
				'authorid' => $user['id'],
				'type' => '',
				'subject' => $subject,
				'cover' => '',
				'content' => $content,
				// 'attachment' => '',
				'attachment' => $attachment,
				'tags' => $tags,
				'readtimes' => 0,
				'favtimes' => 0,
				'replynum' => 0,
				'date_post' => time(),
				'date_update' => ''
			]
		);

		if ( $article_id ) {

			// if ( !empty($tmp) ) { // 封面处理
			
			// 	$cover_query = \DB::table('attachments')->where('id', $tmp)->first();
			// 	$cover_query = json_decode(json_encode($cover_query), True);
			// 	$destinationPath = 'uploads/photo/'.$cover_query['filepath'].$cover_query['filename'].'__'.$cover_query['salt'].'.'.$cover_query['suffix'];
			// 	$path = realpath($destinationPath);
			// 	$newPath = public_path().'/uploads/tmp/cover/'.Auth::user()->id;

			// 	$image = new ImageManager( array('driver' => 'imagick') );
			// 	if ( $cover_query['animated'] ) {
			// 		$result = $image->make( $path )->widen( 300 )->encode( 'webp', 75 )->save( public_path().'/uploads/cover/'.$dir.$fileName.'__sm.webp' ) && copy( $path, public_path().'/uploads/cover/'.$dir.$fileName.'__'.$salt.'.'.$extension );
			// 	} else {
			// 		$result = $image->make( $Path )->widen( 330 )->save( public_path().'/uploads/tmp/cover/'.$article_id.$fileName.'__.'.$cover_query['suffix'], 90 ) && $image->make( $path )->widen( $small )->save( public_path().'/uploads/tmp/cover/'.$dir.$fileName.'__sm.'.$extension, 80 );
			// 	}

			// 	$im->destroy();

			// 	var_dump($destinationPath);
			// 	var_dump($cover_query);

			// }


			return Response::json(
				[
					'success' => 1,
					'article_id' => $article_id,
					'info' => '发表成功，即将跳转文章页面'
				]
			);
			// foreach ($image_id as $key => $value) {
			// 	\DB::table('attachments')
			// 		->where('id', $value)
			// 		->update(['article_id' => $article_id]);
			// }
			
		}
		
	}
}
