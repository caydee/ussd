<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('Request','UssdController@Request');
Route::get('ussdmenus','UssdController@ussdmenus');

Route::get('testussd','UssdController@getussdmenus');
Route::post('SubscribeUser','ApiController@SubscribeUser');

Route::post('ussdpayment', 'ApiController@payment')->name('ussdpayment');
