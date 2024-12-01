<?php

namespace App\Support;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HttpLogger
{
    public function __construct()
    {
    }

    public function logOutbound(ClientRequest $request, ClientResponse $response = null): void
    {
        if (!config('http-logger.enable', true)) {
            return;
        }

        $elapsedTime = round(($response?->transferStats?->getTransferTime() ?? 0) * 1000);

        $context = [
            'request' => [
                'method' => $method = $request->method(),
                'fullUrl' => $url = $this->sanitizeUrl($request->url()),
                'body' => $this->sanitizeBody($request->body()),
                'headers' => $this->sanitizeHeaders($request->headers()),
            ],
            'response' => [
                'statusCode' => $statusCode = $response?->status() ?? 'N/A',
                'body' => $this->sanitizeBody($response?->body()),
                'headers' => $this->sanitizeHeaders($response?->headers() ?? []),
            ],
            'elapsedTimeMs' => $elapsedTime,
        ];

        $this->log("REQ OUT: $statusCode $method $url", $context);
    }

    public function logInboundRequest(Request $request): void
    {
        if (!config('http-logger.enable', true) || !$this->shouldLogPath($request)) {
            return;
        }

        $context = [
            'method' => $method = $request->method(),
            'fullUrl' => $this->sanitizeUrl($request->fullUrl()),
            'path' => $path = Str::start($request->path(), '/'),
            'body' => $this->sanitizeBody($request->getContent()),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'ip' => $this->getRequestClientIp($request),
            'userAgent' => $request->userAgent(),
        ];

        $this->log("REQ IN: $method $path", $context);
    }

    public function logInboundResponse(Request $request, $response): void
    {
        if (!config('http-logger.enable', true) || !$this->shouldLogPath($request)) {
            return;
        }

        $method = $request->method();
        $path = Str::start($request->path(), '/');

        $context = [
            'statusCode' => $statusCode = $response->getStatusCode(),
            'body' => $this->sanitizeBody($response->getContent()),
            'headers' => $this->sanitizeHeaders($response->headers->all()),
            'elapsedTimeMs' => round((microtime(true) - LARAVEL_START) * 1000),
        ];

        $this->log("RES IN: $statusCode $method $path", $context);
    }

    private function sanitizeHeaders($headers)
    {
        $hiddenHeaders = config('http-logger.hidden_headers', []);

        $headers = collect($headers)
            ->mapWithKeys(fn ($value, $key) => [strtolower($key) => $value])
            ->toArray();

        foreach ($hiddenHeaders as $headerName) {
            if (array_key_exists($headerName, $headers)) {
                $headers[$headerName] = '*****';
            }
        }

        foreach ($headers as $headerName => $headerValue) {
            if (is_array($headerValue)) {
                $headers[$headerName] = implode(', ', $headerValue);
            }
        }

        return $headers;
    }

    private function sanitizeParameters(array $parameters, string $hidden = '*****'): array
    {
        $hiddenParameters = config('http-logger.hidden_parameters', []);

        foreach ($hiddenParameters as $parameterName) {
            if (array_key_exists($parameterName, $parameters)) {
                $parameters[$parameterName] = $hidden;
            }
        }

        return $parameters;
    }

    private function sanitizeBody(?string $body): string|int|array|null
    {
        if (!$body) {
            return null;
        }

        $jsonBody = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return rescue(
                fn () => (string) $body,
                '*** Purged due to not stringable value ***'
            );
        }

        if (is_array($jsonBody)) {
            return $this->sanitizeParameters($jsonBody);
        }

        return $jsonBody;
    }

    private function shouldLogPath(Request $request): bool
    {
        $path = Str::start($request->path(), '/');
        $onlyPaths = config('http-logger.only_paths', []);
        $exceptPaths = config('http-logger.except_paths', []);

        foreach ($exceptPaths as $exceptPath) {
            if (Str::is(Str::start($exceptPath, '/'), $path)) {
                return false;
            }
        }

        foreach ($onlyPaths as $onlyPath) {
            if (Str::is(Str::start($onlyPath, '/'), $path)) {
                return true;
            }
        }

        return true;
    }

    private function getRequestClientIp(Request $request)
    {
        $clientIp = $request->getClientIp();

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        return $clientIp;
    }

    private function sanitizeUrl(string $url): string
    {
        $queryParameters = UrlHelper::getQueryParams($url);
        $sanitizedQueryParameters = $this->sanitizeParameters($queryParameters, '--HIDDEN--');
        return UrlHelper::mergeQueries($url, $sanitizedQueryParameters);
    }

    private function log(string $message, array $context = [])
    {
        $logLevel = config('http-logger.log_level', 'info');

        Log::{$logLevel}($message, $context);
    }
}
