<?php

namespace app\process;


use app\common\exceptions\ShopException;
use app\common\facades\SiteSetting;
use app\common\modules\shop\ShopConfig;
use app\common\services\SystemMsgService;
use app\framework\Log\SimpleLog;
use app\host\HostManager;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Redis;

class QueueKeeper
{
    use Fork;

    private $config;
    private $pid;
    /**
     * @var SimpleLog
     */
    private $log;

    private function config()
    {
        $result = ShopConfig::current()->getItem('queue');
        $queueSetting = SiteSetting::get('queue');
        foreach ($result as &$item) {
            if (isset($queueSetting[$item['key']]) && $queueSetting[$item['key']]) {
                $item['total'] = $queueSetting[$item['key']];
            } else {
                $item['total'] = count(app('supervisor')->getHostname()) * $item['total'];
            }
        }
        if ($this->config != $result) {
			$this->log->add('config changed', [$this->config, $result,count(app('supervisor')->getHostname())]);
			$this->config = $result;
        }
        $this->config = $result;
        return $result;
    }

    public function main()
    {
        $this->pid = getmypid();
        $this->log = new SimpleLog('queueKeeper');
        $this->stopQueues();
        $this->log->add("{$this->pidKey}:stopping",['初始化']);
        /**
         * @var QueueManager $queue
         */
        $queue = app('queue');
        $queue->looping(function () {
            // 执行任务之前，检查自己是否存在于redis的pid列表中，检查keeper是否存活
            if ((new HostManager())->localhost()->pid($this->pidKey) != getmypid() || !posix_kill($this->pid, 0)) {
                die;
            }
        });
        $queue->before(function (JobProcessing $event) {
            Redis::hset('RunningQueueJobs',$this->pidKey,$event->job->getRawBody());
            $dataStr = $event->job->getRawBody();
            $data = json_decode($dataStr);
            $this->log->add("{$this->pidKey}:{$data->id}:{$data->data->commandName}:begin", [$event->job->getRawBody()]);

        });
        $queue->after(function (JobProcessed $event) {
            Redis::hdel('RunningQueueJobs',$this->pidKey);
            $dataStr = $event->job->getRawBody();
            $data = json_decode($dataStr);
            $this->log->add("{$this->pidKey}:{$data->id}:{$data->data->commandName}:end");
        });
        $queue->exceptionOccurred(function (JobExceptionOccurred $event) {
            Redis::hdel('RunningQueueJobs',$this->pidKey);
            $dataStr = $event->job->getRawBody();
            $data = json_decode($dataStr);
			if (!($event->exception instanceof ShopException)) {
				SystemMsgService::addWorkMessage(['title'=>'队列执行错误','content'=>"{$data->data->commandName}:failed"],unserialize($data->data->command)->uniacid);
			}
            $this->log->add("{$this->pidKey}:{$data->id}:{$data->data->commandName}:failed",[$event->job->getRawBody(),$event->exception]);
            \Log::error("队列任务[{$this->pidKey}:{$data->id}:{$data->data->commandName}]运行错误({$this->pidKey})", [$event->job->getRawBody(),$event->exception]);
        });
        $queue->stopping(function () {
            // 队列关闭时清除redis 进程记录
            (new HostManager())->localhost()->clearPid($this->pidKey);
            $this->log->add("{$this->pidKey}:stopping",["内存占用过高:".(memory_get_usage() / 1024 / 1024)]);
        });
        //$this->startQueues();
    }

    public function aliveQueueTotal()
    {
        $aliveTotal = 0;
        $hostManager = new HostManager();
        foreach ($hostManager->pidkeys() as $pidkey) {
            if (strpos($pidkey, 'queues:') !== false) {
                $aliveTotal += 1;
            }
        }
        return $aliveTotal;
    }

    public function aliveQueueLocalTotal()
	{
		$aliveTotal = 0;
		$hostManager = new HostManager();
		foreach ($hostManager->pidkey() as $pidkey) {
			if (strpos($pidkey, 'queues:') !== false) {
				$aliveTotal += 1;
			}
		}
		return $aliveTotal;
	}

    public function keepAlive()
    {
        Redis::setex('queueKeeperAlive', 600, 1);
        $queueTotal = collect($this->config())->sum('total');
        $hostManager = new HostManager();
        // 每个服务器平均队列数
        $avgTotal = $queueTotal / (count(app('supervisor')->getHostname()) ?: 1) ?: 1;

        if ($this->aliveQueueTotal() < $queueTotal && $hostManager->localhost()->numberOfPids() < $avgTotal+1) {
            //
            $i = $avgTotal - $this->aliveQueueLocalTotal();
            while ($this->aliveQueueTotal() < $queueTotal && $i > 0) {
                $i--;
                $this->startQueues();
            }
        } elseif ($this->aliveQueueTotal() > $queueTotal) {
            $this->log->add("{$this->pidKey}:stopping",['已生成队列进程数量大于设置队列总数',$this->aliveQueueTotal(),$queueTotal]);
            // 大于设置数量重新生成
            $hostManager->refresh();
        } else {

            if ($avgTotal < 3) {
                // 太少不需要处理
            } elseif ($hostManager->localhost()->numberOfPids() / $avgTotal > 1.5) {
                $this->log->add("{$this->pidKey}:stopping",['已生成队列进程数大于单机最大数量',$hostManager->localhost()->numberOfPids(),$avgTotal]);

                // 小于平均值1.5倍数,刷新重新分配
                $pidKeys = array_slice($hostManager->localhost()->pidKeys(), $hostManager->localhost()->numberOfPids());
                foreach ($pidKeys as $key => $pid) {
                    $hostManager->localhost()->killProcess($key);
                }
            }
        }

    }

    public function startQueues()
    {
        foreach ($this->config() as $conf) {
            $result = $this->queue($conf['key'], $conf['option'], $conf['total'], $conf['is_serial']);
            if ($result) {
                return true;
            }
        }
    }

    static public function isAlive()
    {
        return Redis::get('queueKeeperAlive');
    }

    static public function stopQueues()
    {
        \Artisan::call("queue:restart");
        Redis::del('RunningQueueJobs');
    }

    public function stop()
    {
        $this->log->add("{$this->pidKey}:stopping",debug_backtrace(2));
        self::stopQueues();
    }

    private function queue($key, $option, $total, $isSerial = false)
    {
        $hostManager = new HostManager();
        $total = SiteSetting::get('queue.' . $key) ?: $total;
        $option = array_merge(["--sleep" => 5,"--tries"=>1], $option);
        for ($i = 0; $i < $total; $i++) {
            $queueProcess = new QueueProcess($key, $total, $i, $option, $isSerial);
            $queueKey = 'queues:' . $queueProcess->queueName . $queueProcess->index;

            // 检查集群所有服务器范围内是否重复
            foreach ($hostManager->pidkeys() as $pidkey) {
                if (strpos($pidkey, $queueKey) !== false) {
                    continue 2;
                }
            }

            $this->log->add($queueKey, $queueProcess->getOption());

            $this->cProcess(function () use ($queueProcess, $hostManager, $queueKey) {
                try {
                    \Artisan::call("queue:work", $queueProcess->getOption());
                } catch (\Exception $exception) {
                    $hostManager->localhost()->clearPid($this->pidKey);
                    throw $exception;
                }
            }, $queueKey);
            return true;
        }
        return false;
    }

}