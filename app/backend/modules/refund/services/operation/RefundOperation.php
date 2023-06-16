<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/22
 * Time: 13:58
 */

namespace app\backend\modules\refund\services\operation;

use app\common\events\Event;
use app\common\models\OrderGoods;
use app\common\models\refund\RefundGoodsLog;
use app\common\modules\refund\RefundOrderFactory;
use app\framework\Http\Request;
use app\frontend\modules\refund\services\RefundBackWayService;
use Illuminate\Support\Facades\DB;
use app\common\exceptions\AppException;
use app\common\models\refund\RefundApply;

abstract class RefundOperation  extends RefundApply
{

    protected $transaction = false;

    protected $statusBeforeChange = [];
    //改变后状态
    protected $statusAfterChanged;
    protected $name = ''; //操作名称
    protected $timeField = ''; //操作时间


    protected $refundOrderType;//售后订单类型

    protected $port_type = 'frontend'; //frontend = 前端、backend = 后端

    public $params;//参数，暂时没用

    protected $backWay;

    /**
     * 表更新后
     */
    abstract protected function updateAfter();


    /**
     * 操作日志记录
     */
    abstract protected function writeLog();

    /**
     * 表更新前
     */
    abstract protected function updateBefore();

    /**
     * 发送通知
     */
    protected function sendMessage() {}

    /**
     * 退款操作事件
     * @return Event|array|null
     */
    protected function afterEventClass()
    {
        return null;
    }

    /**
     * 必须要在触发完退款操作事件，才能去操作订单。
     * 因为订单状态改变会触发订单事件
     */
    protected function triggerEventAfter()
    {

    }

    /**
     * 更新申请表
     * @return bool
     */
    protected function updateTable() {
        if (isset($this->statusAfterChanged)) {
            $this->status = $this->statusAfterChanged;
        }
        if(!empty($this->timeField)){
            $timeFields = $this->timeField;
            $this->$timeFields = time();
        }
        return $this->save();
    }

    /**
     * 操作验证
     * @throws AppException
     */
    protected function operationValidate()
    {
        if (!empty($this->statusBeforeChange) && !in_array($this->status, $this->statusBeforeChange)) {
            throw new AppException($this->status_name . '的退款申请,无法执行' . $this->name . '操作');
        }
    }

    protected function backWay()
    {
        if (!isset($this->backWay)) {
            $this->backWay = RefundBackWayService::getBackWayClass($this->refund_way_type);
            $this->backWay->setRefundApply($this);
        }
        return $this->backWay;
    }

    protected function setBackWay()
    {
        $this->backWay()->saveRelation();
    }

    /***
     * 执行操作操作
     * @return bool
     * @throws AppException
     */
    final public function execute()
    {
        $this->operationValidate(); //验证

        $this->updateBefore(); //更新表之前

        $result = $this->updateTable();//更新表
        if (!$result) {
            throw new AppException('信息更新失败');
        }

        $this->updateAfter();//更新表之后

        $this->writeLog(); //写入售后协商记录表

        $this->triggerRefundEvent(); //售后事件触发

        $this->triggerEventAfter();//事件触发后

        $this->sendMessage(); //发送通知

        return $result;
    }

    /**
     * 发布监听
     */
    final protected function triggerRefundEvent()
    {
        $eventClass =  $this->afterEventClass();

        if (is_array($eventClass)) {
            foreach ($eventClass as $itemEvent) {
                if (!is_null($itemEvent) && ($itemEvent instanceof Event)) {
                    event($itemEvent);
                }
            }

        } else {
            if (!is_null($eventClass) && ($eventClass instanceof Event)) {
                event($eventClass);
            }
        }

    }

    final public function setParams($request)
    {
        $this->params = $request;
    }

    final public function getParam($key)
    {
        return array_get($this->params, $key, '');
    }

    final public function getParams()
    {
        return $this->params;
    }

    final public function getRequest()
    {
//        if (!isset($this->request)) {
//            $this->request = request();
//        }
        return request();
    }


    //对应的订单
    final public function relatedPluginOrder()
    {
        if (!isset($this->refundOrderType)) {
            $this->refundOrderType = RefundOrderFactory::getInstance()->getRefundOrder($this->order, $this->port_type);
        }

        return $this->refundOrderType;
    }

    /**
     * 售后申请记录退款商品
     * @param $refundGoods
     */
    protected function createOrderGoodsRefundLog($refundGoods)
    {

        $order_goods_ids = array_column($refundGoods, 'id');

        OrderGoods::whereIn('id', $order_goods_ids)->update(['refund_id' => $this->id]);

        foreach ($refundGoods as $goodsItem) {
            RefundGoodsLog::saveData($this,$goodsItem);

        }
    }

    /**
     * 退款完成、换货完成
     * 更新订单商品表售后状态
     */
    protected function updateOrderGoodsRefundStatus()
    {

        //不是换货售后商品需要标识下退过款
        if ($this->refund_type != self::REFUND_TYPE_EXCHANGE_GOODS) {
            //is_refund 字段现在作用只是标记下该商品售后过
            $updateData['is_refund'] =  DB::raw('is_refund + 1');
        }
        $updateData['refund_id'] = 0;

        OrderGoods::where('refund_id', $this->id)->update($updateData);

        $this->closeManyApply();

    }

    /**
     * 售后驳回、用户取消申请
     * @throws \Exception
     */
    protected function delRefundOrderGoodsLog()
    {
        RefundGoodsLog::where('refund_id', $this->id)->delete();

        //更新订单商品表售后字段
        OrderGoods::where('refund_id', $this->id)->update(['refund_id'=> 0]);

        $this->closeManyApply();
    }

    protected function cancelRefund()
    {
        return $this->order->cancelRefund();
    }

    protected function closeOrder()
    {
        return $this->order->close();
    }

    //todo blank-05-23 有一种情况就是订单同时拥有多条售后记录问题，造成售后记录异常
    protected function closeManyApply()
    {
        $errorRefundId = \app\common\models\refund\RefundApply::uniacid()
            ->where('order_id', $this->order_id)
            ->where('id','!=', $this->id)
            ->where('status', '>=',RefundApply::WAIT_CHECK)
            ->where('status', '<', RefundApply::COMPLETE)->pluck('id')->toArray();

        if ($errorRefundId) {
            \app\common\models\refund\RefundApply::whereIn('id', $errorRefundId)->update([
                'status' => RefundApply::CLOSE,
                'reject_time' => time(),
                'reason' => '关闭之前未完成的记录',
                'remark' => '订单同时存在多条的售后记录',
            ]);
            RefundGoodsLog::whereIn('refund_id', $errorRefundId)->delete();
        }

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