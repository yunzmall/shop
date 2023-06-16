<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/7
 * Time: 20:42
 */

namespace app\common\services\notice\applet;

use app\common\services\notice\BaseMessageBody;
use app\common\models\Order;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OrderNoticeData;
use app\common\services\notice\share\MiniNoticeTemplate;

class OrderReceivedMinNotice extends BaseMessageBody
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
//            'keyword1'=>['value'=> $this->goodsTitle],// 商品名
//            'keyword2'=>['value'=> $this->member->nickname],//买家昵称
//            'keyword3'=>['value'=> $this->order->order_sn],//  订单编号
//            'keyword4'=>['value'=> $this->timeData['create_time']],//  订单时间
//            'keyword5'=>['value'=> $this->order['price']],//订单金額
//            'keyword6'=>['value'=> $this->timeData['finish_time']],//  确认收货时间
//        ];

        $this->data = [
            'thing6'=>['value'=> $this->goodsTitle],//  商品名称
            'thing4'=>['value'=> \Setting::get('shop.shop')['name']],//商户名称
            'character_string8'=>['value'=>  $this->order->order_sn],// 订单编号
            'date7'=>['value'=> $this->timeData['finish_time']],//  收货时间
            'phrase3'=>['value'=> "已收货"]//  订单状态
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("确认收货通知");
        $this->getBackMember();
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