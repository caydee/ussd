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

Route::get('Request', 'UssdController@Request');
Route::get('content', 'ApiController@content')->name('content');
Route::post('createcontent', 'ApiController@CreateContent')->name('CreateContent');
Route::post('editcontent', 'ApiController@EditContent')->name('CreateContent');

Route::get('prefixes', 'ApiController@prefixes');
Route::get('postairtime', 'ApiController@postairtime');

Route::post('airtimepayment', 'ApiController@mpesa_callback');
