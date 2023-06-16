<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/21
 * Time: 15:47
 */

namespace app\common\modules\goods\queue;


use app\common\models\GoodsOption;
use app\common\modules\goods\events\GoodsOptionStockChangeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GoodsOptionUpdateObserverQueue implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    private $uniacid;


    /**
     * @var GoodsOption
     */
    protected $goodsOption;

    protected $requestData;

    protected $type;

    public function __construct($uniacid, $goodsOption, $requestData)
    {
        $this->uniacid = $uniacid;
        \YunShop::app()->uniacid = $uniacid;
        \Setting::$uniqueAccountId = $uniacid;

        $this->requestData = $requestData;

        $this->goodsOption = $goodsOption;

    }

    public function handle()
    {
        \YunShop::app()->uniacid = $this->uniacid;
        \Setting::$uniqueAccountId = $this->uniacid;

        //有修改退库存
        if ($this->goodsOption->isDirty('stock')) {
            $event = new GoodsOptionStockChangeEvent($this->goodsOption, $this->requestData);
            event($event);
        }

    }
}