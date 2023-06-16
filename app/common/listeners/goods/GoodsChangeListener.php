<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/6/21 上午9:49
 * Email: livsyitian@163.com
 */

namespace app\common\listeners\goods;

use app\common\events\goods\GoodsChangeEvent;
use app\common\events\goods\GoodsCreateEvent;
use app\common\events\goods\GoodsLimitBuyCloseEvent;
use app\common\events\goods\GoodsOptionChangeEvent;
use app\common\events\goods\GoodsStockNotEnoughEvent;
use app\common\models\Goods;
use app\common\models\goods\GoodsLimitBuy;
use app\common\services\SystemMsgService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GoodsChangeListener
{
    use DispatchesJobs;

    public function subscribe(Dispatcher $events)
    {
        $events->listen(GoodsLimitBuyCloseEvent::class, static::class . "@limitBuyClose" ); //限时购下架
        $events->listen(GoodsStockNotEnoughEvent::class, static::class . "@stockNotEnough" ); //库存为0

        /*
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('goods_min_price_time_task', '0 3 * * *', function() {
                $this->handleGoodsMinPrice();//有很多商品未设置到min_price，先运行一段时间再说
            });
        });
        */
    }


    public function limitBuyClose(GoodsLimitBuyCloseEvent $event)
    {
        $goods = $event->getGoods();
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $goods->uniacid;
        $data = GoodsLimitBuy::getDataByGoodsId($goods->id);
        if(!empty($data) && $data['status'] == '1' && time() >= $data['end_time'])
        {
            (new SystemMsgService())->limitBuyClose($goods);
        }
    }

    public function stockNotEnough(GoodsStockNotEnoughEvent $event)
    {
        $goods = $event->getGoods();
        $specs = $event->getSpecs();
        (new SystemMsgService())->stockNotEnough($goods,$specs);
    }

    public function handleGoodsMinPrice()
    {
        $uniAccount = \app\common\models\UniAccount::getEnable() ?: [];
        foreach ($uniAccount as $u) {
            \app\common\facades\Setting::$uniqueAccountId = $u->uniacid;
            \YunShop::app()->uniacid = $u->uniacid;
            \app\common\models\Goods::uniacid()->select('id')
                ->whereNull('min_price')
                ->chunk('500',function ($goods) use ($u) {
                    $ids = $goods->pluck('id')->all();
                    if ($ids) {
                        dispatch(new \app\Jobs\SetGoodsPriceJob($ids,$u->uniacid));
                    }
                });
        }
    }
}
