<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/22
 * Time: 13:57
 */

namespace app\frontend\modules\finance\models;

use app\common\models\VirtualCoin;

class BalanceCoin  extends VirtualCoin
{
    protected function _getExchangeRate()
    {
        return false;
    }

    protected function _getName()
    {
        $credit = trim(\Setting::get('shop.shop.credit'));

        return $credit ? $credit : '余额';
        // return \Setting::get('shop.shop.credit1','积分');
    }

    protected function _getCode()
    {
        return 'balance';
    }
}