<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 9:42
 */

namespace app\common\services\notice\applet\refund;


use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderRefundSuccessMinNotice extends BaseMessageBody
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
           //'keyword1'=>['value'=>  $this->order->nickname],// 退款人
            //'keyword2'=>['value'=> $this->refund->refund_sn],//退款单号
            //'keyword4'=>['value'=> $this->order->pay_type_name],// 退款方式
            //'keyword6'=>['value'=> $this->refund->reason],// 退款原因,
            'amount2'=>['value'=> $this->order->price],// 退款金额
            'character_string1'=>['value'=> $this->checkDataLength($this->order->order_sn,32)],
            'thing4'=>['value'=>$this->checkDataLength($this->refund->reason,20)],
            'time3' => ['value'=> $this->refund->create_time->toDateTimeString()],
            'phrase5' => ['value'=>'退款成功']
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("订单退款通知");

        if (empty($this->temp_open)) {
            \Log::debug($this->temp_title."消息通知未开启");
            return ;
        }

        $this->organizeData();

        \Log::debug("新版小程序消息-退款1",$this->temp_id);
        \Log::debug("新版小程序消息-退款2",$this->miniFans->openid);
        \Log::debug("新版小程序消息-退款3",$this->data);

        $result =  (new AppletMessageNotice($this->temp_id,$this->miniFans->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}