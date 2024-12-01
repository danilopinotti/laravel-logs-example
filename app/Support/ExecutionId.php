<?php

namespace App\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class ExecutionId implements \Stringable
{
    private const CLI_PREFIX = 'CLI';
    private const WEB_PREFIX = 'WEB';

    private string $id;

    public function __construct()
    {
        $prefix = App::runningInConsole()
            ? self::CLI_PREFIX
            : self::WEB_PREFIX;

        $this->id = $prefix . ':' . Str::ulid();
    }

    public function get(): string
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }
}
