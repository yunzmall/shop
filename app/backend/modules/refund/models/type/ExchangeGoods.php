<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/21
 * Time: 下午4:34
 */

namespace app\backend\modules\refund\models\type;

use app\common\models\refund\RefundApply;
use app\common\models\refund\ResendExpress;

class ExchangeGoods extends ReturnGoods
{
    /**
     * 同意换货 商家发货 4
     * @return bool
     */
    public function resend()
    {
        $expressData = $this->refundApply->getRequest()->only('express_code', 'express_company_name', 'express_sn');
        $resendExpress = new ResendExpress($expressData);
        $this->refundApply->resendExpress()->save($resendExpress);

        $bool = $this->updateSave([
            'send_time' => time(),
            'status' => RefundApply::WAIT_RECEIVE_RESEND_GOODS,
        ]);

        return$bool;
    }

    /**
     * 换货完成 -3
     * @return bool
     */
    public function close()
    {
        $bool = $this->updateSave([
            'refund_time' => time(),
            'status' => RefundApply::CLOSE,
        ]);

        return $bool;
    }
}