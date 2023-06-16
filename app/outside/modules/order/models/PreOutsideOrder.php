<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/13
 * Time: 15:18
 */

namespace app\outside\modules\order\models;


use app\common\exceptions\ApiException;
use app\frontend\modules\order\models\PreOrder;
use app\common\modules\order\OrderCollection;
use app\frontend\modules\order\services\OrderService;
use app\outside\modes\OutsideOrder;

/**
 * Class PreOutsideOrder
 * @property OrderCollection orders
 * @package app\outside\modules\order\models
 */
class PreOutsideOrder extends OutsideOrder
{
    /**
     * @var OrderCollection
     */
    public $orders;

    public function setOrders(OrderCollection $orders)
    {
        $this->orders = $orders;

        $this->initAttributes();
    }

    protected function initAttributes()
    {
        $this->fill([
            'uniacid' => \YunShop::app()->uniacid,
            'total_price' => $this->orders->sum('price'),
            'outside_sn' => request()->input('outside_sn'),
            'order_ids' => $this->orders->pluck('id'),
            'trade_sn' => self::createSn(),
        ]);
    }

    /**
     * @throws ApiException
     */
    public function store()
    {
        $this->save();
        if ($this->id === null) {
            throw new ApiException('第三方订单请求记录保存失败');

        }
        $this->orders()->attach($this->order_ids);

    }
}