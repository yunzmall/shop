<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/6/28
 * Time: 17:52
 */

namespace app\common\services\wechat;


use app\common\services\Pay;
use app\payment\PaymentController;

class WechatPayEventNoticeHandle extends PaymentController
{
    protected function init()
    {

    }

    /**
     * @param $data
     * @return array
     */
    public function payNotify($data)
    {

        return $this->payEvent($data);
    }

}