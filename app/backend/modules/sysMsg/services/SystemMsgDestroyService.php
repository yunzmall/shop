<?php

namespace app\backend\modules\sysMsg\services;

use app\common\facades\Setting;
use app\common\models\systemMsg\SysMsgLog;
use app\common\models\UniAccount;
use Illuminate\Support\Carbon;

class SystemMsgDestroyService
{
    public function subscribe()
    {
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('SystemMsgDestroy', '0 1 * * *', function () {//每天1点删
                $this->handle();
                return;
            });
        });
    }

    public function handle()
    {
        try {
            set_time_limit(0);
            $uniAccount = UniAccount::get() ?: [];
            $time = Carbon::now()->subMonths(3)->startOfDay()->timestamp;
            foreach ($uniAccount as $u) {
                Setting::$uniqueAccountId = \YunShop::app()->uniacid = $u->uniacid;
                SysMsgLog::uniacid()->where('created_at','<',$time)->delete();
            }
        } catch (\Exception $e) {
            \Log::debug('-----定时删除系统消息错误------',[$e->getMessage()]);
        }
    }
}