<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Controllers\Controller;
// use Auth;
use Redirect, Response;
use Illuminate\Support\Facades\Input;
use File;

require(__DIR__ . './../../../vendor/autoload.php');
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

function set_home($id, $dir = '.') {
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
	$dir3 = substr($id, 5, 2);

	!is_dir($dir.'/'.$dir1) && File::makeDirectory($dir.'/'.$dir1,  $mode = 0777, $recursive = false);
	!is_dir($dir.'/'.$dir1.'/'.$dir2) && File::makeDirectory($dir.'/'.$dir1.'/'.$dir2,  $mode = 0777, $recursive = false);
	!is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && File::makeDirectory($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3,  $mode = 0777, $recursive = false);
}

function get_home($id) {
	$id = abs(intval($id));
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
	$dir3 = substr($id, 5, 2);

	return $dir1.'/'.$dir2.'/'.$dir3;
}

function linkSRC() {
	var_dump(env('REMOTE'));
}

// function get_target_extension($ext) {
// 	static $safeext  = array('attach', 'jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp', 'txt', 'zip', 'rar', 'mp3');
// 	return strtolower(!in_array(strtolower($ext), $safeext) ? 'attach' : $ext);
// }

function check_dir_type( $type ) {
	return !in_array($type, array('blog', 'photo', 'video', 'tmp', 'avatar', 'banner')) ? 'tmp' : $type;
}

function get_target_filename( $id ) {
	$u = str_pad($id, 7, "0", STR_PAD_LEFT);
	$filename = date('His').'_'.$u.'_'.str_random(15).'_'.str_random(4).'_'.str_random(4).'_'.str_random(4);
	return $filename;
}

function get_target_dir( $type, $extid = '', $check_exists = true ) {
	$subdir = $subdir1 = $subdir2 = '';
	$subdir1 = date('Ym');
	$subdir2 = date('d');
	$subdir = $subdir1.'/'.$subdir2.'/';

	$check_exists && check_dir_exists($type, $subdir1, $subdir2);
	return $subdir;
}

function check_dir_exists( $type, $sub1 = '', $sub2 = '' ) {

	$type = check_dir_type( $type );
	$typedir = $type ? ( public_path().'/uploads/'.$type ) : '';
	$subdir1  = $type && $sub1 !== '' ?  ( $typedir.'/'.$sub1 ) : '';
	$subdir2  = $sub1 && $sub2 !== '' ?  ( $subdir1.'/'.$sub2 ) : '';

	$res = $subdir2 ? is_dir( $subdir2 ) : ( $subdir1 ? is_dir( $subdir1 ) : is_dir( $typedir ) );

	if( !$res ) {
		!is_dir( $typedir ) && File::makeDirectory( $typedir, $mode = 0755, $recursive = false );
		!is_dir( $subdir1 ) && File::makeDirectory( $subdir1, $mode = 0755, $recursive = false );
		!is_dir( $subdir2 ) && File::makeDirectory( $subdir2, $mode = 0755, $recursive = false );
	}

	return $res;
}

function check( $method, $source ) {
	$imginfo = @getimagesize($source);
	return $imginfo;
}

/**
* Check if the provided file is an animated gif.
*
* @param string $fileName
* @return bool
*/
function isAnimatedGif($fileName)
{
    $fh = fopen($fileName, 'rb');

    if (!$fh) {
        return false;
    }

    $totalCount = 0;
    $chunk = '';

    // An animated gif contains multiple "frames", with each frame having a header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

    // We read through the file until we reach the end of it, or we've found at least 2 frame headers.
    while (!feof($fh) && $totalCount < 2) {
        // Read 100kb at a time and append it to the remaining chunk.
        $chunk .= fread($fh, 1024 * 100);
        $count = preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
        $totalCount += $count;

        // Execute this block only if we found at least one match,
        // and if we did not reach the maximum number of matches needed.
        if ($count > 0 && $totalCount < 2) {
            // Get the last full expression match.
            $lastMatch = end($matches[0]);
            // Get the string after the last match.
            $end = strrpos($chunk, $lastMatch) + strlen($lastMatch);
            $chunk = substr($chunk, $end);
        }
    }

    fclose($fh);

    return $totalCount > 1;
}

