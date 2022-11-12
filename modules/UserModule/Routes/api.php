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
        'middleware' => 'Modules\UserModule\Http\Middleware\UserModuleMiddleware'
    ],
    function ($routeHeader) {
        $routeHeader->get('/', [
            'uses'             =>  'UserModuleController@get',
            'as'               =>  'getUser'
        ]);
        $routeHeader->post(
            '/registration',
            [
                'uses' =>  'UserModuleController@registration',
                'as'   =>  'registrationUser'
            ]
        );
        $routeHeader->post(
            '/login',
            [
                'uses' =>  'UserModuleController@login',
                'as'   =>  'loginUser'
            ]
        );
    }
);

include_once 'api/user-client.php';
