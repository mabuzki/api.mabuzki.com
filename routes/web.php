<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// image get
Route::get('/avatar/{uid}/{size}', 'AvatarController@show');
Route::get('/banner/{uid}/{size}', 'BannerController@show');
// Route::get('/upload/{type}', function(){ App::abort(404); });
Route::get('/photo/{picid}/{size}', 'AttachController@show');

// json back
Route::get('/matrix/article-{article_id}', 'MatrixController@getArticle');
Route::get('/matrix/comment-{article_id}', 'MatrixController@getComment');

Route::get('/matrix/user-profile/{uid}', 'MatrixController@getUserProfile');
 
Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

Route::post('/auth/login', 'AuthController@login');
Route::post('/sign-up', 'SignUpController@register');

Route::group(['middleware' => 'jwt.api.auth'], function() {
// Route::group(['middleware' => 'auth:api'], function() {
	Route::post('/publish', 'PublishController@publish');
	Route::post('/upload/avatar', 'UploadController@avatar');
	Route::post('/upload/banner', 'UploadController@banner');
	Route::post('/upload/photo', 'UploadController@photo');
	Route::post('/auth/logout', 'AuthController@logout');
	Route::post('/auth/refresh', 'AuthController@refresh');
	Route::post('/auth/me', 'AuthController@me');

	Route::post('/matrix/setting-profile', 'MatrixController@getProfile');
	Route::post('/matrix/setting-account', 'MatrixController@getAccount');
	Route::post('/setting/profile/update', 'SettingController@updateSettingProfile');
});

// Route::group([
//	 'prefix' => 'auth'
// ], function ($router) {
	
// });

// Route::any('{all}', function(){ App::abort(404); });
