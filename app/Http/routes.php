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

//Home related routes
Route::get(
    '/', ['as' => 'home', function () {
        return response()->view('home');
    }]
);

// User related routes
Route::get(
    '/login',
    ['uses' => 'UserController@login', 'as' => 'login']
);

Route::get(
    '/user/new',
    ['uses' => 'UserController@new', 'as' => 'user-new']
);
