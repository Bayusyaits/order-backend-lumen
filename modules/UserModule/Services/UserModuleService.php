<?php
namespace Modules\UserModule\Services;

use Illuminate\Support\Facades\Log;
use Modules\UserModule\Entities\UserEntity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;
use stdClass;

/**
 * Class Service is a service that handle series of action after callback from DOKU
 *
 * @package App\Services
 */
class UserModuleService
{
    public static function join($condition)
    {
        return UserEntity::selectRaw('user.userId as id,
        user.userClientId as clientId,
        user.userFullName as fullName,
        user.userName as username,
        user.userToken as token,
        user.userPassword as password,
        userClient.userClientSignature as signature,
        userClient.userClientStatus as status')->
        where($condition)->join(
            'userClient',
            'userClient.userClientId',
            '=',
            'user.userClientId'
        );
    }
    public static function get($condition)
    {
        return UserEntity::selectRaw('userId as id,
        userFullName as fullName,
        userName as username,
        userToken as token,
        userPassword as password')->
        where($condition);
    }
    public static function post($value)
    {
        $response    = [];
        if (isset($value->username) && !empty($value->username)) {
            $data = self::get([[
                    'userName', '=', $value->username
            ]])->first();
            if (!$data) {
                $response  = self::add($value);
            } else {
                $response  = self::edit($value);
            }
        }
        return $response;
    }
    public static function put($value)
    {
        $response    = [];
        if (isset($value->username) && !empty($value->username)) {
            $response  = self::edit($value);
        }
        return $response;
    }
    protected static function add($body)
    {
        $action = null;
        DB::beginTransaction();
        if (isset($body->username) && isset($body->password)) {
            try {
                $ott = new UserEntity();
                $ott->userClientId  = $body->clientId;
                $ott->userFullName  = isset($body->fullName) ? $body->fullName : '';
                $ott->userName      = $body->username;
                $ott->userPassword  = $body->password;
                $ott->save();
                $action = self::get([['userId', '=', $ott->userId]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                Log::error("Error in processing add user, message: " . $message);
                Log::error('Error tracestring add user: ' . $e->getTraceAsString());
                DB::rollback();
            }
        } else {
            Log::error("Error in processing add user, username or password is empty");
        }
        return $action;
    }
    protected static function edit($body)
    {
        $action = null;
        DB::beginTransaction();
        if (isset($body->password) &&
        !empty($body->password) && isset($body->username)) {
            try {
                $ott = UserEntity::where([
                    [
                        'userName', '=', $body->username
                    ]
                ])->first();
                if (isset($body->fullName) && !empty($body->fullName)) {
                    $ott->userFullName = $body->fullName;
                }
                $ott->userToken    = generateRandomChar(32);
                $ott->save();
                $action = self::get([['userId', '=', $ott->userId]])->first();
                DB::commit();
            } catch (Throwable $e) {
                $message = $e->getMessage();
                Log::error("Error in processing edit user, message: " . $message);
                Log::error('Error tracestring edit user: ' . $e->getTraceAsString());
                DB::rollback();
            }
        } else {
            Log::error("Error in processing add user, username or password is empty");
        }
        return $action;
    }
}
