<?php

namespace App\Listeners;

use App\Support\HttpLogger;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;

class ClientHttpRequestListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // ...
    }

    /**
     * Handle the event.
     */
    public function handle(ResponseReceived|ConnectionFailed $event): void
    {
        (new HttpLogger())
            ->logOutbound($event->request, $event->response ?? null);
    }
}
