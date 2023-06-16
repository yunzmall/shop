<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/12/22
 * Time: 15:25
 */

namespace app\backend\modules\refund\services\operation;


use app\common\events\order\AfterOrderRefundSendBackEvent;
use app\common\models\refund\RefundProcessLog;
use app\common\models\refund\ReturnExpress;
use app\common\repositories\ExpressCompany;

class RefundSendBack extends RefundOperation
{
    protected $statusBeforeChange = [self::WAIT_RETURN_GOODS];
    protected $statusAfterChanged = self::WAIT_RECEIVE_RETURN_GOODS;
    protected $name = '用户发货';
    protected $timeField = 'return_time'; //用户退货时间


    protected $returnExpress;

    protected function afterEventClass()
    {
        return new AfterOrderRefundSendBackEvent($this);
    }

    protected function updateBefore()
    {

        $express_company_name = array_get(ExpressCompany::create()->where('value', $this->getRequest()->input('express_company_code'))->first(), 'name', '其他快递');
        if ($express_company_name == "其他快递" && !empty($this->getRequest()->input('express_company_name'))) {
            $express_company_name = $this->getRequest()->input('express_company_name');
        }
        
        $data = [
            'express_sn' => $this->getRequest()->input('express_sn'),
            'express_company_name' => $express_company_name,
            'express_code' => $this->getRequest()->input('express_company_code'),
            'images' => $this->getRequest()->input('images'),
        ];
        $returnExpress = new ReturnExpress($data);

        $this->returnExpress()->save($returnExpress);

        $this->returnExpress = $returnExpress;
    }

    protected function updateAfter()
    {

    }

    protected function writeLog()
    {
        $detail = [
            '快递公司：'.$this->returnExpress->express_company_name,
            '快递单号：'.$this->returnExpress->express_sn,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_MEMBER);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_USER_SEND_BACK);
        $processLog->saveLog($detail);
    }
}