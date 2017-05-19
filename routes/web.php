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

    $ch = curl_init(env('ELS_SERVER', 'localhost'));
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); //timeout in seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (200==$retcode) {
        return view('home');
    } else {
        return 'Sorry, no Alive ElasticSearch Server found. Please check your environment and configuration';
    }



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
