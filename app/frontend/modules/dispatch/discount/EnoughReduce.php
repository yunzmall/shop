<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 上午11:17
 */

namespace app\frontend\modules\dispatch\discount;

use app\common\facades\Setting;

/**
 * 全场运费满额减
 * Class EnoughReduce
 * @package app\frontend\modules\dispatch\discount
 */
class EnoughReduce extends BaseFreightDiscount
{
    protected $name = '全场运费满额减';
    protected $code = 'freightEnoughReduce';


    /**
     * @return int|number
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {

        // 不参与包邮地区
        if (in_array($this->order->orderAddress->city_id, Setting::get('enoughReduce.freeFreight.city_ids'))) {
            trace_log()->freight('全场运费满额减',"{$this->order->orderAddress->city_id}地区不参加");
            return 0;
        }
        // 设置为0 全额包邮
        if (Setting::get('enoughReduce.freeFreight.enough') === 0 || Setting::get('enoughReduce.freeFreight.enough') === '0') {
            trace_log()->freight('全场运费满额减',"全额包邮");
            return $this->orderFreight->getPriceBefore($this->getCode());
        }

        // 订单金额满足满减金额（订单抵扣后价格）
        if (Setting::get('enoughReduce.freeFreight.amount_type') == 1) {
            trace_log()->freight('全场运费满额减','订单抵扣后价格计算');
            return 0;
        }
        // 订单金额满足满减金额（订单抵扣后价格）
//        if (Setting::get('enoughReduce.freeFreight.amount_type') == 1) {
//            if (($this->order->getPriceBefore('orderFee') - $this->order->getDispatchAmount()) >= Setting::get('enoughReduce.freeFreight.enough')) {
//                trace_log()->freight('全场运费满额减',"订单金额{$this->order->getPriceBefore('orderFee')}满足".Setting::get('enoughReduce.freeFreight.enough'));
//                return $this->orderFreight->getPriceBefore($this->getCode());
//            }
//            trace_log()->freight('全场运费满额减','订单抵扣后价格计算');
//            return 0;
//        }

        // 订单金额满足满减金额（商品现价）
        if ($this->order->getPriceBefore('orderDispatchPrice') >= Setting::get('enoughReduce.freeFreight.enough')) {
            trace_log()->freight('全场运费满额减',"订单金额{$this->order->getPriceBefore('orderDispatchPrice')}满足".Setting::get('enoughReduce.freeFreight.enough'));
            return $this->orderFreight->getPriceBefore($this->getCode());
        }
        trace_log()->freight('全场运费满额减',"订单金额{$this->order->getPriceBefore('orderDispatchPrice')}不满足".Setting::get('enoughReduce.freeFreight.enough'));
        return 0;
    }

    public function validate()
    {
        if (!Setting::get('enoughReduce.freeFreight.open')) {
            trace_log()->freight('全场运费满额减','设置未开启');
            return false;
        }

        //只有商城、应用市场、应用市场-子平台
        if(!in_array($this->order->plugin_id,[0,57,59])){
            trace_log()->freight('全场运费满额减','只有商城,应用市场,应用市场-子平台订单参加');
            return false;
        }

        return parent::validate();
    }
}