<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/22
 * Time: 13:48
 */

namespace app\frontend\modules\finance\deduction;

use app\frontend\modules\deduction\GoodsDeduction;

class BalanceGoodsDeduction extends GoodsDeduction
{
    public function getCode()
    {
        return 'balance';
    }

    public function deductible($goods)
    {
        return true;
    }
}