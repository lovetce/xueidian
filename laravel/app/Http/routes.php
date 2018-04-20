<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/hello', function () {
//    return view('welcome');
    return "hello";
});
//Route::get('/index','index/index');
/*这样设置路由*/
Route::get('/index','Auth\IndexController@index');
