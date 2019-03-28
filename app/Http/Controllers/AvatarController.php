<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use File;

require(__DIR__ . './../../../vendor/autoload.php');
// use Md\MDAvatars;
// use Laravolt\Avatar\Avatar;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use LasseRafn\StringScript;
use Intervention\Image\ImageManager;
use \Colors\RandomColor;

function get_avatar($id, $size) {
	$id = abs(intval($id));
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
	$dir3 = substr($id, 5, 2);

	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($id, -2)."_avatar_".$size.".png";
}

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

class AvatarController extends Controller
{
	public function show($id,$size)
	{
		switch ($size) {
			case 0:
				$size = config('mabuzki.avatar_tiny');
				break;

			case 1:
				$size = config('mabuzki.avatar_small');
				break;

			case 2:
				$size = config('mabuzki.avatar_medium');
				break;

			case 3:
				$size = config('mabuzki.avatar_large');
				break;

			default:
				$size = config('mabuzki.avatar_large');
				break;
		}

		$avatar = get_avatar($id, $size);
		$home = get_home($id);

		if(!file_exists(public_path().'/uploads/avatars/'.$avatar)) {

			$results = \DB::table('users')->where('id', $id)->first();
			if(!$results) {
				echo '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">';
				header('Content-type:image/png');
				
				exit;
			}
			$results = json_decode(json_encode($results), True);

			if(!is_dir(public_path().'/uploads/')) {
				File::makeDirectory( public_path().'/uploads/',  $mode = 0777, $recursive = false);
			}
	
			if(!is_dir(public_path().'/uploads/avatars/')) {
				File::makeDirectory( public_path().'/uploads/avatars/',  $mode = 0777, $recursive = false);
			}
	
			if(!is_dir(public_path().'/uploads/avatars/'.$home)) {
				set_home($id, public_path().'/uploads/avatars/');
			}

			$id = abs(intval($id));
			$id = sprintf("%09d", $id);
			$aavatar = new InitialAvatar();
			$background = RandomColor::one(array('format'=>'hex','hue'=>array('blue', 'purple','red') ));

			$image_xs = $aavatar
				->autoFont()
				->name($results['username'])
				->background($background)
				->size(28)
				->fontSize(0.35)
				->generate()->stream('png', 100);

			$image_s = $aavatar
				->autoFont()
				->name($results['username'])
				->background($background)
				->size(36)
				->fontSize(0.35)
				->generate()->stream('png', 100);

			$image_m = $aavatar
				->autoFont()
				->name($results['username'])
				->background($background)
				->size(128)
				->fontSize(0.35)
				->generate()->stream('png', 100);

			$image_l = $aavatar
				->autoFont()
				->name($results['username'])
				->background($background)
				->size(180)
				->fontSize(0.35)
				->generate()->stream('png', 100);

			$test = new ImageManager();
			$test->make($image_xs)->resize(28, 28)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_tiny').'.png', 100);
			$test->make($image_s)->resize(36, 36)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_small').'.png', 100);
			$test->make($image_m)->resize(128, 128)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_medium').'.png', 100);
			$test->make($image_l)->resize(180, 180)->save(public_path().'/uploads/avatars/'.$home.'/'.substr($id, -2).'_avatar_'.config('mabuzki.avatar_large').'.png', 100);
		}

		$random = !empty($random) ? rand(1000, 9999) : '';
		$avatar_url = empty($random) ? $avatar : $avatar.'?random='.$random;

		$avatar_url = URL('/uploads/avatars/'.$avatar_url);

		header("Content-Type:image/png");
		@readfile($avatar_url); 
		exit;
	}
}
