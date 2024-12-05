<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Context;
use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class LogJsonFormatter extends JsonFormatter
{
    /**
     * @inheritDoc
     */
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalize($record);

        $context = Context::only(['trace_id', 'start_time']);

        $data = [
            'level' => strtolower($normalized['level_name']),
            'message' => $normalized['message'],
            'origin' => $this->getOrigin(),
            'data' => Arr::except(
                data_get($normalized, 'context'),
                ['contextName', 'timestamp']
            ),
            'traceId' =>  data_get($context, 'trace_id'),
            'timestamp' => data_get($normalized, 'context.timestamp')
                ?? Carbon::make($normalized['datetime']),
        ];

        return $this->toJson($data, true) . ($this->appendNewline ? "\n" : '');
    }

    private function getOrigin(): string
    {
        $backtrace = debug_backtrace();
        $caller = Arr::first($backtrace, static function (array $trace) {
            return isset($trace['file'])
                && $trace['file'] !== __FILE__
                && !str_contains($trace['file'], 'vendor');
        });
        $caller = $caller ?? $backtrace[0];
        $fileWithoutExtension = pathinfo($caller['file'], PATHINFO_FILENAME);
        return $fileWithoutExtension . ':' . $caller['line'];
    }
}
