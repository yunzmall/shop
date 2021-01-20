<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/3
 * Time: 17:40
 */

namespace app\common\services\notice;


abstract class BaseMessageBody
{
    public $data=[];

    //组装数据
   abstract public function organizeData();

    //发放数据
   abstract public function sendMessage();
}