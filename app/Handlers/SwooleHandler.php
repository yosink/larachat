<?php

namespace App\Handlers;


use App\Services\RedisService;

class SwooleHandler
{
    const MESSAGE_MAX_LEN = 1024; //单条消息不得超过1K
    const MESSAGE_INTERVAL_LIMIT = 10;  // 再次发送信息的时间间隔
    protected $lastSendTime = [];
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
        var_dump( json_decode($frame->data));
        $info = json_decode($frame->data);
//        $server->push($frame->fd, $info->name);
        $message = [];
        switch ($info->cmd) {
            case 'login':
                $this->redis->login($frame->fd, $info);
                //广播给所有其他人
                $message['name'] = $info->name;
                $message['cmd'] = 'newUser';
                foreach ($this->redis->getOnlineUsers() as $other) {
////                    if ($other != $frame->fd){
                    $server->push($other, json_encode($message));
////                    }
                }
                break;
            case 'message':
                $info->cmd = 'fromMsg';
                if (strlen($info->content) > self::MESSAGE_MAX_LEN) {
                    $server->push($frame->fd, json_encode(['code' => 102, 'msg' => '消息内容不能超过1K']));
                    return;
                }
                echo isset($this->lastSendTime[$frame->fd]) ? $frame->fd.'上次登录时间：'.$this->lastSendTime[$frame->fd] : $frame->fd.'上次登录时间还没有被记录';
                echo "\n";
                if (isset($this->lastSendTime[$frame->fd]) && $this->lastSendTime[$frame->fd] > (time() - self::MESSAGE_INTERVAL_LIMIT)) {
                    echo 'in'."\n";
                    $server->push($frame->fd, json_encode(['code' => 104, 'msg' => self::MESSAGE_INTERVAL_LIMIT . 's以后才能再次发送']));
                    return;
                }
                $this->lastSendTime[$frame->fd] = time();
                if ($info->chanel == 0) {   // 群发
                    foreach ($this->redis->getOnlineUsers() as $other) {
                        if ($other != $frame->fd) {
                            $server->push($other, json_encode($info));
                        }
                    }
                    // 使用task异步执行记录聊天历史
                    $server->task(serialize([
                        'cmd' => 'addHistroy',
                        'msg' => $info,
                        'fd' => $frame->fd
                    ]));
                } elseif ($info->chanel == 1) {  // 私聊
                    $server->push($info->to, json_encode($info));
                }
                break;
            case 'logout':
                $this->redis->logout($frame->fd);
                $server->push($frame->fd,json_encode(['cmd'=>'logout']));
                break;
            default:
                break;
        }
    }

    public function onClose(\swoole_websocket_server $server, $fd)
    {
        $this->redis->logout($fd);
        $server->push($fd, json_encode(['cmd' => 'logout']));
        echo "client {$fd} closed\n";
    }

    public function onTask(\swoole_server $serv, int $task_id, int $src_worker_id, $data)
    {
        $req = unserialize($data);
        if ($req) {
            switch ($req['cmd']) {
                case 'addHistroy':
                    if (empty($req['msg'])) {
                        $req['msg'] = '';
                    }
                    var_dump($req) ;
                        // 存入mysql
                    $this->redis->addHistroy($req['fd'], $req['msg']);
                    break;
            }
        }
    }

    public function onFinish(\swoole_server $serv, int $task_id, string $data)
    {
        var_dump($data);
        echo '\n';
    }

}