<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:10
 */

namespace app\backend\modules\refund\services\button;


use app\common\models\refund\RefundApply;
use app\frontend\modules\order\operations\OrderOperationInterface;

abstract class RefundButtonBase implements OrderOperationInterface
{

    protected $refund;

    /**
     * @param RefundApply $refundApply
     */
    public function __construct(RefundApply $refund)
    {
        $this->refund = $refund;
    }

    /**
     * @return string
     */
    abstract function getDesc();
}