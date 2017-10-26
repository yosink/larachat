<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole server';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $serv;
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch ($action = $this->argument('action')) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->shutdown();
                break;
        }
    }

    protected function start(){
        $this->serv = new \swoole_websocket_server('0.0.0.0', 9501);
        $this->serv->set(array(
            'task_worker_num' => 10
        ));     // 具体参考文档
        $handler = App::make('swoolehandler');
        $this->serv->on('open', [$handler, 'onOpen']);
        $this->serv->on('message', [$handler, 'onMessage']);
        $this->serv->on('close', [$handler, 'onClose']);
        $this->serv->on('task', [$handler, 'onTask']);
        $this->serv->on('finish', [$handler, 'onFinish']);
        $this->serv->start();
    }

    protected function shutdown()
    {
        $this->serv->shutdown();
    }
}
