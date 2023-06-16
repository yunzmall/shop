<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/8/24 下午2:35
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\backend\widgets\goods;


use app\backend\modules\goods\models\DivFrom;
use app\common\components\Widget;

class DivFromWidget extends Widget
{
    public function run()
    {
        $div_from = DivFrom::ofGoodsId($this->goods_id)->first() ?: [];

        return view('goods.widgets.div_from', [
            'div_from' => $div_from,
        ])->render();
    }
}