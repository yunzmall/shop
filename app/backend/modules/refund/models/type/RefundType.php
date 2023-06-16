<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/21
 * Time: 下午4:54
 */

namespace app\backend\modules\refund\models\type;

use app\backend\modules\refund\models\RefundApply;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\events\order\AfterOrderRefundRejectEvent;
use app\common\exceptions\AdminException;

abstract class RefundType
{
    /**
     * @var $refundApply RefundApply
     */
    protected $refundApply;


    protected $status;


    public function __construct($refundApply)
    {
        $this->refundApply = $refundApply;
    }

    protected function validate(array $allowBeforeStatus, $operationName)
    {
        if (!in_array($this->refundApply->status, $allowBeforeStatus)) {
            throw new AdminException($this->refundApply->status_name . '的退款申请,无法执行' . $operationName . '操作');
        }
    }

    /**
     * 驳回 -1
     * @return bool
     */
    public function reject()
    {
        $this->validate([RefundApply::WAIT_CHECK], '驳回');
        $bool = $this->updateSave([
            'status' => RefundApply::REJECT,
            'reject_reason' => $this->refundApply->getRequest()->input('reject_reason'),
        ]);

        $this->afterEvent(AfterOrderRefundRejectEvent::class, $this->refundApply);

        $this->refundApply->order->refund_id = 0;
        $this->refundApply->order->save();

//        event(new AfterOrderRefundRejectEvent($this->refundApply));

        return $bool;
    }

    /**
     * 手动退款 7
     * @return bool
     */
    public function consensus()
    {
        $bool = $this->updateSave([
            'refund_time' => time(),
            'status' => RefundApply::CONSENSUS,
        ]);

        $this->afterEvent(AfterOrderRefundedEvent::class, $this->refundApply->order);
//        event(new AfterOrderRefundedEvent($this->refundApply->order));
        return $bool;
    }

    /**
     * ??不会走这里
     * 换货完成(关闭订单)
     * @return bool
     */
    public function close()
    {
        $bool = $this->updateSave([
            'refund_time' => time(),
            'status' => RefundApply::CLOSE,
        ]);

        $this->afterEvent(AfterOrderRefundedEvent::class, $this->refundApply->order);
//        event(new AfterOrderRefundedEvent($this->refundApply->order));

        return $bool;

    }

    /**
     * 订单退款原路返回调用此方法 6
     * @return bool
     */
    public function refundMoney()
    {
        //$this->validate([RefundApply::WAIT_CHECK],'手动退款');
//        $this->refundApply->refund_time = time(); //退款时间
//        $this->refundApply->status = RefundApply::COMPLETE;
//        $this->refundApply->price = \YunShop::request()->refund_custom_money ? \YunShop::request()->refund_custom_money : $this->refundApply->price;//todo  增加自定义退款金额

        $bool = $this->updateSave([
            'refund_time' => time(),
            'status' => RefundApply::COMPLETE,
            'price' => $this->refundApply->getRequest()->input('refund_custom_money')?:$this->refundApply->price,
        ]);

        $this->afterEvent(AfterOrderRefundedEvent::class, $this->refundApply->order);

        //event(new AfterOrderRefundedEvent($this->refundApply->order));

        return $bool;
    }

    protected function updateSave($data)
    {
        $this->refundApply->fill($data);
        return $this->refundApply->save();
    }

    /**
     * @param $eventClass
     * @param mixed ...$parameter
     */
    protected function afterEvent($eventClass, ...$parameter)
    {
        event(new $eventClass(...$parameter));
    }

}