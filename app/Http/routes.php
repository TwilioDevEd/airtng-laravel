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
    '/auth/login', ['as' => 'login-index', function() {
        return response()->view('login');
    }]
);

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

// Vacation Property related routes
Route::get(
    '/property/new',
    ['as' => 'property-new',
     'middleware' => 'auth',
     function() {
         return response()->view('property.newProperty');
     }]
);

Route::get(
    '/properties',
    ['as' => 'property-index',
     'middleware' => 'auth',
     'uses' => 'VacationPropertyController@index']
);

Route::get(
    '/property/{id}',
    ['as' => 'property-show',
     'middleware' => 'auth',
     'uses' => 'VacationPropertyController@show']
);

Route::get(
    '/property/{id}/edit',
    ['as' => 'property-edit',
     'middleware' => 'auth',
     'uses' => 'VacationPropertyController@editForm']
);

Route::post(
    '/property/edit/{id}',
    ['uses' => 'VacationPropertyController@editProperty',
     'middleware' => 'auth',
     'as' => 'property-edit-action']
);

Route::post(
    '/property/create',
    ['uses' => 'VacationPropertyController@createNewProperty',
     'middleware' => 'auth',
     'as' => 'property-create']
);

// Reservation related routes
Route::post(
    '/property/{id}/reservation/create',
    ['uses' => 'ReservationController@create',
     'as' => 'reservation-create',
     'middleware' => 'auth']
);

Route::post(
    '/reservation/incoming',
    ['uses' => 'ReservationController@acceptReject',
     'as' => 'reservation-incoming']
);
