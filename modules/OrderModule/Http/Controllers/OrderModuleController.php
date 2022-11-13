<?php

namespace Modules\OrderModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Validator;
use Throwable;

class OrderModuleController extends Controller
{
    const ID_MESSAGE = '[1103] ';

    public function post(Request $request)
    {
        $input     = $request->all();
        $validator = Validator::make($input, [
            'value'       => 'required',
            'status'      => '',
            'permissions' => ''
        ]);

        if ($validator->fails()) {
            //return error message
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => self::ID_MESSAGE . 'validation is fail',
                'data'    => $input
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }

        if (isset($input['value']) && requestHasInvalidJSON($input['value'])) {
            //return error message
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => self::ID_MESSAGE . 'value is not valid',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }
        $validation = $this->validation($request);
        if ($validation && isset($validation["code"]) &&
            $validation["code"] !== Response::HTTP_OK
        ) {
            return response()->json($validation, setHTTPResponse($validation["code"]));
        }

        try {
            $action = service("OrderModule", "OrderModuleService")::post($request, $input);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            return response()->json([
                "status"  => "ERROR",
                "code"    => Response::HTTP_EXPECTATION_FAILED,
                "message" => self::ID_MESSAGE . "failed, can't add order",
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

    public function put(Request $request)
    {
        $input     = $request->all();
        $validator = Validator::make($input, [
            'value'       => 'required',
            'status'      => '',
            'permissions' => ''
        ]);

        if ($validator->fails()) {
            //return error message
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => self::ID_MESSAGE . 'validation is fail',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }

        if (isset($input['value']) && requestHasInvalidJSON($input['value'])) {
            //return error message
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => self::ID_MESSAGE . 'value is not valid',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }
        $validation = $this->validation($request, 'put');
        if ($validation && isset($validation["code"]) &&
            $validation["code"] !== Response::HTTP_OK
        ) {
            return response()->json($validation, setHTTPResponse($validation["code"]));
        }
        try {
            $action = service("OrderModule", "OrderModuleService")::put($request, $input);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            return response()->json([
                "status"  => "ERROR",
                "code"    => Response::HTTP_EXPECTATION_FAILED,
                "message" => self::ID_MESSAGE . "failed, can't edit order",
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
                'message' => self::ID_MESSAGE.'fail to update data',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }
        return response()->json([
            "status"  => "SUCCESS",
            "code"    => Response::HTTP_OK,
            "message" => self::ID_MESSAGE . "data has been updated",
            "data"    => $action
        ], Response::HTTP_OK);
    }

    public static function validationItem($data, $method)
    {
        $rules = [
            "productCode"   => "required",
            "paymentMethod" => "required",
            "state"         => "required",
            "totalWeight"   => "required",
            "totalQty"      => "required",
            "total"         => "required"
        ];
        $tmpResponse = [
            "status" => "SUCCESS",
            "code"   => Response::HTTP_OK,
            "message" => null,
            "error"   => [],
            "data"   => []
        ];
        if (is_array($data->items) && isset($data->items[0]) && count($data->items) > 0) {
            $makeValidator = [];
            for ($i = 0; $i <= count($data->items) - 1; $i++) {
                $makeValidator[$i] = Validator::make((array) $data->items[$i], $rules);
                if ($makeValidator[$i]->passes()) {
                    //TODO Handle your data
                    $tmpResponse["error"][$i] = false;
                } else {
                    //TODO Handle your error
                    $tmpResponse["error"][$i] = true;
                    $tmpResponse["message"][$i] = $makeValidator[$i]->errors()->all();
                }
            }
            if (array_search(true, $tmpResponse["error"], true) === 0) {
                $tmpResponse["status"] = "MULTI_STATUS";
                $tmpResponse["code"]    = Response::HTTP_EXPECTATION_FAILED;
            }
        } else {
            $tmpResponse["error"] = true;
            $tmpResponse["message"] = self::ID_MESSAGE."item must be object";
        }
        if ($tmpResponse["error"] === true) {
            $tmpResponse["status"] = "ERROR";
            $tmpResponse["code"]    = Response::HTTP_EXPECTATION_FAILED;
        }
        return $tmpResponse;
    }

    public function validation(Request $request, $method = 'post')
    {
        $input     = $request->all();
        $rules = [
            "customerName"     => "required",
            "number"           => "required|min:6|unique:order,orderNumber",
            "transactionDate"  => "",
            "totalCharge"      => "required",
            "totalWeight"      => "required",
            "totalQty"         => "required",
        ];
        if ($method === 'put') {
            $rules = [
                "customerName"     => "",
                "number"           => "required|min:6",
                "transactionDate"  => "",
                "totalCharge"      => "",
                "totalWeight"      => "",
                "totalQty"         => ""
            ];
        }
        $tmpResponse = [
            "status" => "SUCCESS",
            "code"   => Response::HTTP_OK,
            "message" => self::ID_MESSAGE . "created data",
            "error"   => [],
            "data"   => []
        ];
        $data = json_decode($input["value"]);
        if (is_array($data) && isset($data[0]) && count($data) > 0) {
            $makeValidator = [];
            for ($i = 0; $i <= count($data) - 1; $i++) {
                $makeValidator[$i] = Validator::make((array) $data[$i], $rules);
                if ($makeValidator[$i]->passes()) {
                    //TODO Handle your data
                    $validationItem = self::validationItem($data[$i], $method);
                    if (isset($validationItem['message']) &&
                    is_array($validationItem['message']) &&
                    count($validationItem['message']) > 0) {
                        $tmpResponse["error"][$i] = true;
                        $tmpResponse["status"] = $validationItem['status'];
                        $tmpResponse["code"] = $validationItem['code'];
                        $tmpResponse["message"] = $validationItem['message'];
                    } else {
                        $tmpResponse["error"][$i] = false;
                        $tmpResponse["message"] = null;
                    }
                } else {
                    //TODO Handle your error
                    $tmpResponse["error"][$i] = true;
                    $tmpResponse["message"] = $makeValidator[$i]->errors()->all();
                }
            }
            if (array_search(true, $tmpResponse["error"], true) === 0) {
                $tmpResponse["status"] = "MULTI_STATUS";
                $tmpResponse["code"]    = Response::HTTP_EXPECTATION_FAILED;
            }
        } else {
            //ONE ORDER
            $makeValidator = Validator::make((array) $data, $rules);
            $data = (object) $data;
            if ($makeValidator->passes()) {
                //TODO Handle your data
                $validationItem = self::validationItem($data, $method);
                if (isset($validationItem['message']) &&
                is_array($validationItem['message']) &&
                count($validationItem['message']) > 0) {
                    $tmpResponse["error"] = true;
                    $tmpResponse["status"] = $validationItem['status'];
                    $tmpResponse["code"] = $validationItem['code'];
                    $tmpResponse["message"] = $validationItem['message'];
                } else {
                    $tmpResponse["error"] = false;
                    $tmpResponse["message"] = null;
                }
            } else {
                //TODO Handle your error
                $tmpResponse["code"] = 500;
                $tmpResponse["status"] = "ERROR";
                $tmpResponse["error"] = true;
                $tmpResponse["message"] = $makeValidator->errors()->all();
            }
        }
        return [
            "status"  => $tmpResponse["status"],
            "code"    => $tmpResponse["code"],
            "message" => $tmpResponse["message"],
            "data"    => $tmpResponse["data"]
        ];
    }

    public function get(Request $request)
    {
        $param       = $request->all();
        $get         = isset($param['get']) && !empty($param['get']) ? $param['get'] : 'one';
        $search = isset($param['search']) && !empty($param['search']) ?
            strtolower($param['search']) : null;
        $number = isset($param['number']) && !empty($param['number']) ?
        $param['number'] : null;
        $condition   = [];
        $pagination  = [];
        $body = null;
        if (!empty($number)) {
            array_push($condition, [
                'order.orderNumber', '=', $number
            ]);
        }
        array_push($condition, [
            'order.orderUserId', '=', $request->userId
        ]);
        try {
            $data = service("OrderModule", "OrderModuleService")::hasMany($condition);
            if (isset($param['sortBy']) && $param['sortBy'] === 'id') {
                $data = $data->orderBy('order.orderId', 'desc');
            } else {
                $data = $data->orderBy('order.orderId', 'asc');
            }
            if (!empty($search)) {
                $data->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(order.orderCustomerName) like (?)', ["%{$search}%"]);
                    $query->orWhere('order.orderNumber', 'like', '%' . ($search) . '%');
                });
            }
            if ($get === 'all') {
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
                if ($data && count($data) > 0) {
                    $body = [
                        "pagination"  => $pagination,
                        "body"        => $data,
                    ];
                }
            } else {
                $body = $data->distinct()->first();
            }
        } catch (Throwable $e) {
            \Log::error(self::ID_MESSAGE."can't get order: {"
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

        if ($body) {
            return response()->json([
                "status"  => "SUCCESS",
                "code"    => Response::HTTP_OK,
                "message" => self::ID_MESSAGE."list data",
                "data"    => $body
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
