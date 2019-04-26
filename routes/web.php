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
Route::get('/avatar/{uid}/{size}/{cacheKey}', 'AvatarController@show');
Route::get('/banner/{uid}/{size}', 'BannerController@show');
// Route::get('/upload/{type}', function(){ App::abort(404); });
Route::get('/photo/{picid}/{size}', 'AttachController@show');

// json back
Route::get('/article/{article_id}', 'MatrixController@getArticle');
Route::get('/articles-new', 'MatrixController@getArticles');
Route::get('/comment-{article_id}', 'MatrixController@getComment');

Route::get('/user-profile/{uid}', 'MatrixController@getUserProfile');
Route::get('/user-article/{uid}/{page}', 'MatrixController@getUserArticle');
 
// Route::middleware('auth:api')->get('/user', function (Request $request) {
// 	return $request->user();
// });

Route::post('/sign-up', 'SignUpController@register');

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::group(['middleware' => 'jwt.api.auth'], function() {
	Route::post('/publish', 'PublishController@publish');
	Route::post('/comment', 'CommentController@post');
	Route::post('/upload/avatar', 'UploadController@avatar');
	Route::post('/upload/banner', 'UploadController@banner');
	Route::post('/upload/photo', 'UploadController@photo');
	Route::post('/setting-profile', 'MatrixController@getProfile');
	Route::post('/setting-account', 'MatrixController@getAccount');
	Route::post('/setting-profile-update', 'SettingController@updateSettingProfile');
});
