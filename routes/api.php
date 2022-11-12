<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function () use (&$router) {

    Route::get('/notif', function () {
        return response()->json([
            'status'  => 'error',
            'code'    => 404,
            'message' => 'url not found',
            'data'    => null
        ]);
    });
});
