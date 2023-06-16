<?php
/**
 * 退款申请操作接口版
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/11
 * Time: 14:24
 */

namespace app\backend\modules\refund\controllers;

use app\backend\modules\refund\services\operation\RefundComplete;
use app\backend\modules\refund\services\RefundOperationService;
use app\common\components\BaseController;
use app\backend\modules\refund\models\RefundApply;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\events\order\AfterOrderRefundRejectEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;
use app\common\exceptions\AdminException;
use app\common\exceptions\AppException;
use app\common\models\refund\RefundChangeLog;
use app\common\models\refund\ResendExpress;
use Illuminate\Support\Facades\DB;
use app\backend\modules\refund\services\RefundMessageService;


/**
 * 统一售后操作接口
 * Class VueOperationController
 * @package app\backend\modules\refund\controllers
 */
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

    public function test()
    {

    }

    /**
     * 拒绝
     * @param \Request $request
     * @return mixed
     */
    public function reject()
    {
        RefundOperationService::refundReject(['refund_id' => request()->input('refund_id')]);

        return $this->successJson('操作成功');
    }


    /**
     * 同意
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function pass()
    {
        RefundOperationService::refundPass(['refund_id' => request()->input('refund_id')]);

        return $this->successJson('操作成功');
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function batchResend()
    {
        RefundOperationService::refundBatchResend(['refund_id' => request()->input('refund_id')]);
        return $this->successJson('操作成功');

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function resend()
    {
        RefundOperationService::refundResend(['refund_id' => request()->input('refund_id')]);
        return $this->successJson('操作成功');

    }

    public function close()
    {
        RefundOperationService::refundClose(['refund_id' => request()->input('refund_id')]);
        return $this->successJson('操作成功');
    }

    /**
     * 手动退款
     * @param \Request $request
     * @return mixed
     */
    public function consensus()
    {
        RefundOperationService::refundConsensus(['refund_id' => request()->input('refund_id')]);

        return $this->successJson('操作成功');
    }

    /**
     * 修改退款金额
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePrice()
    {
        $params = [
            'refund_id' => request()->input('refund_id'),
            'change_price' => request()->input('change_price'),
        ];

        $bool = RefundOperationService::refundChangePrice($params);

        if ($bool) {
            return $this->successJson('改价成功');
        }

        return $this->errorJson('改价失败');
    }
}