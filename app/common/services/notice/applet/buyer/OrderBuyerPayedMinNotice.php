<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 8:15
 */

namespace app\common\services\notice\applet\buyer;


use app\common\models\Order;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerPayedMinNotice extends BaseMessageBody
{

    use OrderNoticeData,MiniNoticeTemplate;
    public $orderModel;

    public function __construct($order)
    {
        $this->orderModel = $order;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'name1'=>['value'=> $this->checkDataLength($this->member->nickname,10)],//姓名
            //'date4'=>['value'=> $this->order->order_sn],//订单号码
            'date4'=>['value'=>  $this->timeData['create_time']],//下单时间
            'amount3'=>['value'=>  $this->order['price']],//订单金额
            'thing6'=>['value'=> $this->checkDataLength($this->goodsTitle,20)],// 商品名称
            //'keyword6'=>['value'=> $this->order->pay_type_name],//支付方式
            'date8'=>['value'=>  $this->timeData['pay_time']],//支付时间
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("订单支付成功通知");
        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }
        
        $this->organizeData();

        \Log::debug("新版小程序消息-支付1",$this->temp_id);
        \Log::debug("新版小程序消息-支付2",$this->miniFans->openid);
        \Log::debug("新版小程序消息-支付3",$this->data);

        $result = (new AppletMessageNotice($this->temp_id,$this->miniFans->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}