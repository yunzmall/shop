<?php
/**
 * 退款申请操作接口版
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/11
 * Time: 14:24
 */

namespace app\backend\modules\refund\controllers;

use app\common\components\BaseController;
use app\backend\modules\refund\models\RefundApply;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\exceptions\AdminException;
use app\common\exceptions\AppException;
use app\common\models\refund\RefundChangeLog;
use app\common\models\refund\ResendExpress;
use Illuminate\Support\Facades\DB;
use app\backend\modules\refund\services\RefundMessageService;


class VueOperationController extends BaseController
{
    /**
     * @var $refundApply RefundApply
     */
    private $refundApply;

    public function preAction()
    {
        parent::preAction();
        $this->validate([
            'refund_id' => 'required',
        ]);
        $this->refundApply = RefundApply::find(request()->input('refund_id'));
        if (!isset($this->refundApply)) {
            throw new AdminException('退款记录不存在');
        }
    }

    /**
     * 拒绝
     * @param \Request $request
     * @return mixed
     */
    public function reject()
    {
        $refundApply = $this->refundApply;
        DB::transaction(function () use ($refundApply) {
            $refundApply->reject(\Request::only(['reject_reason']));
            $refundApply->order->refund_id = 0;
            $refundApply->order->save();
            RefundMessageService::rejectMessage($refundApply);//通知买家
        });

        if (app('plugins')->isEnabled('instation-message')) {
            //开启了站内消息插件
            event(new \Yunshop\InstationMessage\event\RejectOrderRefundEvent($this->refundApply));
        }

        return $this->successJson('操作成功');
    }

    /**
     * 同意
     * @param \Request $request
     * @return mixed
     */
    public function pass()
    {
        $this->refundApply->pass();

        if (app('plugins')->isEnabled('instation-message')) {
            //开启了站内消息插件
            event(new \Yunshop\InstationMessage\event\PassOrderRefundEvent($this->refundApply));
        }

        return $this->successJson('操作成功');
    }

    public function receiveReturnGoods()
    {
        $this->refundApply->receiveReturnGoods();
        return $this->message('操作成功', '');
    }

    public function resend()
    {
        $resendExpress = new ResendExpress(request()->only('express_code', 'express_company_name', 'express_sn'));
        $this->refundApply->resendExpress()->save($resendExpress);
        $this->refundApply->resend();

        return $this->successJson('操作成功');

    }

    public function close()
    {
        $refundApply = $this->refundApply;
        DB::transaction(function () use ($refundApply) {
            $refundApply->close();
            RefundMessageService::passMessage($refundApply);//通知买家

            if (app('plugins')->isEnabled('instation-message')) {
                event(new \Yunshop\InstationMessage\event\OrderRefundSuccessEvent($refundApply));
            }
        });
        return $this->successJson('操作成功');
    }

    /**
     * 手动退款
     * @param \Request $request
     * @return mixed
     */
    public function consensus()
    {

        $refundApply = $this->refundApply;
        DB::transaction(function () use ($refundApply) {
            $refundApply->consensus();
            $refundApply->order->close();
            RefundMessageService::passMessage($refundApply);//通知买家

            if (app('plugins')->isEnabled('instation-message')) {
                event(new \Yunshop\InstationMessage\event\OrderRefundSuccessEvent($refundApply));
            }
        });
        return $this->successJson('操作成功');
    }

    /**
     * 修改退款金额
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePrice()
    {
        $refund_change_price = request()->input('change_price');

        $old_price = $this->refundApply->price;

        $new_price = $this->refundApply->price + $refund_change_price;

        if(bccomp($new_price, 0,2) != 1) {
            throw new AppException('退款金额必须大于0！');
        }

        $this->refundApply->price = $new_price;

        $bool =  $this->refundApply->save();

        if ($bool) {
            $data = [
                'old_price' => $old_price,
                'new_price' => $new_price,
                'change_price' => $refund_change_price,
                'username' => \Yunshop::app()->username,
                'refund_id' => $this->refundApply->id,
                'order_id' => $this->refundApply->order_id,
            ];
            RefundChangeLog::create($data);


            return $this->successJson('改价成功');
        }

        return $this->errorJson('改价失败');
    }
}