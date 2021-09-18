<?php

namespace app\console\Commands;

use app\host\HostManager;
use app\process\CronKeeper;
use app\process\QueueKeeper;
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

    private function logReader()
    {
        $worker = new Worker('websocket://0.0.0.0:2348');
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
            Timer::add(1, function () use ($worker) {
                foreach ($worker->connections as $connection) {
                    $fileStream = $connection->logFileInfo['file_stream'];
                    $lastSize = $connection->logFileInfo['last_size'];
                    clearstatcache();
                    $newSize = filesize(stream_get_meta_data($fileStream)['uri']);
                    $addSize = $newSize - $lastSize;
                    if ($addSize > 0) {
                        fseek($fileStream, $lastSize);
                        $connection->send(fread($fileStream, $addSize));
                        $connection->logFileInfo['last_size'] = $newSize;
                    }
                }
            });

        };
        // 关闭队列进程
        $worker->onWorkerStop = function ($worker) {
            error_reporting(0);
            ini_set('display_errors', 1);
            Timer::delAll();
        };

        $worker->onMessage = function ($connection, $data) use ($worker) {
            error_reporting(0);
            ini_set('display_errors', 1);

            $logFileName = storage_path('logs/' . $data);

            $logFileStream = fopen($logFileName, "r");
            $connection->logFileInfo = [
                'file_stream' => $logFileStream,
                'last_size' => 0,
            ];
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
				if (($hostManager->restartTime() > $this->startTime) && ($this->startTime + 60 < $hostManager->restartTime())) {
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

    private function start()
    {
        // 记录自身host
        (new HostManager())->localhost()->register();
        $this->startTime = time();
        $this->daemonKeeper();
        $this->queueKeeper();
        $this->cronKeeper();
//        if(function_exists('stream_socket_server')){
//            $this->logReader();
//        }
    }
}
