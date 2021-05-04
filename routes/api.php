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

Route::prefix('products')->group(function() {
    Route::patch('{product}/upload-image', 'ProductsController@uploadImage');
    Route::patch('{product}/attach-to-user', 'ProductsController@attachToUser');
    Route::patch('{product}/detach-from-user', 'ProductsController@detachFromUser');
    Route::get('/belongs-to-user', 'ProductsController@showUserProducts');
});

Route::apiResource('products', 'ProductsController');
