<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/7/25
 * Time: 下午7:10
 */

namespace app\frontend\models\orderGoods;

use app\common\models\orderGoods\OrderGoodsTaxFee;
use app\common\modules\orderGoods\models\PreOrderGoods;

class PreOrderGoodsTaxFee extends OrderGoodsTaxFee
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->appends = array_merge(['show_style'],$this->appends);
    }

    public $orderGoods;

    public function setOrderGoods(PreOrderGoods $orderGoods)
    {
        $this->orderGoods = $orderGoods;
        $this->uid = $this->orderGoods->uid;
        $orderGoods->getOrderGoodsTaxFees()->push($this);
    }

    /**
     * 前端显示样式:1左右布局（积分全额抵扣/兑换    xxx），0文字叙述（例：177.00积分a全额 抵扣66.02元）
     * @return int
     */
    public function getShowStyleAttribute()
    {
        if (in_array($this->discount_code,['coinExchange'])) {
            return 1;
        }
        return 0;
    }
}