class UploadController extends Controller
{
	// public function handle($request, Closure $next)
    // {
    //     if (!session('user')) {
    //         return redirect('login');
	// 	}
    //     return $next($request);
	// }
	

	public function show()
	{
		return '请正常访问<a href="./../">网站</a>！';
	}

	public function crop()
	{
		
	}

	public function avatar()
	{
		$user = auth('api')->user();
		$id = $user['id'];

		if(!$id) {
			return Response::json(
				[
					'success' => false,
					'info' => '您没有权限！'
				]
			);
		}

		$file = Input::file('Filedata');
		
		
		if(!$file) {
			return Response::json(
				[
					'success' => false,
					'info' => '文件上传错误！'
				]
			);
		}

		$home = get_home($id);

		if(!is_dir(public_path().'/uploads/avatars/'.$home)) {
			set_home($id, public_path().'/uploads/avatars/');
		}

		$mime = $file->getMimeType();
		if($mime !== 'image/jpeg' && $mime !== 'image/gif' && $mime !== 'image/png') {
			return Response::json(
				[
					'success' => false,
					'info' => '请上传 png, jpg 或者 gif格式的图片'
				]
			);
		}		

		$destinationPath = 'uploads/tmp/avatar/';
		$extension = $file->getClientOriginalExtension();

		$fileName = str_pad($id,7,"0",STR_PAD_LEFT).'.'.$extension;
		$file->move($destinationPath, $fileName);
		
		$small = url('/avatar/'.$id.'/0?r='.mt_rand(1000000, 9999999));
		$link = url('/avatar/'.$id.'/2?r='.mt_rand(1000000, 9999999));
		
		$id = abs(intval($id));
		$id = sprintf("%09d", $id);
	
		$Avatar = new ImageManager();
		$result_s = $Avatar->make($destinationPath.$fileName)->resize(36, 36)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_tiny').'.png', 100);
		$result_s = $Avatar->make($destinationPath.$fileName)->resize(36, 36)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_small').'.png', 100);
		$result_m = $Avatar->make($destinationPath.$fileName)->resize(110, 110)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_medium').'.png', 100);
		$result_l = $Avatar->make($destinationPath.$fileName)->resize(180, 180)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_large').'.png', 100);

		if ($result_s && $result_m && $result_l) {
			return Response::json(
				[
					'success' => true,
					// 'id' => $id,
					'small' => $small,
					'link' => $link
					// 'link' => asset('/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_m110x110.png?r='.mt_rand(1000000, 9999999))
				]
			);
		}

		return Response::json(
			[
				'success' => false,
				'info' => '设置出错，请联系管理员'
			]
		);

	}

	public function banner()
	{
		if(!Auth::user()) return view('auth/login', ['needlogin' => true, 'refresh' => true]);
		$id = Auth::user()->id;
		if(!$id) {
			return Response::json(
				[
					'success' => false,
					'info' => '您没有权限！'
				]
			);
		}

		$file = Input::file('Filedata');
		if(!$file) {
			return Response::json(
				[
					'success' => false,
					'info' => '文件上传错误！'
				]
			);
		}

		$home = get_home($id);

		if(!is_dir(public_path().'/uploads/banner/'.$home)) {
			set_home($id, public_path().'/uploads/banner/');
		}

		$mime = $file->getMimeType();
		if($mime !== 'image/jpeg' && $mime !== 'image/gif' && $mime !== 'image/png') {
			return Response::json(
				[
					'success' => false,
					'info' => '请上传 png, jpg 或者 gif格式的图片'
				]
			);
		}

		$destinationPath = 'uploads/tmp/banner/';
		$extension = $file->getClientOriginalExtension();

		$fileName = str_pad($id,7,"0",STR_PAD_LEFT).'.'.$extension;
		$file->move($destinationPath, $fileName);

		$link = url('/banner/'.$id.'/2?r='.mt_rand(1000000, 9999999));

		$id = abs(intval($id));
		$id = sprintf("%09d", $id);
	
		$Banner = new ImageManager();
		$result = $Banner->make($destinationPath.$fileName)->resize(1500, 500)->save(public_path().'/uploads/banner/'.$home.'/'.substr($id, -2).'_banner_1500x500.png', 100);
		$result_s = $Banner->make($destinationPath.$fileName)->resize(600, 200)->save(public_path().'/uploads/banner/'.$home.'/'.substr($id, -2).'_banner_600x200.png', 100);

		if ($result && $result_s) {
			return Response::json(
				[
					'success' => true,
					'link' => $link
				]
			);
		}

		return Response::json(
			[
				'success' => false,
				'info' => '设置出错，请联系管理员'
			]
		);
	}

