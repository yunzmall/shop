<?php


namespace app\process;


use Illuminate\Support\Facades\Redis;

class QueueManager
{
    public function runningJobs()
    {
        $result = [];
        $runningQueueJobs = Redis::hgetall('RunningQueueJobs');
        foreach ($runningQueueJobs as $key => $queueJob) {
            $queueJob = json_decode($queueJob);
            $queueJob->data->command = unserialize($queueJob->data->command);
            $result[$key] = $queueJob;
        }
        return $result;
    }
    public function jobCounts(){
        $result = [];
        $keys = Redis::keys('queues:*');
        foreach ($keys as $key) {
            if (strpos($key, 'reserved')||strpos($key, 'delayed')) {
                continue;
            }
            $result[$key] = Redis::llen($key);
        }
        return $result;
    }
}