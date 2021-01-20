<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 9:49
 */

namespace app\common\services\notice\applet\refund;

use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderRefundFailMinNotice extends BaseMessageBody
{
    use OrderNoticeData,MiniNoticeTemplate;
    public $refund;
    public $orderModel;

    public function __construct($refund,$order)
    {
        $this->refund = $refund;
        $this->orderModel = $order;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'keyword1'=>['value'=>  $this->order->nickname],// 商户名称
            'keyword2'=>['value'=> $this->refund->refund_sn],//订单编号
            'keyword3'=>['value'=> $this->refund->create_time],// 退款时间
            'keyword4'=>['value'=> $this->refund->price],// 退款金额
            'keyword5'=>['value'=> $this->refund->reason],// 退款理由
            'keyword6'=>['value'=> $this->refund->reject_reason],// 拒绝原因
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("退款成功通知");
        $back = [];

        if (empty($this->temp_open)) {
            $back['status'] = 0;
            $back['message'] = $this->temp_title."消息通知未开启";
            return $back;
        }

        $this->organizeData();
        $result =  (new AppletMessageNotice($this->temp_id,$this->miniFans->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}