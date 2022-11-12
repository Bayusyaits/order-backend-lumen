<?php


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Now create something great!
|
*/

/**
 * Empty param or uri
 * @return not found
 */

Route::group(
    [
        'prefix'     => 'client',
        'middleware' => 'Modules\UserModule\Http\Middleware\UserModuleMiddleware'
    ],
    function ($routeHeader) {
        Route::patch('/', function () {
            return response()->json([
                "status"  => "ERROR",
                'code'    => 404,
                'message' => 'url not found',
                'data'    => null
            ], 404);
        });
        Route::put('/', function () {
            return response()->json([
                "status"  => "ERROR",
                'code'    => 404,
                'message' => 'url not found',
                'data'    => null
            ], 404);
        });
        $routeHeader->get('/', [
            'uses'             =>  'UserClientModuleController@get',
            'as'               =>  'getUserClient'
        ]);
        $routeHeader->post(
            '/',
            [
                'uses' =>  'UserClientModuleController@post',
                'as'   =>  'postUserClient'
            ]
        );
    }
);
