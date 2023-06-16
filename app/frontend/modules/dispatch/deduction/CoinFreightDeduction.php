<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 17:53
 */

namespace app\frontend\modules\dispatch\deduction;


class CoinFreightDeduction extends BaseFreightDeduction
{
    /**
     * 抵扣运费金额
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function _getAmount()
    {

        if ($this->orderDeduction->openFreightDeduction()) {

            return max($this->orderDeduction->getUsableFreightDeduction()->getMoney(),0);
        }

        return 0;
    }
}