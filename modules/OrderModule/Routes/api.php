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
        'middleware' => 'Modules\OrderModule\Http\Middleware\OrderModuleMiddleware'
    ],
    function ($routeHeader) {
        $routeHeader->get('/', [
            'uses'             =>  'OrderModuleController@get',
            'as'               =>  'getOrder'
        ]);
        $routeHeader->post(
            '/',
            [
                'uses' =>  'OrderModuleController@post',
                'as'   =>  'postOrder'
            ]
        );
        $routeHeader->put(
            '/',
            [
                'uses' =>  'OrderModuleController@put',
                'as'   =>  'putOrder'
            ]
        );
        $routeHeader->delete(
            '/{code}',
            [
                'uses' =>  'OrderModuleController@delete',
                'as'   =>  'deleteOrder'
            ]
        );
    }
);
