<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 8:49
 */

namespace app\common\services\notice\applet\buyer;

use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerRefundMinNotice extends BaseMessageBody
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
            'keyword1'=>['value'=>  $this->member->nickname],// 退款人
            'keyword2'=>['value'=> $this->refund->refund_sn],//退款单号
            'keyword3'=>['value'=> $this->refund->create_time],// 退款时间
            'keyword4'=>['value'=>  $this->order->pay_type_name],// 退款方式
            'keyword5'=>['value'=> $this->order->price],// 订单金额
            'keyword6'=>['value'=> $this->refund->reason],// 订单原因
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("退款申请通知");
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