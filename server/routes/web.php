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


header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');
header('Access-Control-Allow-Credentials: true');


Route::get('/', function () {
    return view('welcome');
});

Route::resource('/frames', 'FrameController');
Route::resource('/video-recipes', 'VideoRecipeController');
Route::post('/video-recipes/run', 'VideoRecipeController@run');

