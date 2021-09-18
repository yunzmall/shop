<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/19
 * Time: 15:14
 */

namespace app\backend\modules\order\services\row;


class RowTemplate extends RowBase
{
    public function enable()
    {
       return true;
    }

    public function getContent()
    {
       return ['aad','测试'];
    }
}