<?php


namespace app\host;


use app\process\Process;
use Illuminate\Support\Facades\Redis;

class Host
{
    public $hostName;

    public function __construct($hostName)
    {
        $this->hostName = $hostName;
    }

    public function register()
    {
        Redis::hset('hosts', gethostname(),getmypid());
        Redis::expire('hosts',40);
    }

    public function pids()
    {
        return Redis::hvals($this->hashKey());
    }

    public function pidKeys()
    {
        return Redis::hkeys($this->hashKey());
    }

    public function clearPid($key)
    {
        return Redis::hdel($this->hashKey(), $key);

    }

    public function clearPids()
    {
        foreach ($this->map() as $key => $pid) {
            if (!posix_kill($pid, 0)) {
                $this->clearPid($key);
            }
        }
        return true;
    }

    public function killAll()
    {
        foreach ($this->pids() as $pid) {
            posix_kill($pid, SIGKILL);
        }
    }

    private function hashKey()
    {
        return 'forkPids:' . $this->hostName;
    }

    public function numberOfPids()
    {
        return Redis::hlen($this->hashKey());
    }

    public function map()
    {
        return Redis::hgetall($this->hashKey())?:[];
    }
    public function show()
    {
        $result = [];
        $pids = $this->map();
        foreach ($pids as $key=>$pid){
            $result[$key] = new Process($pid);
        }

        return $result;
    }

    public function pid($key)
    {
        return Redis::hget($this->hashKey(), $key);
    }

    public function killProcess($key)
    {
        Redis::hdel($this->hashKey(), $key);
        return posix_kill($this->pid($key), SIGKILL);
    }

    public function processRunning($key)
    {
        if (!$this->pid($key)) {
            return false;
        }
        return posix_kill($this->pid($key), 0);
    }
}