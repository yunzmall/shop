<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/27
 * Time: 16:39
 */

namespace app\frontend\modules\payment\paymentSettings\shop;


class ConfirmPaySetting extends BaseSetting
{
    public function canUse()
    {

        return true;
    }

    public function exist()
    {

        return true;
    }
}