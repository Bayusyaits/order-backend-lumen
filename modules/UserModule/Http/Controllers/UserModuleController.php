<?php

namespace Modules\UserModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;

use Validator;
use Throwable;

class UserModuleController extends Controller
{
    public const ID_MESSAGE = "[1101] ";

    public function registration(Request $request)
    {
        $input     = $request->all();
        if (sizeof($input) <= 1) {
            $input = (array) json_decode($request->getContent());
        }
        $input     = sanitizePostXss($input);
        $validator = Validator::make($input, [
            'username'     => 'required|string|unique:user,userName',
            'fullName'     => '',
            'password'     => ['required',Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()]
        ]);
        if ($validator->fails()) {
            //return error message
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => $validator->errors()->all(),
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }
        try {
            $client  = service('UserModule', 'UserClientModuleService')
                ::get([['userClientIpAddress', '=', getClientIp()]])->first();
            if (!$client) {
                $client  = service('UserModule', 'UserClientModuleService')
                ::post(['userClientIpAddress', '=', getClientIp()]);
            }
            $body               = $input;
            $body['clientId']   = $client->id;
            $plainPassword      = $input['password'];
            $body['password']   = app('hash')->make($plainPassword);
            $action  = service('UserModule', 'UserModuleService')::post((object) $body);
            if ($action) {
                return self::login($request);
            } else {
                return response()->json([
                    "status"  => "ERROR",
                    'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => self::ID_MESSAGE.'fail to insert data',
                    'data'    => null
                ], setHTTPResponse(Response::HTTP_INTERNAL_SERVER_ERROR));
            }
        } catch (Throwable $e) {
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => self::ID_MESSAGE.''.$e->getMessage(),
                'data'    => null
            ], setHTTPResponse(Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    public static function login(Request $request)
    {
        $input     = $request->all();
        if (sizeof($input) <= 1) {
            $input = (array) json_decode($request->getContent());
        }
        $input     = sanitizePostXss($input);
        $validator = Validator::make($input, [
            'username'     => 'required',
            'password'     => ['required',Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()]
        ]);
        if ($validator->fails()) {
            //return error message
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_FORBIDDEN,
                'message' => $validator->errors()->all(),
                'data'    => null
            ], setHTTPResponse(Response::HTTP_FORBIDDEN));
        }
        try {
            $get  = service('UserModule', 'UserModuleService')
            ::join([['userName', '=', $input['username']]])->first();
            if (!$get) {
                return response()->json([
                    "status"  => "ERROR",
                    'code'    => Response::HTTP_NOT_FOUND,
                    'message' => self::ID_MESSAGE."username tidak ditemukan atau password salah",
                    'data'    => null
                ], setHTTPResponse(Response::HTTP_NOT_FOUND));
            }
            if (isset($get->password) && Hash::check($input['password'], $get->password)) {
                $loggedIn = service('UserModule', 'UserModuleService')
                ::put((object) $input);
                $data            = $loggedIn;
                $data->time      = timeId();
                $data->expired   = $data->time + 86400;
                $data->clientId  = $get->clientId;
                $data->signature = $get->signature;
                return response()->json([
                    "status"  => "SUCCESS",
                    'code'    => Response::HTTP_OK,
                    'message' => self::ID_MESSAGE.'logged in',
                    'data'    => [
                        "loggedIn"  => true,
                        "token"     => self::setToken($data),
                        "expired"   => $data->expired
                    ]
                ], setHTTPResponse(Response::HTTP_OK));
            } else {
                return response()->json([
                    "status"  => "SUCCESS",
                    'code'    => Response::HTTP_FORBIDDEN,
                    'message' => self::ID_MESSAGE.'username tidak ditemukan atau password salah',
                    'data'    => null
                ], setHTTPResponse(Response::HTTP_FORBIDDEN));
            }
        } catch (Throwable $e) {
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => self::ID_MESSAGE.''.$e->getMessage(),
                'data'    => null
            ], setHTTPResponse(Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    protected static function setToken($data)
    {
        $headers = getRequestHeaders();
        $domain  = '';

        if (isset($headers["Origin"])) {
            $domain = removeHttp($headers["Origin"]);
        } elseif (isset($headers["Host"])) {
            $domain = $headers["Host"];
        }
        $payload = [
            'iss' => $domain,                             // Issuer of the token
            'sub' => $data,   // Subject of the token
            'iat' => $data->time,                                               // Time when JWT was issued.
            'exp' => $data->expired                                             // Expiration time
         ];
         return JWT:: encode($payload, $data->signature);
    }
}
