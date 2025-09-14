<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;

class AuthToken
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Auth-Token');

        \Log::info('AuthToken middleware triggered', [
            'expected' => config('services.api.token'),
            'received' => $token,
            'route' => $request->path(),
        ]);

        if (!$token || $token !== config('services.api.token')) {
            \Log::warning('Unauthorized access attempt', [
                'ip' => $request->ip(),
                'token' => $token,
                'route' => $request->path(),
            ]);

            return $this->unauthorizedResponse();
        }

        return $next($request);
    }


}

