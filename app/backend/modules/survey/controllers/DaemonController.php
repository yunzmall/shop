<?php


namespace app\backend\modules\survey\controllers;


use app\common\components\BaseController;
use app\host\Host;
use app\host\HostManager;
use app\process\CronManager;
use app\process\QueueManager;
use Illuminate\Support\Facades\Redis;

class DaemonController extends BaseController
{
    public function index()
    {
        $hostManager = new HostManager();
        $result['pids'] = $hostManager->show();
        $result['hosts'] = $hostManager->hostnames();
        $cronManager = new CronManager();
        $result['running_cron_jobs'] = $cronManager->runningJobs();
        $result['cron_running'] = Redis::get('CronRunning');
        $queueManager = new QueueManager();
        $result['running_queue_jobs'] = $queueManager->runningJobs();
        $result['queue_jobs'] = $queueManager->jobCounts();
        return $this->successJson('', $result);
    }

    public function start()
    {
        app('supervisor')->startAllProcesses();
        return $this->successJson();
    }

    public function restart()
    {
        $hostManager = new HostManager();
        $hostManager->restart();
        return $this->successJson();
    }
}
