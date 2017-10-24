<?php

namespace App\Handlers;


use App\Services\RedisService;

class SwooleHandler
{
    protected $redis;
    public function __construct(RedisService $redisService)
    {
        $this->redis = $redisService;
    }

    public function onOpen(\swoole_websocket_server $serv, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage(\swoole_websocket_server $server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//        var_dump( json_decode($frame->data));
//        $server->push($frame->fd, "this is server");
        $info = json_decode($frame->data);
        $this->redis->login($frame->fd, $info);
        switch ($info->cmd){
            case 'login':
                //广播给所有其他人
                $broad_msg = $info->name . '上线了';
                foreach ($this->redis->getOnlineUsers() as $other) {
//                    if ($other != $frame->fd){
                        $server->push($other, $broad_msg);
//                    }
                }
                break;
            default:
                break;
        }
    }

}