<?php


namespace app\process;


use app\framework\Log\SimpleLog;
use Illuminate\Support\Facades\Redis;
use Liebig\Cron\Cron;

class CronKeeper
{
    use Fork;


    public function main()
    {

        app()->share(function ($app) {
            return new Cron;
        });
    }

    public function run()
    {
        if (Redis::get('CronRunning')) {
            // 60秒内运行过了
            return true;
        }
        Redis::setex('CronRunning', 59, gethostname() . '[' . getmypid() . ']');
        $this->cProcess(function (){
            \Artisan::call("cron:run");
        },'cron');

    }

}