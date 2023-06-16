<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/11
 * Time: 10:08
 */

namespace app\frontend\modules\dispatch\freight;

use app\backend\modules\goods\models\Dispatch;
use app\common\models\goods\GoodsDispatch;
use app\common\modules\orderGoods\OrderGoodsCollection;
use app\frontend\models\OrderGoods;
use app\frontend\modules\orderGoods\models\PreOrderGoods;

class ShopFreight  extends BaseFreight
{
    protected $code = 'shop';

    protected $name = '标准运费';



    public function needDispatch()
    {
        // 虚拟物品不需要配送
        if ($this->order->is_virtual) {
            return false;
        }

        return true;
    }


    protected function _getAmount()
    {
        //去掉重复的OrderGoods
        $uniqueOrderGoods = $this->order->orderGoods->unique('goods_id');


        //这里还需要优化，需要更进一步在订单商品里实现每个商品对应的运费计算返回金额
        $templateFreight = new TemplateFreight($this->order);
        $unifyFreight = new UnifyFreight($this->order);


        return max($templateFreight->getAmount() + $unifyFreight->getAmount(),0);

    }


}