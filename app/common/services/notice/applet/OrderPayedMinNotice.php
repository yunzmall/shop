<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/5
 * Time: 17:34
 */

namespace app\common\services\notice\applet;

use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OrderNoticeData;
use app\common\services\notice\share\MiniNoticeTemplate;

class OrderPayedMinNotice extends BaseMessageBody
{
    use OrderNoticeData,BackstageNoticeMember,MiniNoticeTemplate;

    public $orderModel;

    public function __construct($order)
    {
        $this->orderModel = $order;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
//        $this->data = [
//            'keyword1'=>['value'=> $this->member->nickname],// 用户
//            'keyword2'=>['value'=> $this->order->order_sn],//订单号
//            'keyword3'=>['value'=>  $this->goodsTitle],//商品名称
//            'keyword4'=>['value'=> $this->order->pay_type_name],// 支付方式
//            'keyword5'=>['value'=> $this->order['price']],// 支付金额
//            'keyword6'=>['value'=> $this->address['realname']],//收貨人
//            'keyword7'=>['value'=>   $this->address['province'] . ' ' . $this->address['city'] . ' ' . $this->address['area'] . ' ' . $this->address['address']],//收貨地址
//        ];
        $this->data = [
            'name1'=>['value'=> $this->member->nickname],//姓名
            //'date4'=>['value'=> $this->order->order_sn],//订单号码
            'date4'=>['value'=>  $this->timeData['create_time']],//下单时间
            'amount3'=>['value'=>  $this->order['price']],//订单金额
            'thing6'=>['value'=> $this->goodsTitle],// 商品名称
            //'keyword6'=>['value'=> $this->order->pay_type_name],//支付方式
            'date8'=>['value'=>  $this->timeData['pay_time']],//支付时间
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("订单支付成功通知");
        $this->getBackMember();
        if (empty($this->temp_open)) {
            $back['message'] = "消息通知未开启";
            \Log::debug($back['message']);
        }

        $this->organizeData();

        $result =  (new AppletMessageNotice($this->temp_id,0,$this->data,$this->minOpenIds,2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}