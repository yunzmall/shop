<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 下午3:55
 */

namespace app\frontend\modules\order\discount;

use app\common\facades\Setting;

class EnoughReduce extends BaseDiscount
{
    protected $code = 'enoughReduce';
    protected $name = '全场满减优惠';

    /**
     * 获取总金额
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {

        if(!Setting::get('enoughReduce.open')){
            return 0;
        }

        //只有商城、益生插件、应用市场、应用市场-子平台
        if(!in_array($this->order->plugin_id,[0,61,57,59])){
            return 0;
        }


        // 获取满减设置,按enough倒序
        $settings = collect(Setting::get('enoughReduce.enoughReduce'));

        if (empty($settings)) {
            return 0;
        }

        $settings = $settings->sortByDesc(function ($setting) {
            return $setting['enough'];
        });

        // 订单总价满足金额,则返回优惠金额
        foreach ($settings as $setting) {

            if ($this->order->getPriceBefore($this->getCode()) >= $setting['enough']) {
                return min($setting['reduce'],$this->order->getPriceBefore($this->getCode()));
            }
        }
        return 0;
    }
}