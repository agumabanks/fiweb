<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        return $next($request);
    }
}
