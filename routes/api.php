<?php

use Illuminate\Http\Request;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Credentials: true');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::match(['get','post'],'return_uri', 'LineController@returnLine');
Route::post('login', 'PassportController@login');
Route::post('register', 'PassportController@register');

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('get-details', 'PassportController@getDetails');
    Route::post('get-teacher-list', 'UserController@teacherList');
    Route::post('get-students-list', 'UserController@studentsList');
    Route::post('my-follow', 'UserController@myFollow');
    Route::post('check-follow', 'UserController@checkFollow');
});