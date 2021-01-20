<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/6/21 上午9:49
 * Email: livsyitian@163.com
 */

namespace app\common\listeners\goods;

use app\common\events\goods\GoodsLimitBuyCloseEvent;
use app\common\events\goods\GoodsStockNotEnoughEvent;
use app\common\models\goods\GoodsLimitBuy;
use app\common\services\SystemMsgService;
use Illuminate\Contracts\Events\Dispatcher;

class GoodsChangeListener
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(GoodsLimitBuyCloseEvent::class, static::class . "@limitBuyClose" ); //限时购下架
        $events->listen(GoodsStockNotEnoughEvent::class, static::class . "@stockNotEnough" ); //库存为0
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

}
