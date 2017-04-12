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

Route::get('/', function () {
    return view('home');
});
//Manage Entity
Route::get('/entity', 'Admin\\EntityController@index');
Route::post('/entity', 'Admin\\EntityController@postIndex');
Route::get('/entity/show/{index}/{type}/{id}', 'Admin\\EntityController@showFromId');
Route::get('/entity/edit/{index}/{type}/{id}', 'Admin\\EntityController@getEdit');
Route::get('/entity/edit/{index}/{type}/{id}', 'Admin\\EntityController@getEdit');
Route::get('/entity/delete/{index}/{type}/{id}', 'Admin\\EntityController@delete');
Route::get('/entity/create/{index}/{type}', 'Admin\\EntityController@getCreate');
Route::post('/entity/create/{index}/{type}', 'Admin\\EntityController@postCreate');
