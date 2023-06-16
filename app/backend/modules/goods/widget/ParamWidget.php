<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/9/10
 * Time: 14:10
 */

namespace app\backend\modules\goods\widget;


class ParamWidget extends BaseGoodsWidget
{
    public $group = 'base';

    public $widget_key = 'param';

    public $code = 'param';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {


        if (is_null($this->goods)) {
            return [];
        }
        return $this->goods->hasManyParams?$this->goods->hasManyParams->toArray():[];
    }


    public function pagePath()
    {
        return  $this->getPath('resources/views/goods/assets/js/components/');
    }
}