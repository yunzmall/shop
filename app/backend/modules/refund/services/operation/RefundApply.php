<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/9/20
 * Time: 9:44
 */

namespace app\backend\modules\refund\services\operation;

use app\common\models\refund\RefundProcessLog;

class RefundApply extends \app\frontend\modules\refund\services\operation\RefundApply
{
    protected $port_type = 'backend';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->uid = $attributes['uid'];
    }

    protected function writeLog()
    {
        $detail = [
            '售后类型：'. $this->getRefundTypeName()[$this->refund_type],
            $this->refund_type == static::REFUND_TYPE_EXCHANGE_GOODS ? '': '退款金额：'.$this->price,
            $this->freight_price?'运费:'. $this->freight_price :'',
            $this->other_price?'其他费用:'. $this->other_price :'',
            '售后原因：'.$this->reason,
            '说明：'.$this->content,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_MEMBER);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_APPLY_SHOP);
        $processLog->saveLog($detail, request()->input());
    }
}