<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 下午2:30
 */

namespace app\backend\modules\order\controllers;

use app\common\components\BaseController;
use app\common\models\Order;
use app\common\models\PayType;
use app\frontend\modules\order\services\OrderService;
use app\common\models\order\Remark;
use app\common\exceptions\AppException;

class OperationController extends BaseController
{
    protected $param;
    /**
     * @var Order
     */
    protected $order;
    public $transactionActions = ['*'];

    public function preAction()
    {
        parent::preAction();

        $this->param = request()->input();

        if (!isset($this->param['order_id'])) {
            return $this->message('order_id不能为空!', '', 'error');

        }
        $this->order = Order::find($this->param['order_id']);
        if (!isset($this->order)) {
            return $this->message('未找到该订单!', '', 'error');

        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function pay()
    {
        $this->order->backendPay();
        return $this->successJson();

    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function cancelPay()
    {
        OrderService::orderCancelPay($this->param);

        return $this->message('操作成功');
    }
    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function addOrderExpress()
    {

        OrderService::addOrderExpress($this->param);

        return $this->message('操作成功');
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function send()
    {

        OrderService::orderSend($this->param);

        return $this->message('操作成功');
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function fClose(){
        $this->order->refund();
        return $this->message('强制退款成功');

    }
    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function cancelSend()
    {
        OrderService::orderCancelSend($this->param);

        return $this->message('操作成功');
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function receive()
    {
        OrderService::orderReceive($this->param);

        return $this->message('操作成功');
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function close()
    {
        OrderService::orderClose($this->param);

        return $this->message('操作成功');
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function manualRefund()
    {
        $restrictAccess = \app\common\services\RequestTokenService::limitRepeat('manual_refund_'. $this->param['order_id']);

        if (!$restrictAccess) {
            throw new AppException('短时间内重复操作，请等待10秒后再操作');
        }

        if ($this->order->isPending()) {
            throw new AppException("订单已锁定,无法继续操作");
        }
        if ($this->order->hasOneRefundApply && $this->order->hasOneRefundApply->isRefunding()) {
            throw new AppException('订单有售后待处理,无法继续操作');
        }
//        $result = $this->order->refund();
        \app\backend\modules\refund\services\RefundOperationService::orderCloseAndRefund($this->order);

        \app\common\models\order\ManualRefundLog::saveLog($this->order->id);


        return $this->message('操作成功');
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function delete()
    {
        OrderService::orderDelete($this->param);

        return $this->message('操作成功');
    }

    public function remarks()
    {
        $order = Order::find(request()->input('order_id'));
        if(!$order){
            throw new AppException("未找到该订单".request()->input('order_id'));
        }

        if(request()->has('remark')){
            $remark = $order->hasOneOrderRemark;
            if (!$remark) {
                $remark = new Remark([
                    'order_id' => request()->input('order_id'),
                    'remark' => request()->input('remark')
                ]);

                if(!$remark->save()){
                    return $this->errorJson();
                }
            } else {
                $reUp = Remark::where('order_id', request()->input('order_id') )
                    ->where('remark', $remark->remark)
                    ->update(['remark'=> request()->input('remark')]);

                if (!$reUp) {
                    return $this->errorJson();
                }
            }
        }
        //(new \app\common\services\operation\OrderLog($remark, 'special'));
        echo json_encode(["data" => '', "result" => 1]);
    }

    public function invoice()
    {
        $order = Order::with(['orderInvoice'])->find(request()->input('order_id'));
        
        if(!$order){
            throw new AppException("未找到该订单".request()->input('order_id'));
        }


        if (!request()->has('invoice')) {
            throw new AppException('未上传图片');
        }


        $orderInvoice =  $order->orderInvoice;

        if ($orderInvoice) {
            $orderInvoice->invoice = request()->input('invoice');
            $orderInvoice->save();
        }

        $order->invoice = request()->input('invoice');
        $order->save();

        //发邮件
       // $flag = \Illuminate\Support\Facades\Mail::to('email')->subjuet('订单发票')->send(new \app\Mail\OrderInvoice(yz_tomedia(request()->input('invoice'))));

        echo json_encode(["data" => '', "result" => 1]);
    }
}