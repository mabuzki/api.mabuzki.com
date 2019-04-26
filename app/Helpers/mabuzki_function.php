<?php

function __success($info, $extra1 = '', $extra2 = '', $extra3 = '')
{
	return Response::json(
		[
			'success' => 1,
			$extra1,
			$extra2
		]
	);
}

function __test() {
	return 'test';
}

function __message($type, $success, $info = '')
{
	return '';
}

function __avatar($userid, $size, $css = '')
{
	return '<img src="'.URL('/avatar/'.$userid.'/'.$size).'" class="image '.$css.' avatar">';
}

function __banner($userid, $size)
{
    return (mt_rand(1,2) == 1) ? 'something' : 'other';
}

function __pic($picid)
{
	$pic = \DB::select('select * from photos where id = ?', [$picid]);
	var_dump($pic);
    // return $pic;
}



