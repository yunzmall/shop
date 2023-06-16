<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/14
 * Time: 11:17
 */

namespace app\backend\modules\goods\widget\manage;

use app\backend\modules\goods\widget\manage\GoodsWidgetManager;
use Illuminate\Container\Container;

class GoodsWidgetContainer  extends Container
{

    private $widget_setting;

    public function __construct()
    {
        $this->bindModels();

        $this->widget_setting = \app\common\modules\widget\Widget::current()->getItem('vue-goods');
    }

    public function getSetting()
    {
        return $this->widget_setting;
    }

    private function bindModels()
    {

        $this->bind('Manager', function ($GoodsWidgetManager, $parameter) {
            return new GoodsWidgetManager($parameter);
        });


    }
}