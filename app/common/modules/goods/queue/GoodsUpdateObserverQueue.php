<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/11/18
 * Time: 16:37
 */

namespace app\common\modules\goods\queue;

use app\common\models\Goods;
use app\common\modules\goods\events\GoodsStockChangeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GoodsUpdateObserverQueue implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    private $uniacid;


    protected $goods;

    protected $requestData;

    protected $type;

    public function __construct($uniacid, Goods $goods, $requestData)
    {
        $this->uniacid = $uniacid;
        \YunShop::app()->uniacid = $uniacid;
        \Setting::$uniqueAccountId = $uniacid;

        $this->requestData = $requestData;

        $this->goods = $goods;

    }

    public function handle()
    {
        \YunShop::app()->uniacid = $this->uniacid;
        \Setting::$uniqueAccountId = $this->uniacid;

        \Log::debug(\YunShop::app()->uniacid.'----库存变动--'.$this->goods->id, $this->goods->getDirty());
        \Log::debug('---库存变动------', $this->goods->getAttributes());
        \Log::debug('---库存变动------', $this->requestData);

        //有修改退库存
        $bool = $this->goods->isDirty('stock');

        if ($bool) {
            \Log::debug('----库存变动--stock'.$bool);
        }
        if ($bool) {
            $event = new GoodsStockChangeEvent($this->goods, $this->requestData);
            \Log::debug('----库存变动--触发--'.get_class($event));
            event($event);
        }

    }
}