	public function photo()
	{
		$user = auth('api')->user();
		$id = $user['id'];

		$dir = get_target_dir( 'photo' );
		$fileName = get_target_filename( $id );

		$file = Input::file('Filedata');
		if(!$file) {
			return Response::json(
				[
					'success' => false,
					'info' => '文件上传错误！'
				]
			);
		}

		$info = @getimagesize( $file->getPathName() );
		$extension = $file->getClientOriginalExtension();
		$support = array('gif', 'jpg', 'jpeg', 'bmp', 'png');
		
		if( in_array( $extension , $support ) ) {
			if( $info === false || ( $extension == 'gif' && empty( $info['bits' ] ) ) ) {
				return Response::json(
					[
						'success' => false,
						'info' => '不支持的图片类型！'
					]
				);             
			}
		}

		// $intermediateSalt = md5(uniqid(rand(), true));
		// $salt = substr($intermediateSalt, 0, 12);
		$salt = str_random(12);
		$path = 'photo/'.$dir.$fileName.'__'.$salt.'.'.$extension;

		// $result = Storage::put( 'photo/'.$dir.$fileName.'__'.$salt.'.'$extension, $file ); // file path
		$animated = isAnimatedGif($file->getRealPath()) ? 1 : 0;
		$result = Storage::put( $path, file_get_contents( $file->getRealPath() ) ); // true

		if( $result ) {
			$imageid = \DB::table('photos')->insertGetId(
				[
					'userid' => $user['id'],
					'username' => $user['username'],
					'article_id' => '',
					'article_type' => '',
					'filepath' => $dir,
					'filename' => $fileName,
					'salt' => $salt,
					'suffix' => $extension,
					'animated' => $animated,
					'caption' => '',
					'width' => $info[0],
					'height' => $info[1],
					'postip' => Request::getClientIp(),
					'upload_time' => time()
				]
			);
		
			if ( $imageid ) { // 插入数据库successed
				$dir = env('cdn').'/photo/'.$dir;
				$image = $animated ? '//'.config('app.CDN').'/'.$path.'?imageid='.$imageid : '//'.config('app.CDN').'/'.$path.'@!middle?imageid='.$imageid;
				return Response::json(
					[
						'success' => true,
						'imageid' => $imageid,
						'image' => $image
					]
				);
			} else { // 插入数据库fail
				return Response::json(
					[
						'success' => false,
						'info' => '数据库链接失败' //数据库链接失败
					]
				);
			}
		}

		// $dir = get_target_dir( 'photo' );
		// $fileName = get_target_filename( $id );

		// $destinationPath = 'uploads/tmp/photo/';
		// $extension = $file->getClientOriginalExtension();

		// $tmpfileName = $fileName.'.'.$extension;
		// $file->move($destinationPath, $tmpfileName);
		// $path = realpath($destinationPath.$tmpfileName);

		// if( $imageinfo = @getimagesize( $destinationPath.$tmpfileName ) ) {
		// 	$width = $imageinfo[0];
		// 	$height = $imageinfo[1];
		// 	$small = $medium = $large = $width;

		// 	// $intermediateSalt = md5(uniqid(rand(), true));
		// 	// $salt = substr($intermediateSalt, 0, 12);
		// 	$salt = str_random(12);

		// 	if ( $width >= 360 ) $small = 360;
		// 	if ( $width >= 720 ) $medium = 720;
		// 	if ( $width >= 1080 ) $large = 1080;

		// 	// $im = new \Imagick( $path );
		// 	$Photo = new ImageManager( array('driver' => 'gd') );

		// 	if ( isAnimatedGif($path) ) {
		// 		$result = $Photo->make( $path )->widen( $small )->encode( 'webp', 75 )->save( public_path().'/uploads/photo/'.$dir.$fileName.'__sm.webp' ) && $Photo->make( $path )->widen( $small )->save( public_path().'/uploads/photo/'.$dir.$fileName.'__sm.'.$extension, 80 ) && copy( $path, public_path().'/uploads/photo/'.$dir.$fileName.'__'.$salt.'.'.$extension );
		// 		$animated = 1;
		// 	} else {
		// 		$result = $Photo->make( $path )->widen( $medium )->encode( 'webp', 75 )->save( public_path().'/uploads/photo/'.$dir.$fileName.'__md.webp' ) && $Photo->make( $path )->widen( $small )->encode( 'webp', 75 )->save( public_path().'/uploads/photo/'.$dir.$fileName.'__sm.webp' ) && $Photo->make( $path )->widen( $medium )->save( public_path().'/uploads/photo/'.$dir.$fileName.'__md.'.$extension, 90 ) && $Photo->make( $path )->widen( $small )->save( public_path().'/uploads/photo/'.$dir.$fileName.'__sm.'.$extension, 80 ) && copy( $path, public_path().'/uploads/photo/'.$dir.$fileName.'__'.$salt.'.'.$extension );
		// 		$animated = 0;
		// 	}

		// 	@unlink( $destinationPath.$tmpfileName );

		// 	// $im->destroy();

		// 	if ( $result ) { // 生成缩略图succeed
		// 		$imageid = \DB::table('photos')->insertGetId(
		// 			[
		// 				'userid' => $user['id'],
		// 				'username' => $user['username'],
		// 				'article_id' => '',
		// 				'article_type' => '',
		// 				'filepath' => $dir,
		// 				'filename' => $fileName,
		// 				'salt' => $salt,
		// 				'suffix' => $extension,
		// 				'animated' => $animated,
		// 				'caption' => '',
		// 				'width' => $width,
		// 				'height' => $height,
		// 				'postip' => Request::getClientIp(),
		// 				'upload_time' => time()
		// 			]
		// 		);

		// 		if ( $imageid ) { // 插入数据库successed
		// 			$dir = url('/uploads/photo/'.$dir);
		// 			if ( config('app.remote') ) $dir = config('app.remote_url').'/photo/'.$dir;
		// 			if ( $animated ) {
		// 				$image = $dir.'/'.$fileName.'__'.$salt.'.gif';
		// 			} else {
		// 				$image = $dir.'/'.$fileName.'__md.'.$extension;
		// 			}

		// 			return Response::json(
		// 				[
		// 					'success' => true,
		// 					'imageid' => $imageid,
		// 					'image' => $image.'?imageid='.$imageid
		// 				]
		// 			);
		// 		} else { // 插入数据库fail
		// 			return Response::json(
		// 				[
		// 					'success' => false,
		// 					'info' => '数据库链接失败' //数据库链接失败
		// 				]
		// 			);
		// 		}
			
			// } else { // 生成缩略图fail
			// 	return Response::json(
			// 		[
			// 			'success' => false,
			// 			'info' => '图片处理错误' //请联系管理员
			// 		]
			// 	);
			// }

		// } else { // 获取不到imginfo
		// 	@unlink( $destinationPath.$tmpfileName );
		// 	return Response::json(
		// 		[
		// 			'success' => false,
		// 			'info' => '图片格式错误' 
		// 		]
		// 	);
		// }
	}

}
