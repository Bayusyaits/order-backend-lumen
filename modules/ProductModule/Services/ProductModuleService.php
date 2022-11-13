<?php namespace Modules\ProductModule\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;
use Modules\ProductModule\Entities\ProductEntity;

/**
 * Class Service is a service that handle series of action after callback from DOKU
 *
 * @package App\Services
 */
class ProductModuleService
{
    public static function get($condition)
    {
        return ProductEntity::selectRaw('productId as id,
        productName as name, productWeight as weight,
        productMinOrder as minOrder, productStock as stock,
        productCode as code,productPrice as price')->
        where($condition);
    }

    public static function post(Request $request, $input = [])
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
    public static function put(Request $request, $input = [])
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
        $data = self::get([['productCode', '=', $value->code]])->first();
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
        if (isset($body->name) && isset($body->price)) {
            DB::beginTransaction();
            try {
                $ott = new ProductEntity();
                $ott->productStock = isset($body->stock) ? intVal($body->stock) : 0;
                $ott->productName = $body->name;
                $ott->productMinOrder = isset($body->minOrder) ? intVal($body->minOrder) : 0;
                $ott->productUserClientId = $request->clientId;
                $ott->productWeight  = isset($body->weight) ? intVal($body->weight) : 0;
                $ott->productCode   = isset($body->code) ? $body->code : generateRandomCode(8);
                $ott->productPrice  = intVal($body->price);
                $ott->save();
                $action = self::get([['productCode', '=', $ott->productCode]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                \Log::error("Error in processing add user client, message: " . $message);
                \Log::error('Error tracestring add user client: ' . $e->getTraceAsString());
                DB::rollback();
            }
        }
        return $action;
    }
    protected static function edit($body)
    {
        $action = null;
        DB::beginTransaction();
        if (isset($body->code) && !empty($body->code)) {
            try {
                $ott = ProductEntity::where([
                    [
                        'productCode', '=', $body->code
                    ]
                ])->first();
                if (isset($body->price) && !empty($body->price)) {
                    $ott->productPrice    = intVal($body->price);
                }
                if (isset($body->stock) && !empty($body->stock)) {
                    $ott->productStock    = intVal($body->stock);
                }
                if (isset($body->weight) && !empty($body->weight)) {
                    $ott->productWeight    = intVal($body->weight);
                }
                if (isset($body->minOrder) && !empty($body->minOrder)) {
                    $ott->productMinOrder    = intVal($body->minOrder);
                }
                if (isset($body->name) && !empty($body->name)) {
                    $ott->productName    = $body->name;
                }
                $ott->save();
                $action = self::get([['productCode', '=', $ott->productCode]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                \Log::error("Error in processing edit user client, message: " . $message);
                \Log::error('Error tracestring edit user client: ' . $e->getTraceAsString());
                DB::rollback();
            }
        }
        return $action;
    }
}
