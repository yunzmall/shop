<?php

namespace app\framework\Bus;

use app\process\QueueKeeper;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Queue\RedisQueue;
use RuntimeException;


class Dispatcher extends \Illuminate\Bus\Dispatcher
{
    private $redisQueues = [];

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
        $this->redisQueues[$level][] = [$queue, $command];
    }

    public function dbTransactionCommitted(TransactionCommitted $event)
    {
        // mysql事务提交后，推送redis队列任务，判断是否level是否为0
		if ($event->connection->transactionLevel() == 0) {
			$this->pushRedisQueues();
		}
    }

    public function dbTransactionRollBack(TransactionRolledBack $event)
    {
		if (!isset($this->redisQueues)) {
            return;
        }
        $level = $event->connection->transactionLevel();
        if (!isset($this->redisQueues[$level + 1])) {
            return;
        }
        \Log::error('取消队列',$event);

        unset($this->redisQueues[$level + 1]);
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