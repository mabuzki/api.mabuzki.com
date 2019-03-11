<?php
namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Redirect, Response;
use Request;

class HireController extends Controller
{
	//
	public function show() {
		return view('hire');
	}

	public function init() {
		return 'init';
	}

}
