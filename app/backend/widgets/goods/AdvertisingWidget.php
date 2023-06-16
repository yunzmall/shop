<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/8/11
 * Time: 10:39
 */

namespace app\backend\widgets\goods;

use app\common\components\Widget;
use app\common\models\goods\GoodsAdvertising;

class AdvertisingWidget extends Widget
{
    public function run()
    {
        $goods_id = \YunShop::request()->id;
        $data = GoodsAdvertising::getGoodsData($goods_id);

        return view('goods.widgets.advertising', [
            'data' => $data
        ])->render();
    }
}