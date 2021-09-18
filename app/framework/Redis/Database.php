<?php


namespace app\framework\Redis;


use Closure;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;


class Database extends RedisManager
{
    
    public function refresh()
    {
        app('redis')->disconnect();
        app('cache')->store('redis')->connection()->disconnect();
        app()->forgetInstance('redis');
        Redis::clearResolvedInstance('redis');
    }

    public function lock($key, $identification, $expireTime = 60)
    {
        while (true) {
            $lock = $this->get($key);
            if (isset($lock) and $lock != $identification) {
                sleep(0.1);
            } else {
                break;
            }
        }
        return $this->setex($key, $expireTime, $identification);
    }

    public function unlock($key)
    {
        return $this->del($key);
    }
}