<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 7:57
 */

namespace app\common\services\notice\applet\buyer;


use app\common\models\Order;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerCancelMinNotice extends BaseMessageBody
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
            'keyword1'=>['value'=> $this->member->nickname],//  用户名
            'keyword2'=>['value'=> $this->order->order_sn],//订单号
            'keyword3'=>['value'=> $this->timeData['create_time']],// 下单时间
            'keyword4'=>['value'=> $this->order['price']],//  订单金额
            'keyword5'=>['value'=> $this->order['dispatch_price']],//  订单运费
            'keyword6'=>['value'=> $this->goodsTitle],//  商品详情
            'keyword7'=>['value'=> $this->timeData['cancel_time']],//  取消时间
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("订单取消通知");
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