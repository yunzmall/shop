<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/21
 * Time: 下午4:54
 */

namespace app\backend\modules\refund\models\type;

use app\backend\modules\refund\models\RefundApply;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\exceptions\AdminException;

abstract class RefundType
{
    /**
     * @var $refundApply RefundApply
     */
    protected $refundApply;

    protected function validate(array $allowBeforeStatus, $operationName)
    {
        if (!in_array($this->refundApply->status, $allowBeforeStatus)) {
            throw new AdminException($this->refundApply->status_name . '的退款申请,无法执行' . $operationName . '操作');
        }
    }

    public function __construct($refundApply)
    {
        $this->refundApply = $refundApply;
    }

    /**
     * 驳回
     * @param $data
     * @return bool
     */
    public function reject($data)
    {
        $this->validate([RefundApply::WAIT_CHECK], '驳回');
        $this->refundApply->status = RefundApply::REJECT;
        $this->refundApply->reject_reason = $data['reject_reason'];
        return $this->refundApply->save();
    }

    /**
     * 手动退款
     * @return bool
     */
    public function consensus()
    {
        //$this->validate([RefundApply::WAIT_CHECK], '手动退款');

        $this->refundApply->status = RefundApply::CONSENSUS;
        $this->refundApply->refund_time = time(); //退款时间
        event(new AfterOrderRefundedEvent($this->refundApply->order));
        return $this->refundApply->save();
    }

    /**
     * 换货完成(关闭订单)
     * @return bool
     */
    public function close()
    {
        $this->refundApply->status = RefundApply::CLOSE;
        $this->refundApply->refund_time = time(); //退款时间
        event(new AfterOrderRefundedEvent($this->refundApply->order));
        return $this->refundApply->save();

    }

    /**
     * 订单退款原路返回调用此方法
     * todo ???
     * @return bool
     */
    public function refundMoney()
    {
        //$this->validate([RefundApply::WAIT_CHECK],'手动退款');
        $this->refundApply->refund_time = time(); //退款时间
        $this->refundApply->status = RefundApply::COMPLETE;
        $this->refundApply->price = \YunShop::request()->refund_custom_money ? \YunShop::request()->refund_custom_money : $this->refundApply->price;//todo  增加自定义退款金额

        event(new AfterOrderRefundedEvent($this->refundApply->order));

        return $this->refundApply->save();
    }

    //abstract public function pass();

}