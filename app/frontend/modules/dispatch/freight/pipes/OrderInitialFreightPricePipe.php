<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 10:18
 */

namespace app\frontend\modules\dispatch\freight\pipes;


class OrderInitialFreightPricePipe extends PricePipe
{
    function getKey()
    {
        return 'initialFreight';
    }

    public function getAmount()
    {
        // TODO: Implement getAmount() method.
    }

    /**
     * @return mixed
     */
    function getPrice()
    {
        return $this->orderFreight->getInitialFreightAmount();
    }
}