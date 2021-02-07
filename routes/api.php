<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::namespace('Api')->group(function () {
    Route::post('register', 'AuthenticationController@register');
    Route::post('login', 'AuthenticationController@login');
    Route::middleware('check_user')->group(function () {
        Route::get('user-detail', 'UserController@userDetail');
        Route::post('verify-user', 'UserController@verifyUser');
        Route::post('transfer-money', 'UserController@transferMoney');
    });
});
