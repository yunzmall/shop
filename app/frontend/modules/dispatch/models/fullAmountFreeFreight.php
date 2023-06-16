<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/5/16
 * Time: 17:47
 */

namespace app\frontend\modules\dispatch\models;

use app\common\facades\Setting;
use app\frontend\modules\order\discount\BaseDiscount;

class fullAmountFreeFreight  extends BaseDiscount
{
    protected $name = '全场运费满额减';
    protected $code = 'freightEnoughReduce';

    public $amount;

    /**
     * 获取总金额
     * @return float
     */
    public function getAmount()
    {
        if (isset($this->amount)) {
            return $this->amount;
        }

        $this->amount = $this->_getAmount();

        return $this->amount;
    }

    /**
     * @return int|number
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {

        if (!Setting::get('enoughReduce.freeFreight.open')) {
            trace_log()->freight('全场运费满额减','设置未开启');
            return 0;
        }


        //只有商城、应用市场、应用市场-子平台
        if(!in_array($this->order->plugin_id,[0,57,59])){
            trace_log()->freight('全场运费满额减','只有商城,应用市场,应用市场-子平台订单参加');
            return 0;
        }


        // 不参与包邮地区
        if (in_array($this->order->orderAddress->city_id, Setting::get('enoughReduce.freeFreight.city_ids'))) {
            trace_log()->freight('全场运费满额减',"{$this->order->orderAddress->city_id}地区不参加");
            return 0;
        }
        // 订单金额满足满减金额（订单抵扣后价格）
        if (Setting::get('enoughReduce.freeFreight.amount_type') == 1) {
            if (($this->order->getPriceBefore('orderFee') - $this->order->getDispatchAmount()) >= Setting::get('enoughReduce.freeFreight.enough')) {
                trace_log()->freight('全场运费满额减',"订单金额{$this->order->getPriceBefore('orderFee')}满足".Setting::get('enoughReduce.freeFreight.enough'));
                return $this->order->getDispatchAmount();
            }
            trace_log()->freight('全场运费满额减','订单抵扣后价格计算');
            return 0;
        }

        return 0;
    }

}