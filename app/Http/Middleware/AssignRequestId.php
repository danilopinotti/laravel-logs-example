<?php

namespace App\Http\Middleware;

use App\Support\ExecutionId;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $executionId = (new ExecutionId())->get();

        Context::add([
            'trace_id' => $executionId,
            'start_time' => microtime(true),
        ]);

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->headers
                ->set('Trace-Id', $executionId);
        }

        return $response;
    }
}
