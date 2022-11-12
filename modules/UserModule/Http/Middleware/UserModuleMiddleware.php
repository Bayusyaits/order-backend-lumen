<?php

namespace Modules\UserModule\Http\Middleware;

use Closure;
use Throwable;
use App\Services\MsAuth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class UserModuleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $domain  = '';
        $headers = getRequestHeaders();

        if (isset($headers["Origin"])) {
            $domain = removeHttp($headers["Origin"]);
        } elseif (isset($headers["Host"])) {
            $domain = $headers["Host"];
        }

        $request->domain    = $domain;
        return $next($request);
    }
}
