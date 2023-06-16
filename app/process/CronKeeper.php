<?php


namespace app\process;


use app\framework\Log\SimpleLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Liebig\Cron\Cron;
use function foo\func;

class CronKeeper
{
    use Fork;


    public function main()
    {

//        app()->share(function ($app) {
//            return new Cron;
//        });
        app()->singleton('cron',function () {
            return new Cron;
        });
    }

    public function run()
    {
		//改为redis锁，防止集群极端并发
		if (!Redis::setnx('CronRunning', gethostname() . '[' . getmypid() . ']')) {
			// 60秒内运行过了
			// 验证CronRunning的过期时间
			$ttl = Redis::TTL('CronRunning');
			if ($ttl == -1 || $ttl > 59) {
				Redis::del('CronRunning');
			}
			return true;
		}
		Redis::expire('CronRunning', 59);
        $this->cProcess(function (){
            \Artisan::call("cron:run");
        },'cron');

    }

}