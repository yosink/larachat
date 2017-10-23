<?php


Route::get('/test', 'TestController@index');
Route::get('/', function (){
    return view('welcome');
});

