<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SwooleListener
{
    public function onSwooleRequesting($event)
    {

    }

    public function onSwooleRequested($event)
    {

    }

    public function onSwooleWebsocketClosing($event)
    {

    }

    public function subscribe($events)
    {
        $events->listen(
            'swoole.requesting',
            'App\Listeners\SwooleListener@onSwooleRequesting'
        );

        $events->listen(
            'swoole.requested',
            'App\Listeners\UserEventSubscriber@onSwooleRequested'
        );

        $events->listen(
            'swoole.websocket.closing',
            'App\Listeners\UserEventSubscriber@onSwooleWebsocketClosing'
        );
    }
}
