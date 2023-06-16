<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/3
 * Time: 14:42
 */

namespace app\backend\modules\order\controllers;


use app\common\components\BaseController;
use app\common\models\Order;
use app\common\models\order\ManualRefundLog;
use app\common\models\PayType;
use app\frontend\modules\order\services\OrderService;
use app\common\models\order\Remark;
use app\common\exceptions\AppException;
use Illuminate\Support\Facades\DB;
use app\framework\Http\Request;

class VueOperationController extends BaseController
{
    protected $param;
    /**
     * @var Order
     */
    protected $order;
    public $transactionActions = ['pay','send','addOrderExpress','separateSend','cancelSend','receive','close'];

    /**
     * @return mixed|void
     * @throws AppException
     */
    public function preAction()
    {
        parent::preAction();

        $this->param = request()->input();

        if (!isset($this->param['order_id'])) {
            throw new AppException('order_id不能为空!');

        }
        //trim无法处理中文的半角圆角等空格，只能用正则
        $this->param['express_sn'] = preg_replace("/(\s|\ \;|　|\xc2\xa0)/", "", $this->param['express_sn']);
        $this->order = Order::find($this->param['order_id']);
        if (!isset($this->order)) {
            throw new AppException('未找到该订单!');

        }
    }

    /**
     * 支付
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function pay()
    {
        $this->order->backendPay();
        return $this->successJson('操作成功');

    }

    /**
     * 确认发货
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function send()
    {
        OrderService::orderSend($this->param);
        return $this->successJson('操作成功');
    }

    /**
     * 多包裹继续返回
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function addOrderExpress()
    {

        OrderService::addOrderExpress($this->param);

        return $this->successJson('操作成功');
    }

    /**
     * 多包裹发货
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function separateSend()
    {
        if ($this->order->status == Order::WAIT_SEND) {
            OrderService::orderSend($this->param);
        } else {
            OrderService::addOrderExpress($this->param);
        }
        return $this->successJson('操作成功');
    }

    /**
     * 取消发货
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function cancelSend()
    {
        OrderService::orderCancelSend($this->param);

        return $this->successJson('操作成功');
    }

    /**
     * 确认收货
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function receive()
    {
        OrderService::orderReceive($this->param);

        return $this->successJson('操作成功');
    }

    /**
     * 关闭订单
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function close()
    {
        OrderService::orderClose($this->param);

        return $this->successJson('操作成功');
    }

    /**
     * 退款并关闭订单
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
            throw new AppException('订单有售后记录待处理,无法继续操作');
        }

        \app\backend\modules\refund\services\RefundOperationService::orderCloseAndRefund($this->order);

        //$result = $this->order->refund();

        ManualRefundLog::saveLog($this->order->id);

        return $this->successJson('操作成功');
    }

    public function partRefund(Request $request)
    {
        $order = Order::find($request->input('order_id'));
        if (!isset($order)) {
            throw new AppException('订单不存在');
        }
        if ($order->status < Order::WAIT_SEND) {
            throw new AppException('订单未付款,无法退款');
        }

        if ($order->hasOneRefundApply && $order->hasOneRefundApply->isRefunding()) {
            throw new AppException('申请已提交,处理中');
        }

        $refundApply = new \app\backend\modules\refund\services\operation\RefundApply(['uid'=>$order->uid]);
        $refundApply->setRelation('order',$order);

        DB::transaction(function()use($refundApply){
            $refundApply->execute();
        });
//        $order = Order::find($request->input('order_id'));
        return $this->successJson('操作成功',$refundApply->id);
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
                    return $this->errorJson('订单备注保存失败');
                }
            } else {
                $reUp = Remark::where('order_id', request()->input('order_id') )
                    ->where('remark', $remark->remark)
                    ->update(['remark'=> request()->input('remark')]);

                if (!$reUp) {
                    return $this->errorJson('订单备注保存失败');
                }
            }
        }
        return $this->successJson('订单备注保存成功');
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

        return $this->successJson('订单发票保存成功');
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function setParam($param)
    {
        $this->param = $param;
    }
}