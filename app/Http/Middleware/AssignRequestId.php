<?php

namespace App\Http\Middleware;

use App\Support\ExecutionId;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->headers
                ->set('Request-Id', app(ExecutionId::class)->get());
        }

        return $response;
    }
}
