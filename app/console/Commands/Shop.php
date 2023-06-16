<?php

namespace app\console\Commands;

use app\common\facades\SiteSetting;
use app\host\HostManager;
use app\process\CronKeeper;
use app\process\QueueKeeper;
use app\process\WebSocket;
use app\worker\Worker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Workerman\Timer;

class Shop extends Command
{
    protected $signature = 'shop {action} {--d}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '商城守护进程';

    public function handle()
    {
        global $argv;
        $action = $this->argument('action');

        $argv[0] = 'wk';
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d' : '';

        if ($action == 'start') {
            $this->start();
        }
        if ($action == 'stop') {
        }
        // 运行worker
        Worker::runAll();
    }

    private $startTime;

    /**
     * 队列进程管理
     */
    private function queueKeeper()
    {
        $worker = new Worker();
        $worker->count = 1;
        $worker->name = 'yun_shop_queue';
        app()->forgetInstance('redis');
        app()->forgetInstance('db');
        // 生成队列进程
        $worker->onWorkerStart = function ($worker) {
            // 避免多个服务器的work同一时间生成,产生不有必要的并发问题
            sleep(rand(0, 100) / 100);

            error_reporting(0);
            ini_set('display_errors', 1);
            app('redis')->refresh();
            app('db')->refresh();
            (new HostManager())->localhost()->clearPids();
            $queueKeeper = new QueueKeeper();
            $queueKeeper->main();

            $queueTimer = function () use ($queueKeeper) {
                $hostManager = new HostManager();
                $hostManager->localhost()->clearPids();
                $hostManager->localhost()->register();
                $queueKeeper->keepAlive();
            };
            call_user_func($queueTimer);
            Timer::add(30, $queueTimer);

            $worker->queueKeeper = $queueKeeper;
        };
        // 关闭队列进程
        $worker->onWorkerStop = function ($worker) {
            error_reporting(0);
            ini_set('display_errors', 1);
            Timer::delAll();
            $worker->queueKeeper->stop();
            $hostManager = new HostManager();
            $hostManager->localhost()->clearPids();
            $hostManager->localhost()->killAll();
            $hostManager->logout(gethostname());
        };
    }

    private function daemonKeeper()
    {
        $worker = new Worker();
        $worker->count = 1;
        $worker->name = 'yun_shop_daemon';
        app()->forgetInstance('redis');
        app()->forgetInstance('db');
        $worker->onWorkerStart = function ($worker) {
            sleep(rand(0, 100) / 100);

            error_reporting(0);
            ini_set('display_errors', 1);
            app('redis')->refresh();
            app('db')->refresh();
            Timer::add(1, function () {
                $hostManager = new HostManager();
                // 升级后延时重启队列（防止更新后无重启队列）
                //判断重启时间,如果60秒内重启过就不重启
//                if (($hostManager->restartTime() > $this->startTime) && ($this->startTime + 60 < $hostManager->restartTime())) {
                if (($hostManager->restartTime() > $this->startTime)) {
                    app('supervisor')->restart();
                }
            });
        };
        // 关闭队列进程
        $worker->onWorkerStop = function ($worker) {
            error_reporting(0);
            ini_set('display_errors', 1);
            Timer::delAll();
        };

    }

    private function cronKeeper()
    {
        $worker = new Worker();

        $worker->count = 1;
        $worker->name = 'yun_shop_cron';

        // 生成队列进程
        $worker->onWorkerStart = function ($worker) {
            // 避免多个服务器的work同一时间生成,产生不有必要的并发问题
            sleep(rand(0, 100) / 100);
            error_reporting(0);
            ini_set('display_errors', 1);
            app('redis')->refresh();
            app('db')->refresh();
            $cronKeeper = new CronKeeper();
            $cronKeeper->main();
            Timer::add(60, function () use ($cronKeeper) {
                $cronKeeper->run();
            });

        };
        // 关闭队列进程
        $worker->onWorkerStop = function ($worker) {
            error_reporting(0);
            ini_set('display_errors', 1);
            Timer::delAll();
            Redis::del('RunningCronJobs');
        };
    }

    private function webSocketKeeper()
    {
        if (SiteSetting::get('websocket')['is_open'] != 1) {
            return;
        }
        $worker = new Worker('websocket://0.0.0.0:8181');
        $worker->count = 1;
        $webSocket = new WebSocket($worker);
        // 连接时回调
        $worker->onConnect = [$webSocket, 'onConnect'];
        // 收到客户端信息时回调
        $worker->onMessage = [$webSocket, 'onMessage'];
        // 进程启动后的回调
        $worker->onWorkerStart = [$webSocket, 'onWorkerStart'];
        // 断开时触发的回调
        $worker->onClose = [$webSocket, 'onClose'];
    }


    private function start()
    {
        // 记录自身host
        (new HostManager())->localhost()->register();
        $this->startTime = time();
        $this->daemonKeeper();
        $this->queueKeeper();
        $this->cronKeeper();
        $this->webSocketKeeper();
    }
}
