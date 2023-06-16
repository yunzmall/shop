<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/27 下午4:49
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\backend\modules\frame\controllers;


use app\common\components\BaseController;

class IconController extends BaseController
{
    public function index()
    {
        $callback = \YunShop::request()->callback;

        return view('frame.icon', ['callback' => $callback])->render();
    }
}
