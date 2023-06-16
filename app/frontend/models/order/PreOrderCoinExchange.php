<?php

namespace app\frontend\models\order;


use app\common\models\order\OrderCoinExchange;

class PreOrderCoinExchange extends OrderCoinExchange
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->appends = array_merge(['show_style'],$this->appends);
    }

    /**
     * 前端显示样式:1左右布局（积分全额抵扣    xxx），0文字叙述（例：177.00积分a全额 抵扣66.02元）
     * @return int
     */
    public function getShowStyleAttribute()
    {
        if (in_array($this->code,['point'])) {
            return 1;
        }
        return 0;
    }
}