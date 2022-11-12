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
Route::patch('/', function () {
    return response()->json([
        "status"  => "ERROR",
        'code'    => 404,
        'message' => 'url not found',
        'data'    => null
    ], 404);
});

Route::group(
    [
        'middleware' => 'Modules\ProductModule\Http\Middleware\ProductModuleMiddleware'
    ],
    function ($routeHeader) {
        $routeHeader->get('/', [
            'uses'             =>  'ProductModuleController@get',
            'as'               =>  'getProduct'
        ]);
        $routeHeader->post(
            '/',
            [
                'uses' =>  'ProductModuleController@post',
                'as'   =>  'postProduct'
            ]
        );
        $routeHeader->put(
            '/',
            [
                'uses' =>  'ProductModuleController@put',
                'as'   =>  'putProduct'
            ]
        );
        $routeHeader->delete(
            '/{code}',
            [
                'uses' =>  'ProductModuleController@delete',
                'as'   =>  'deleteProduct'
            ]
        );
    }
);
