<?php

namespace app\framework\Bus;

use app\process\QueueKeeper;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Queue\RedisQueue;
use RuntimeException;


class Dispatcher extends \Illuminate\Bus\Dispatcher
{
    private $redisQueues = [];

    private $index;

    private $p = [];


    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param mixed $command
     * @return mixed
     */
    public function dispatch($command)
    {
        if ($this->queueResolver && $this->commandShouldBeQueued($command)) {
            // 如果没有开启商城守护进程,则不为队列分组
            if (!QueueKeeper::isAlive()) {
                $command->queue = null;
            }
            return $this->dispatchToQueue($command);
        } else {
            return $this->dispatchNow($command);
        }
    }

    /**
     * redis队列等待数据提交后
     * @param mixed $command
     * @return mixed
     */
    public function dispatchToQueue($command)
    {
        $connection = isset($command->connection) ? $command->connection : null;

        $queue = call_user_func($this->queueResolver, $connection);

        if (!$queue instanceof Queue) {
            throw new RuntimeException('Queue resolver did not return a Queue implementation.');
        }

        if (method_exists($command, 'queue')) {
            return $command->queue($queue, $command);
        } else {
            // 当队列任务驱动为redis，并且包含在数据库事务中时，保存redis队列任务，等待事务先提交
            if ($queue instanceof RedisQueue && app('db.connection')->transactionLevel() > 0) {
                $this->addRedisQueue($queue, $command, app('db.connection')->transactionLevel());
			} else {
            	return $this->pushCommandToQueue($queue, $command);
            }
        }

    }
    public function getRedis()
	{
		return $this->redisQueues;
	}

    private function addRedisQueue($queue, $command, $level)
    {
		//存入当前指针
        $this->redisQueues[end($this->p)][] = [$queue, $command];
    }


	public function dbTransactionBeginning(TransactionBeginning $event)
	{
		//指针
		$level = $event->connection->transactionLevel();
		if (!empty($this->p)) {
			$now = end($this->p) . '-' . $level;
			$count = array_count_values($this->index);
			$next_level = $count[$level] + 1;
			$now .= '('.$next_level.')';
		} else {
			$now = (string)$level;
		}

		$this->index[] = $event->connection->transactionLevel();
		$this->p[] = $now;
		end($this->p);
	}





    public function dbTransactionCommitted(TransactionCommitted $event)
    {
		//指针前移
		array_pop($this->p);
        // mysql事务提交后，推送redis队列任务，判断是否level是否为0
		if ($event->connection->transactionLevel() == 0) {
			$this->pushRedisQueues();
		}
    }

    public function dbTransactionRollBack(TransactionRolledBack $event)
    {
    	//指针前移
		$p = array_pop($this->p);
		if (!isset($this->redisQueues)) {
            return;
        }

		$p = addcslashes($p,"-()");
		foreach ($this->redisQueues as $key=>$value) {
        	if (preg_match("/$p(.*)/",$key,$match)) {
        		unset($this->redisQueues[$key]);
			};
		}
    }

    public function pushRedisQueues()
    {
        if (empty($this->redisQueues)) {
            return;
        }
        foreach ($this->redisQueues as $redisQueueLevel) {
			foreach ($redisQueueLevel as $redisQueue) {
				list($queue, $command) = $redisQueue;
				$this->pushCommandToQueue($queue, $command);
			}
        }
        unset($this->redisQueues);
    }

}