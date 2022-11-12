<?php

namespace Modules\ProductModule\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use Closure;
use Throwable;

class ProductModuleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $domain  = '';
        $headers = getRequestHeaders();
        $signature = $request->header('X-Signature');
        $authorization = $request->bearerToken() &&
        !empty($request->bearerToken()) ?
        $request->bearerToken() : $request->header('Authorization');

        if (isset($headers["Origin"])) {
            $domain = removeHttp($headers["Origin"]);
        } elseif (isset($headers["Host"])) {
            $domain = $headers["Host"];
        }
        try {
            $credentials = JWT::decode($authorization, $signature, ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_UNAUTHORIZED,
                'message' => '[011275] provided token is expired.',
                'data'    => null
            ], setHTTPResponse(Response::HTTP_UNAUTHORIZED));
        } catch (Throwable $e) {
            return response()->json([
                "status"  => "ERROR",
                'code'    => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage(),
                'data'    => null
            ], setHTTPResponse(Response::HTTP_UNAUTHORIZED));
        }
        $request->userId    = $credentials->sub->id;
        $request->clientId   = isset($credentials->sub->clientId) ?
        $credentials->sub->clientId : 0;
        $request->token     = $credentials->sub->token;
        $request->signature = $signature;
        $request->domain    = $domain;
        return $next($request);
    }
}
