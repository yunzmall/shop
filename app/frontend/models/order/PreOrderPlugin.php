<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/17
 * Time: 14:17
 */

namespace app\frontend\models\order;


use app\common\models\order\OrderPlugin;
use app\frontend\models\Goods;
use app\frontend\modules\order\models\PreOrder;

class PreOrderPlugin extends OrderPlugin
{
    /**
     * @var PreOrder
     */
    protected $order;

    protected function _initAttributes()
    {
        $attributes = [
            'main_plugin_id' =>  $this->order->plugin_id ? : 0
        ];
        $attributes['sub_plugin_id'] = 0;
        foreach ($this->order->orderGoods as $orderGood) {
            $goods = Goods::select('id','plugin_id')->where('id',$orderGood->goods_id)->first();
            if ($goods) {
                $attributes['sub_plugin_id'] = $goods->plugin_id;
                break;
            }
        }

        $attributes = array_merge($this->getAttributes(), $attributes);
        $this->setRawAttributes($attributes);
    }

    /**
     * @param PreOrder $order
     */
    public function setOrder(PreOrder $order)
    {
        $this->order = $order;

        $this->_initAttributes();

        $this->order->setRelation('orderPlugin', $this);

    }
}