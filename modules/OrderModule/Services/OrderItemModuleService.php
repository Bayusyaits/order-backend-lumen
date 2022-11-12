<?php namespace Modules\OrderModule\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\OrderModule\Entities\OrderItemEntity;
use Throwable;
use Carbon\Carbon;

/**
 * Class Service is a service that handle series of action after callback from DOKU
 *
 * @package App\Services
 */
class OrderItemModuleService
{
    public static function join($condition)
    {
        return OrderItemEntity::selectRaw('orderItem.orderItemId as id,
        orderItem.orderItemProductCode as productCode,
        orderItem.orderItemTotalWeight as totalWeight,
        orderItem.orderItemTotalQty as totalQty,
        orderItem.orderItemPaymentMethod as paymentMethod,
        orderItem.orderItemState as state,
        orderItem.orderItemTotal as total,
        order.orderId as orderId,
        order.orderNumber as number')->
        where($condition)->join(
            'order',
            'order.orderId',
            '=',
            'orderItem.orderItemOrderId'
        );
    }

    public static function get($condition)
    {
        return OrderItemEntity::selectRaw('orderItemId as id,
        orderItemProductCode as productCode,
        orderItemTotalWeight as totalWeight,
        orderItemTotalQty as totalQty,
        orderItemOrderId as orderId,
        orderItemPaymentMethod as paymentMethod,
        orderItemState as state,orderItemTotal as total')->
        where($condition);
    }

    public static function post($input = [], Request $request)
    {
        $body        = isset($input['value']) ? json_decode($input['value']) : $input;
        $response    = [];
        if (is_array($body)) {
            foreach ($body as $key => $val) {
                $response[$key] = self::insert($val, $request);
            }
        } else {
            $response = self::insert($body, $request);
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
        $data = self::get([
            ['orderItemProductCode', '=', $value->productCode],
            ['orderItemOrderId', '=', $value->orderId]
        ])->first();
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
        if (isset($body->productCode) && isset($body->total) &&
        isset($body->orderId)) {
            DB::beginTransaction();
            try {
                $ott = new OrderItemEntity();
                $ott->orderItemProductCode = $body->productCode;
                $ott->orderItemPaymentMethod = isset($body->paymentMethod) ?
                strtolower($body->paymentMethod) : 'pas';
                $ott->orderItemState = isset($body->state) ?
                strtolower($body->state) : 'done';
                $ott->orderItemTotalWeight = isset($body->totalWeight) ? intVal($body->totalWeight) : 0;
                $ott->orderItemTotal    = isset($body->total) ? intVal($body->total) : 0;
                $ott->orderItemTotalQty = isset($body->totalQty) ? intVal($body->totalQty) : 0;
                $ott->orderItemUserId   = $request->userId;
                $ott->orderItemOrderId  = $body->orderId;
                $ott->save();
                $action = self::get([['orderItemId', '=', $ott->orderItemId]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                \Log::error("Error in processing add orderItem, message: " . $message);
                \Log::error('Error tracestring add orderItem: ' . $e->getTraceAsString());
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
                $ott = OrderItemEntity::where([
                    [
                        'orderItemId', '=', $body->itemId
                    ]
                ])->first();
                if (isset($body->paymentMethod) && !empty($body->paymentMethod)) {
                    $ott->orderItemPaymentMethod = strtolower($body->paymentMethod);
                }
                if (isset($body->state) && !empty($body->state)) {
                    $ott->orderItemState    = strtolower($body->state);
                }
                if (isset($body->total) && !empty($body->total)) {
                    $ott->orderItemTotal    = intVal($body->total);
                }
                if (isset($body->totalQty) && !empty($body->totalQty)) {
                    $ott->orderItemTotalQty    = intVal($body->totalQty);
                }
                if (isset($body->totalWeight) && !empty($body->totalWeight)) {
                    $ott->orderItemTotalWeight    = intVal($body->totalWeight);
                }
                if (isset($body->totalQty) && !empty($body->totalQty)) {
                    $ott->orderItemTotalQty    = intVal($body->totalQty);
                }
                if (isset($body->productCode) && !empty($body->productCode)) {
                    $ott->orderItemProductCode    = $body->productCode;
                }
                $ott->save();
                $action = self::get([['orderItemId', '=', $ott->orderItemId]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                \Log::error("Error in processing edit orderItem, message: " . $message);
                \Log::error('Error tracestring edit orderItem: ' . $e->getTraceAsString());
                DB::rollback();
            }
        }
        return $action;
    }
}
