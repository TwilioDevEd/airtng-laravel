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

// Session related routes
Route::get(
    '/login', ['as' => 'login-index', function() {
        return response()->view('login');
    }]
);

Route::post(
    '/login',
    ['uses' => 'SessionController@login', 'as' => 'login-action']
);

Route::get(
    '/logout', ['as' => 'logout', function() {
        Auth::logout();
        return redirect()->route('home');
    }]
);

// User related routes
Route::get(
    '/user/new', ['as' => 'user-new', function() {
        return response()->view('newUser');
    }]
);

Route::post(
    '/user/create',
    ['uses' => 'UserController@createNewUser', 'as' => 'user-create', ]
);

Route::get(
    '/vacationProperty/new',
    [
        'uses' => 'UserController@newUser',
        'as' => 'vacation-property-new',
        'middleware' => 'auth'
    ]
);
