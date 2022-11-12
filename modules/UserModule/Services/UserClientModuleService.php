<?php
namespace Modules\UserModule\Services;

use Modules\UserModule\Entities\UserClientEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;
use stdClass;

/**
 * Class Service is a service that handle series of action after callback from DOKU
 *
 * @package App\Services
 */
class UserClientModuleService
{
    public static function get($condition)
    {
        return UserClientEntity::selectRaw('userClientId as id,
        userClientSignature as signature,userClientStatus as status')->
        where($condition);
    }
    public static function post($value, Request $request)
    {
        $response    = [];
        $data = self::get([[
            'userClientIpAddress', '=', getClientIp()
        ]])->first();
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
        DB::beginTransaction();
        try {
            $ott = new UserClientEntity();
            $ott->userClientIpAddress = getClientIp();
            $ott->userClientPlatform  = $request->server('HTTP_USER_AGENT');
            $ott->userClientSignature = generateRandomCode(32);
            $ott->userClientStatus    = isset($body->status) ? $body->status : 'act';
            $ott->save();
            $action = self::get([['userClientSignature', '=', $ott->userClientSignature]])->
            first();
            DB::commit();
        } catch (Throwable $e) {
            $message = $e->getMessage();
            \Log::error("Error in processing add user client, message: " . $message);
            \Log::error('Error tracestring add user client: ' . $e->getTraceAsString());
            DB::rollback();
        }
        return $action;
    }
    protected static function edit($body, Request $request)
    {
        $action = null;
        DB::beginTransaction();
        if (isset($body->status) && !empty($body->status)) {
            try {
                $ott = UserClientEntity::where([
                    [
                        'userClientIpAddress', '=', getClientIp()
                    ]
                ])->first();
                $ott->userClientStatus    = $body->status;
                $ott->save();
                $action = self::get([['userClientSignature', '=', $ott->userClientSignature]])->
                first();
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
