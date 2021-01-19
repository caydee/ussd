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
// Route::fallback(fn()=>[
//     'code'=>'404',
//     'reason'=>'Not Defined'
// ]);

Route::get('Request','UssdController@Request');

Route::post('ussdpayment', 'ApiController@payment')->name('ussdpayment');

Route::get('GetSubscribers', 'ApiController@GetSubscribers')->name('GetSubscribers');
Route::get('GetSessions', 'ApiController@GetSessions')->name('GetSessions');
Route::get('Songs', 'ApiController@Songs')->name('Songs');

Route::post('AddSongs', 'ApiController@AddSongs')->name('AddSongs');
Route::post('EditSongs', 'ApiController@EditSongs')->name('EditSongs');
