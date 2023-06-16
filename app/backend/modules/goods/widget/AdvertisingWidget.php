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

use app\common\models\goods\GoodsAdvertising;

/**
 * 广告宣传语(非插件)
 */
class AdvertisingWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'advertising';

    public $code = 'advertising';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $goods_id = $this->goods->id;
        $data = GoodsAdvertising::getGoodsData($goods_id);

        return $data;
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}