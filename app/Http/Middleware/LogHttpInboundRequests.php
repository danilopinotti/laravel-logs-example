<?php

namespace App\Http\Middleware;

use App\Support\HttpLogger;
use Illuminate\Http\Request;

class LogHttpInboundRequests
{
    public function __construct(
        private readonly HttpLogger $httpLogger,
    ) {
    }

    public function handle(Request $request, \Closure $next): mixed
    {
        $this->logInboundRequest($request);

        $response = $next($request);

        $this->logInboundResponse($request, $response);

        return $response;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function logInboundRequest(Request $request): void
    {
        rescue(fn () => $this->httpLogger->logInboundRequest($request));
    }

    /**
     * Log the inbound response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return void
     */
    public function logInboundResponse(Request $request, mixed $response): void
    {
        rescue(fn () => $this->httpLogger
            ->logInboundResponse($request, $response));
    }
}
