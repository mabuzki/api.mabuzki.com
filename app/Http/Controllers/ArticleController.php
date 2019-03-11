<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Redirect, Response;
use File;

require( __DIR__ . './../../../vendor/autoload.php' );
use Carbon\Carbon;

class ArticleController extends Controller
{
	public function show($article_id)
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

		// $result['date_post_title'] = Carbon::createFromTimestamp($result['date_post']);
		$result['date_post'] = Carbon::createFromTimestamp($result['date_post'])->diffForHumans();

		$result['content'] = html_entity_decode( $result['content'] , ENT_QUOTES, 'UTF-8');
		$result['content'] = preg_replace_callback( '/##PIC::([0-9]*)##/i',
			function ($matches) use ($images){
				$r = array_filter($images, function($t) use ( $matches ) {
					return $t['id'] == $matches[1];
				});

				$r = array_column( $r, null, 'id');

				if ( $matches[1] ) {
					return getImage( $matches[1] , $r[ $matches[1] ] );
				}
			}, $result['content'] );
		

		if( !empty( $result['tags'] ) ) {
			$result['tags'] = explode(',',$result['tags']);
			$html = '';
			$html .= '<div class="tags">';
			foreach ( $result['tags'] as $tag ) {
				$html .= '<span class="tag"><a href="/tag/'.$tag.'" target="_blank">'.$tag.'</a></span>';
			}
			$html .= '</div">';
			$result['tags'] = $html;
		}

		return view('article', ['result' => $result]);

	}

	public function new()
	{
		$result = \DB::table('articles')
			->orderBy('date_post','desc')
			->where('status', '>=', 0)
			->paginate(1);

		$result = json_decode( json_encode($result) , True );
		var_dump($result);
		return view('new');
	}
}
