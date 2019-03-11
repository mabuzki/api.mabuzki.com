<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Redirect, Response;
use Illuminate\Support\Facades\Input;

class DeleteController extends Controller
{
    //
     public function init(Request $request) {
     	if(!Auth::user()) {
     		return Response::json(
				[
					'success' => false,
					'info' => '您没有权限！'
				]
			);
     	}

		$id = Auth::user()->id;
		$type = Input::get('type');
		switch ( $type ) {
			case 'article': //删除文章
				break;

			case 'photo': // 删除图片
				$imageid = Input::get('imageid');
				if ( $imageid ) {
					$query = \DB::table('photos')->whereIn('id', $imageid)->get();
					$query = json_decode(json_encode($query), True);

					if ( \DB::table('photos')->whereIn('id', $imageid)->delete() ) {

						foreach ($query as $key => $value) {
							@unlink( public_path().'/uploads/photo/'.$value['filepath'].$value['filename'].'__'.$value['salt'].'.'.$value['suffix'] );
							@unlink( public_path().'/uploads/photo/'.$value['filepath'].$value['filename'].'__sm.'.$value['suffix'] );
							@unlink( public_path().'/uploads/photo/'.$value['filepath'].$value['filename'].'__sm.webp' );

							if( !$value['animated'] ) {
								@unlink( public_path().'/uploads/photo/'.$value['filepath'].$value['filename'].'__md.'.$value['suffix'] );
								@unlink( public_path().'/uploads/photo/'.$value['filepath'].$value['filename'].'__md.webp' );
							}
						}

						return Response::json(
							[
								'success' => 1
							]
						);
					}
				}
				
				break;
			
			case 'video': //删除短视频
				break;
		}
    }
}
