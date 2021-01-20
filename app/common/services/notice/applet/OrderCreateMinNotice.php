<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/4
 * Time: 13:48
 */

namespace app\common\services\notice\applet;


use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;


class OrderCreateMinNotice extends BaseMessageBody
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
        $this->data = [
            'keyword1'=>['value'=> $this->member->nickname],// 订单发起者
            'keyword2'=>['value'=> $this->goodsTitle],//商品信息
            'keyword3'=>['value'=>  $this->address['province'] . ' ' . $this->address['city'] . ' ' . $this->address['area'] . ' ' . $this->address['address']],// 收货地址
            'keyword4'=>['value'=> $this->order['price']],// 订单金额
            'keyword5'=>['value'=> $this->timeData['create_time']],// 生成时间
            'keyword6'=>['value'=>$this->order->order_sn],
        ];
    }

    public function sendMessage()
    {
        $this->processData($this->orderModel);
        $this->getTemplate("订单生成通知");
        $this->getBackMember();
        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
        }

        $this->organizeData();
        $result =  (new AppletMessageNotice($this->temp_id,0,$this->data,$this->minOpenIds,2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}