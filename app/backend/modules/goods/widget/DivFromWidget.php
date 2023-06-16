<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/14
 * Time: 17:45
 */

namespace app\backend\modules\goods\widget;
use app\backend\modules\goods\models\DivFrom;

//表单
class DivFromWidget extends BaseGoodsWidget
{
    public $group = 'tool';

    public $widget_key = 'div_from';

    public $code = 'forms';

    public function pluginFileName()
    {
        return 'goods';
    }

    public function getData()
    {
        $div_from = DivFrom::ofGoodsId($this->goods->id)->first();

        return $div_from?$div_from:["status"=>0];
    }


    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}