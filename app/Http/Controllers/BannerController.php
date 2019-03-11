<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use File;

function get_banner($id, $size) {
	$id = abs(intval($id));
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
	$dir3 = substr($id, 5, 2);

	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($id, -2)."_banner_".$size.".png";
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

class BannerController extends Controller
{
	public function show($id,$size)
	{
		switch ($size) {
			case 1:
				$size = '600x200';
				break;

			case 2:
				$size = '1500x500';
				break;

			default:
				$size = '1500x500';
				break;
		}

		if (!\DB::table('users')->where('id', $id)->first()) {
			return '查无此人';
		}

		$banner = get_banner($id, $size);

		$home = get_home($id);

		if(!is_dir(public_path().'/uploads/banner/'.$home)) {
			set_home($id, public_path().'/uploads/banner/');
		}

		if(!file_exists(public_path().'/uploads/banner/'.$banner)) {
			$id = abs(intval($id));
			$id = sprintf("%09d", $id);

			$results = \DB::table('users')->where('id', $id)->first();
			$results = json_decode(json_encode($results), True);

			
		}

		$random = !empty($random) ? rand(1000, 9999) : '';
		$banner_url = empty($random) ? $banner : $banner.'?random='.$random;

		$banner_url = URL('/uploads/banner/'.$banner_url);

		header("Content-Type:image/png");
		@readfile($banner_url); 
		exit;
	}
}
