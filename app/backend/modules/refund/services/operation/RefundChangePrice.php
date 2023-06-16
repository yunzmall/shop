<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/22
 * Time: 15:35
 */

namespace app\backend\modules\refund\services\operation;

use app\common\events\order\BeforeOrderRefundChangePriceEvent;
use app\common\exceptions\AppException;
use app\common\models\refund\RefundChangeLog;

/**
 * 修改退款金额
 * Class RefundChangePrice
 * @package app\backend\modules\refund\services\operation
 */
class RefundChangePrice extends RefundOperation
{
//    protected $statusBeforeChange = [self::WAIT_CHECK];
    protected $name = '修改退款金额';

    protected $oldPrice;


    protected function updateAfter()
    {

    }

    protected function updateBefore()
    {

        //兼容旧数据
        if (empty($this->apply_price)) {
            $this->apply_price =  max($this->price - $this->freight_price - $this->other_price,0);
        }

        $changePriceData = $this->getRequest()->only(['change_price', 'change_freight_price', 'change_other_price']);

        event(new BeforeOrderRefundChangePriceEvent($this,$changePriceData));

        //旧金额
        $this->oldPrice = $this->price;

        if (isset($changePriceData['change_freight_price'])) {
            $this->freight_price = max($changePriceData['change_freight_price'],0);
        }

        if (isset($changePriceData['change_other_price'])) {
            $this->other_price = max($changePriceData['change_other_price'],0);
        }

        if (isset($changePriceData['change_price'])) {
            $this->apply_price = $this->apply_price + $changePriceData['change_price'];
        }

        $new_price =  max($this->apply_price + $this->freight_price + $this->other_price,0);

        if(bccomp($new_price, 0,2) != 1) {
            throw new AppException('退款金额必须大于0！');
        }

        $this->price = min($this->order->price, $new_price);
    }

    protected function writeLog()
    {
        $data = [
            'old_price' => $this->oldPrice,
            'new_price' => $this->price,
            'change_price' => $this->getRequest()->input('change_price')?:0,
            'change_freight_price' => $this->getRequest()->input('change_freight_price')?:0,
            'change_other_price' => $this->getRequest()->input('change_other_price')?:0,
            'username' => \Yunshop::app()->username?:'其他',
            'refund_id' => $this->id,
            'order_id' => $this->order_id,
        ];
        RefundChangeLog::create($data);
    }
}