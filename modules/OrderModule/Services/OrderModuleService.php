<?php namespace Modules\OrderModule\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;
use Modules\OrderModule\Entities\OrderEntity;
use Modules\OrderModule\Entities\OrderChildrenEntity;

/**
 * Class Service is a service that handle series of action after callback from DOKU
 *
 * @package App\Services
 */
class OrderModuleService
{
    public static function hasMany($condition)
    {
        return OrderChildrenEntity::with('item')->selectRaw('orderId,orderId as id,
        orderCustomerName as customerName, orderTotalWeight as totalWeight,
        orderTotalQty as totalQty,
        orderNumber as number,orderTotalCharge as totalCharge')->
        where($condition);
    }

    public static function get($condition)
    {
        return OrderEntity::selectRaw('orderId as id,
        orderCustomerName as customerName, orderTotalWeight as totalWeight,
        orderTotalQty as totalQty,
        orderNumber as number,orderTotalCharge as totalCharge')->
        where($condition);
    }

    public static function insertItem($order, $input, Request $request)
    {
        $insert = [];
        if (is_array($input) && count($input) > 0) {
            foreach ($input as $key => $val) {
                $d = $val;
                $d->orderId = $order->id;
                $d->number  = $order->number;
                $insert[$key] = service("OrderModule", "OrderItemModuleService")::insert($d, $request);
            }
        }
        return $insert;
    }

    public static function post($input = [], Request $request)
    {
        $body        = isset($input['value']) ? json_decode($input['value']) : $input;
        $response    = [];
        if (is_array($body)) {
            foreach ($body as $key => $val) {
                $response[$key] = self::insert($val, $request);
                if (isset($response[$key]->id) && isset($val->items)) {
                    self::insertItem($response[$key], $val->items, $request);
                    $response = self::hasMany([['orderNumber', '=', $response[$key]->number]])->first();
                }
            }
        } else {
            $response = self::insert($body, $request);
            if (isset($response->id) && isset($body->items)) {
                self::insertItem($response, $body->items, $request);
                $response = self::hasMany([['orderNumber', '=', $response->number]])->first();
            }
        }
        return $response;
    }
    public static function put($input = [], Request $request)
    {
        $body        = isset($input['value']) ? json_decode($input['value']) : $input;
        $response    = [];
        if (is_array($body)) {
            foreach ($body as $key => $val) {
                $response[$key] = self::update($val, $request);
            }
        } else {
            $response = self::update($body, $request);
        }
        return $response;
    }
    public static function update($value, Request $request)
    {
        return self::edit($value, $request);
    }
    public static function insert($value, Request $request)
    {
        $response    = [];
        $data = self::get([['orderNumber', '=', $value->number]])->first();
        if (!$data) {
            $response  = self::add($value, $request);
        } else {
            $response  = self::edit($value, $request);
        }
        return $response;
    }
    protected static function add($body, Request $request)
    {
        $action = null;
        if (isset($body->customerName) && isset($body->totalCharge)) {
            DB::beginTransaction();
            try {
                $ott = new OrderEntity();
                $ott->orderCustomerName = $body->customerName;
                $ott->orderTotalWeight = isset($body->totalWeight) ? intVal($body->totalWeight) : 0;
                $ott->orderTotalCharge = isset($body->totalCharge) ? intVal($body->totalCharge) : 0;
                $ott->orderTotalQty = isset($body->totalQty) ? intVal($body->totalQty) : 0;
                $ott->orderUserId = $request->userId;
                $ott->orderNumber   = isset($body->number) ? $body->number : generateRandomCode(8);
                $ott->save();
                $action = self::hasMany([['orderNumber', '=', $ott->orderNumber]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                \Log::error("Error in processing add order, message: " . $message);
                \Log::error('Error tracestring add order: ' . $e->getTraceAsString());
                DB::rollback();
            }
        }
        return $action;
    }
    protected static function edit($body)
    {
        $action = null;
        DB::beginTransaction();
        if (isset($body->number) && !empty($body->number)) {
            try {
                $ott = OrderEntity::where([
                    [
                        'orderNumber', '=', $body->number
                    ]
                ])->first();
                if (isset($body->totalCharge) && !empty($body->totalCharge)) {
                    $ott->orderTotalCharge    = intVal($body->totalCharge);
                }
                if (isset($body->totalQty) && !empty($body->totalQty)) {
                    $ott->orderTotalQty    = intVal($body->totalQty);
                }
                if (isset($body->totalWeight) && !empty($body->totalWeight)) {
                    $ott->orderTotalWeight    = intVal($body->totalWeight);
                }
                if (isset($body->totalQty) && !empty($body->totalQty)) {
                    $ott->orderTotalQty    = intVal($body->totalQty);
                }
                if (isset($body->customerName) && !empty($body->customerName)) {
                    $ott->orderCustomerName    = $body->customerName;
                }
                $ott->save();
                $action = self::get([['orderNumber', '=', $ott->orderNumber]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                \Log::error("Error in processing edit order, message: " . $message);
                \Log::error('Error tracestring edit order: ' . $e->getTraceAsString());
                DB::rollback();
            }
        }
        return $action;
    }
}
