<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
// use File;

require(__DIR__ . './../../../vendor/autoload.php');
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use LasseRafn\StringScript;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use \Colors\RandomColor;

function get_avatar($id, $size) {
	$id = abs(intval($id));
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
	$dir3 = substr($id, 5, 2);

	// return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($id, -2)."_avatar_".$size.".png";
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($id, -2)."_avatar.png";
}

// function set_home($id, $dir = '.') {
// 	$id = sprintf("%09d", $id);
// 	$dir1 = substr($id, 0, 3);
// 	$dir2 = substr($id, 3, 2);
// 	$dir3 = substr($id, 5, 2);

	// !is_dir($dir.'/'.$dir1) && File::makeDirectory($dir.'/'.$dir1,  $mode = 0777, $recursive = false);
	// !is_dir($dir.'/'.$dir1.'/'.$dir2) && File::makeDirectory($dir.'/'.$dir1.'/'.$dir2,  $mode = 0777, $recursive = false);
	// !is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && File::makeDirectory($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3,  $mode = 0777, $recursive = false);

// 	!is_dir($dir.'/'.$dir1) && Storage::makeDirectory($dir.'/'.$dir1);
// 	!is_dir($dir.'/'.$dir1.'/'.$dir2) && Storage::makeDirectory($dir.'/'.$dir1.'/'.$dir2);
// 	!is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && Storage::makeDirectory($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3);
// }

// function get_home($id) {
// 	$id = abs(intval($id));
// 	$id = sprintf("%09d", $id);
// 	$dir1 = substr($id, 0, 3);
// 	$dir2 = substr($id, 3, 2);
// 	$dir3 = substr($id, 5, 2);

// 	return $dir1.'/'.$dir2.'/'.$dir3;
// }

class AvatarController extends Controller
{
	public function show($id, $size, $cacheKey)
	{
		switch ($size) {
			case 0:
				$size = 'avatar_tiny';
				break;

			case 1:
				$size = 'avatar_small';
				break;

			case 2:
				$size = 'avatar_medium';
				break;

			case 3:
				$size = 'avatar_large';
				break;

			default:
				$size = 'avatar_large';
				break;
		}

		$avatar = get_avatar($id, $size);
		// $home = get_home($id);
		$resouce = 'avatar/'.$avatar;

		// if(!file_exists(public_path().'/uploads/avatars/'.$avatar)) {
		if(!Storage::exists('/avatar/'.$avatar)) {

			$results = \DB::table('users')->where('id', $id)->first();
			if(!$results) {
				header('Content-type:image/png');
				echo '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">';
				exit;
			}
			$results = json_decode(json_encode($results), True);

			// if(!is_dir(public_path().'/uploads/')) {
			// 	File::makeDirectory( public_path().'/uploads/',  $mode = 0777, $recursive = false);
			// }
	
			// if(!is_dir(public_path().'/uploads/avatars/')) {
			// 	File::makeDirectory( public_path().'/uploads/avatars/',  $mode = 0777, $recursive = false);
			// }
	
			// if(!is_dir(public_path().'/uploads/avatars/'.$home)) {
			// 	set_home($id, public_path().'/uploads/avatars/');
			// }

			// $id = abs(intval($id));
			// $id = sprintf("%09d", $id);
			// $aavatar = new InitialAvatar();
			$background = RandomColor::one(array('format'=>'hex','hue'=>array('blue', 'purple','red') ));

			$IA = new InitialAvatar();
			$new_avatar = $IA
				->autoFont()
				->name($results['username'])
				->background($background)
				->size(180)
				->fontSize(0.35)
				->generate()->stream('png', 100);

			$Image = new ImageManager();
			$path = public_path().'/uploads/avatars/'.$avatar;
			// $test->make($image_xs)->resize(28, 28)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_tiny').'.png', 100);
			// $test->make($image_s)->resize(36, 36)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_small').'.png', 100);
			// $test->make($image_m)->resize(128, 128)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_medium').'.png', 100);

			$Image->make($new_avatar)->resize(180, 180)->save($path, 100);
			$result = Storage::put( $resouce, file_get_contents( $path ) );
			if($result) {
				@unlink( $path );
			}
		}

		// $random = !empty($random) ? rand(1000, 9999) : '';
		$random = isset($cacheKey) ? '?'.$cacheKey : '?test';
		// $avatar_url = empty($random) ? $avatar : $avatar.'?random='.$random;

		$url = "//".config('app.CDN')."/".$resouce.'!'.$size.$random;

		header("Location: https:".$url);
		exit;
		// @readfile($url); 
		// exit;
	}
}
