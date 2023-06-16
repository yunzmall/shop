<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/21
 * Time: 下午4:34
 */

namespace app\backend\modules\refund\models\type;

use app\backend\modules\refund\models\RefundApply;

class ReturnGoods extends RefundType
{
    /**
     * 同意退货 保存退货地址 1
     * @return bool
     * @throws \app\common\exceptions\AdminException
     */
    public function pass()
    {
        $this->validate([RefundApply::WAIT_CHECK],'通过');


        $bool = $this->updateSave([
            'operate_time' => time(),
            'status' => RefundApply::WAIT_RETURN_GOODS,
            'remark' => $this->refundApply->getRequest()->input('message'),
            'refund_address' => $this->refundApply->getRequest()->input('refund_address'),
        ]);

        return $bool;
    }

    public function receiveReturnGoods()
    {
        //$this->validate([RefundApply::WAIT_RECEIVE_RETURN_GOODS,],'收货');

        $this->refundApply->status = RefundApply::WAIT_REFUND;
        return $this->refundApply->save();
    }
    //public function
}