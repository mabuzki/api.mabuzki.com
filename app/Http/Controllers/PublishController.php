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
use Intervention\Image\ImageManager;

class PublishController extends Controller
{
	public function publish() {
		header("Content-Type: text/html;charset=utf-8");

		$user = auth('api')->user();

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
		// $fakes = $finder->query('//p[@class="fake"]');
		$tmp = null;
		// $attachment['node'] = array();
		$attachment['image'] = array();
		// $attachment['filename'] = array();

		if( $nodes->length ) {
			foreach ($nodes as $key => $node) {
				//dd($node->firstChild->nodeType); //1
				//dd($node->nodeValue); //图片的caption

				$img = $node->getElementsByTagName('img')->item(0);
				$image_src = $img->attributes->getNamedItem('src');
				if ( !empty( $image_src ) ) {
					$value = (string)$image_src -> nodeValue;
				}
				preg_match('/([^imageid=]+)\)/i', $value, $match);

				dd($match);


				$newelement = $dom->createTextNode(':PiCiD#'.$match[1].':');
				// $node->replaceChild( $newelement , $node->firstChild );
				$node->replaceChild( $newelement , $node->firstChild );
				$node->parentNode->removeChild( $newelement , $node );
				array_push( $attachment['image'], $match[1] );
			}

			// $images = \DB::table('photos')->whereIn('filename',$attachment['filename'])->get();
			// $images = json_decode( json_encode($images) , True );
			// dd($images);
			// Collection {#297
			// 	#items: array:2 [
			// 	  0 => {#301
			// 		+"id": 4
			// 		+"article_id": ""
			// 		+"article_type": ""
			// 		+"userid": "1"
			// 		+"username": "抹布斯基"
			// 		+"filepath": "201811/17/"
			// 		+"filename": "210210_0000001_3DB7iqOBQXVR3hz_xBci_cDWR_uxsL"
			// 		+"caption": ""
			// 		+"salt": "WQdejS6ofZru"
			// 		+"suffix": "png"
			// 		+"animated": "0"
			// 		+"width": 239
			// 		+"height": 149
			// 		+"postip": "127.0.0.1"
			// 		+"upload_time": "1542459731"
			// 	  }
			// 	  1 => {#296
			// 		+"id": 5
			// 		+"article_id": ""
			// 		+"article_type": ""
			// 		+"userid": "1"
			// 		+"username": "抹布斯基"
			// 		+"filepath": "201811/17/"
			// 		+"filename": "210216_0000001_ywpLYPi5WZUSFO0_g4XZ_UICT_vKro"
			// 		+"caption": ""
			// 		+"salt": "4oSKT6hUIpP3"
			// 		+"suffix": "png"
			// 		+"animated": "0"
			// 		+"width": 140
			// 		+"height": 140
			// 		+"postip": "127.0.0.1"
			// 		+"upload_time": "1542459736"
			// 	  }
			// 	]
			//   }

			// foreach ($nodes as $key => $node) {
			// 	$img = $node->getElementsByTagName('img')->item(0);
			// 	// $caption = $node->getElementsByTagName('figcaption')->item(0);
			// 	$image_src = $img->attributes->getNamedItem('src');

			// 	if ( !empty( $image_src ) ) {
			// 		$image_src = $img->attributes->getNamedItem('src');
			// 		$value = (string)$image_src -> nodeValue;
			// 		preg_match('/\/[0-9][0-9]\/([^.]+)__/i', $value, $match);
			// 		foreach ($images as $key => $image) {
			// 			if($match[1] == $image['filename']) {
			// 				$newelement = $dom->createTextNode(':PiCiD#'.$image['id'].':');
			// 				$img->parentNode->replaceChild( $newelement , $img );
			// 				array_push( $attachment['image'], $image['id'] );
			// 				break;
			// 			}
			// 		}
			// 	}
			// }
		}

		// dd($content);

		// if( $fakes->length ) {
		// 	foreach ($fakes as $fake) {
		// 		$fake->parentNode->removeChild( $fake );
		// 	}
		// }
		
		// $content = '';
		// if ( $dom->documentElement ) {
		// 	$tmp = $dom->documentElement->getElementsByTagName('body')->item(0)->childNodes;
		// 	foreach ($tmp as $child) {
		// 		$content .= $child->ownerDocument->saveXML( $child );
		// 	}
		// }

		
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
			
			// 	$cover_query = \DB::table('photos')->where('id', $tmp)->first();
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
					'article_id' => $article_id
				]
			);
			// foreach ($image_id as $key => $value) {
			// 	\DB::table('photos')
			// 		->where('id', $value)
			// 		->update(['article_id' => $article_id]);
			// }
			
		}
		
	}
}
