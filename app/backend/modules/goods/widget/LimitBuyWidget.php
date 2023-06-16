<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/9
 * Time: 17:39
 */

namespace app\backend\modules\goods\widget;

use app\common\models\goods\GoodsLimitBuy;

/**
 * 限时购(非插件)
 */
class LimitBuyWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'limitbuy';

    public $code = 'buyLimit';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $goods_id = $this->goods->id;
        $goodsLimitBuy = GoodsLimitBuy::getDataByGoodsId($goods_id);
        $starttime = strtotime('-1 month');
        $endtime = time();

        if ($goodsLimitBuy) {
            $goodsLimitBuy = $goodsLimitBuy->toArray();
            $starttime = $goodsLimitBuy['start_time'];
            $endtime = $goodsLimitBuy['end_time'];
        } else {
            $goodsLimitBuy = [
                'status' => 0, //是否开启 0否 1.是
                'display_name' => '限时购', //自定义前端显示名称
            ];
        }

        $data = [
                'data'  =>  $goodsLimitBuy,
                'starttime' => $starttime, //限时购开始时间
                'endtime' => $endtime,     //结束时间
        ];
        
        return $data;
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}