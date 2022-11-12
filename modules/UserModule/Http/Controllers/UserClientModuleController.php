<?php

namespace Modules\UserModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\UserModule\Services\UserClientService;

use Validator;
use Throwable;

class UserClientModuleController extends Controller
{
    const ID_MESSAGE = '[1102] ';

    public function post(Request $request)
    {
        $body = [];
        try {
            $action = service("UserModule", "UserClientModuleService")
                ::post($body, $request);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            return response()->json([
                "status"  => "ERROR",
                "code"    => Response::HTTP_EXPECTATION_FAILED,
                "message" => self::ID_MESSAGE . "failed, can't add user client",
                "data"    => env('APP_ENV') !== 'production' ? $message : null
            ], Response::HTTP_OK);
        }

        if (gettype($action) === 'object' && isset($action->data)) {
            $action = $action->data;
        } elseif (gettype($action) === 'array' && isset($action['data'])) {
            $action = $action['data'];
        }
        if (!$action || empty($action)) {
            return response()->json([
                'status'  => 'ERROR',
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => self::ID_MESSAGE.'fail to create data',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }
        return response()->json([
            "status"  => "SUCCESS",
            "code"    => Response::HTTP_OK,
            "message" => self::ID_MESSAGE . "data has been created",
            "data"    => $action
        ], Response::HTTP_OK);
    }

    public function get(Request $request)
    {
        $param       = $request->all();
        $get         = isset($param['get']) && !empty($param['get']) ? $param['get'] : 'one';
        $date = isset($param['date']) && !empty($param['date']) ?
        $param['date'] : null;

        $signature = isset($param['signature']) && !empty($param['signature']) ?
        $param['signature'] : null;
        $condition   = [];
        $pagination  = [];

        if (!empty($signature)) {
            array_push($condition, [
                'userClientSignature', '=', $signature
            ]);
        }

        if (!empty($date)) {
            array_push($condition, [
                'userClientCreatedDate', 'LIKE', "%{$date}%"
            ]);
        }

        try {
            $data = UserClientService::selectRaw('userClient.*');

            if (count($condition) > 0) {
                $data->where($condition);
            }
            if ($get === 'all') {
                if (isset($param['sortBy']) && $param['sortBy'] === 'id') {
                    $data = $data->orderBy('userClient.userClientId', 'desc');
                } else {
                    $data = $data->orderBy('userClient.userClientId', 'asc');
                }

                $data = $data->distinct()->get();
                $total   = count($data);
                if (isset($param['currentPage']) && isset($param['limit'])) {
                    $currentPage = $param['currentPage'] > 1
                    ? $param['limit'] * intval($param['currentPage'] - 1)
                    : 0;
                    $data = $data->skip($currentPage)
                        ->take(intval($param['limit']));
                    $pagination = [
                        'total'          => intVal($total),
                        'totalData'     => intVal($total),
                        'currentPage'   => intVal($param['currentPage']),
                        'limit'          => intVal($param['limit'])
                    ];
                } elseif (isset($param['limit'])) {
                    $data = $data->skip(0)
                    ->take(intVal($param['limit']));
                }
            } else {
                $data = $data->distinct()->first();
            }
        } catch (Throwable $e) {
            Log::error(self::ID_MESSAGE."can't get user client: {"
                . json_encode($param)."}, message: { ".$e->getMessage()."}");

            if (env('APP_ENV', 'development') === 'localhost') {
                $message = $e->getMessage();
            } else {
                $message = self::ID_MESSAGE.'internal server error';
            }
            return response()->json([
                "status"  => "ERROR",
                "code"    => Response::HTTP_INTERNAL_SERVER_ERROR,
                "message" => $message,
                "data"    => null
            ], setHTTPResponse(Response::HTTP_INTERNAL_SERVER_ERROR));
        }

        if (($data && is_object($data) && count($data) > 0)
        || ($data && is_object($data) && isset($data->userClientSignature))) {
            return response()->json([
                "status"  => "SUCCESS",
                "code"    => Response::HTTP_OK,
                "message" => self::ID_MESSAGE."list data",
                "data"    => [
                    $pagination,
                    "body"        => $data,
                ]
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => self::ID_MESSAGE.' data not found',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_NOT_FOUND));
        }
    }
}
