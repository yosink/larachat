<?php

namespace App\Handlers;


class SwooleHandler
{
    public function onOpen(\swoole_websocket_server $serv, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage(\swoole_websocket_server $server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "this is server");
    }

}