<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 03/03/2017
 * Time: 12:19
 */

namespace app\backend\widgets;


use app\common\components\Widget;

class MenuWidget extends Widget
{
    public $test = '';

    public function run()
    {
        $menu = \app\backend\modules\menu\Menu::current()->getItems();
        return view('widgets.menu',['menu'=>$menu]);
    }
}