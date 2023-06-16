<?php
namespace app\backend\modules\goods\listeners;
use app\common\models\goods\GoodsLimitBuy;
use app\common\models\UniAccount;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2018/4/10 0010
 * Time: 下午 4:12
 */
class LimitBuy
{
    use DispatchesJobs;

    public function handle()
    {
        $uniAccount = UniAccount::getEnable() ?: [];
        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;
            GoodsLimitBuy::uniacid()->select(['yz_goods.id','yz_goods_limitbuy.end_time','yz_goods.status'])
                ->join('yz_goods', 'yz_goods_limitbuy.goods_id', 'yz_goods.id')
                ->where('yz_goods.status', 1)->where('yz_goods_limitbuy.status', 1)
                ->where("yz_goods_limitbuy.end_time", '<', time())
                ->update(['yz_goods.status'=>0]);
        }
    }

    public function subscribe()
    {
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('Limit-buy', '*/10 * * * *', function() {
                $this->handle();
                return;
            });
        });
    }
}