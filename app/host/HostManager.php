<?php


namespace app\host;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class HostManager
{
    /**
     * @return Host
     */
    public function localhost()
    {
        return new Host(gethostname());
    }

    /**
     * @return mixed
     */
    public function hostnames()
    {
        $hostnames = Redis::hkeys('hosts');

        foreach ($hostnames as $key => $hostname) {
            if (!Redis::hkeys('forkPids:' . $hostname)) {
                unset($hostnames[$key]);
            }
        }
        return $hostnames;
    }

    public function hosts()
    {
        $hosts = [];
        foreach ($this->hostnames() as $hostname) {
            $hosts[] = new Host($hostname);
        }
        return $hosts;
    }

    public function pidkeys()
    {
        $result = [];
        foreach ($this->hosts() as $host) {
            /**
             * @var Host $host
             */
            $result = array_merge($result, $host->pidKeys());
        }
        return $result;
    }

    public function pidKey()
	{
		return $this->localhost()->pidKeys();
	}

    public function show()
    {
        $result = new Collection();
        foreach ($this->hosts() as $host) {
            /**
             * @var Host $host
             */
            $result[$host->hostName] = $host->show();

        }
        return $result;
    }

    public function command($command)
    {
        foreach ($this->hosts() as $host) {
            /**
             * @var Host $host
             */
            Redis::lpush('daemon:commands:' . $host->hostName, $command);
        }
    }
    public function logout($hostname){
        Redis::hdel('hosts', $hostname);
    }
    public function restartTime(){
        return Redis::get('daemonRestart');
    }
    public function restart(){
        return Redis::set('daemonRestart',time());

    }
    public function refresh(){
        $this->localhost()->clearPids();
        $this->localhost()->killAll();
    }
}