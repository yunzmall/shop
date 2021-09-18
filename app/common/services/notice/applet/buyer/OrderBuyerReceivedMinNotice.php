<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 8:35
 */

namespace app\common\services\notice\applet\buyer;


use app\common\models\Order;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerReceivedMinNotice extends BaseMessageBody
{

    use MiniNoticeTemplate,OrderNoticeData;

    public $orderModel;

    public function __construct($order)
    {
        $this->orderModel = $order;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.

        $order_sn = $this->order->order_sn;
//        $order_sn = substr($this->order->order_sn,2);
//        $order_sn = preg_replace('|[a-zA-Z]+|','',$order_sn);   //截取字母
        $this->data = [
            'thing6'=>['value'=> $this->checkDataLength($this->goodsTitle,20)],//  商品名称
            'thing4'=>['value'=> $this->checkDataLength((\Setting::get('shop.shop')['name']?:''),20)],//商户名称
            'character_string8'=>['value'=>  $this->checkDataLength($order_sn,32)],// 订单编码
            'date7'=>['value'=> $this->timeData['finish_time']],//  收货时间
            'phrase3'=>['value'=> "已收货"]//  订单状态xiao
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.

        $this->processData($this->orderModel);
        $this->getTemplate("确认收货通知");
        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }

        $this->organizeData();
        \Log::debug("新版小程序消息-收货1",$this->temp_id);
        \Log::debug("新版小程序消息-收货2",$this->miniFans->openid);
        \Log::debug("新版小程序消息-收货3",$this->data);
        $result =  (new AppletMessageNotice($this->temp_id,$this->miniFans->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }

    }
}