<?php


namespace app\process;


use Illuminate\Support\Facades\Redis;

class CronManager
{
    public function runningJobs(){
        return Redis::hgetall('RunningCronJobs');
    }
}