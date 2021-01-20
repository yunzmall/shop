<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 8:26
 */

namespace app\common\services\notice\applet\buyer;


use app\common\models\Order;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerSendMinNotice extends BaseMessageBody
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
           // 'keyword1'=>['value'=> $this->member->nickname],// 用户
            'character_string7'=>['value'=> $this->checkDataLength($this->order->order_sn,32)],//订单号
           // 'keyword3'=>['value'=>  $this->timeData['create_time']],//下单时间
            'amount9'=>['value'=> $this->order['price']],// 订单金额
            'thing5'=>['value'=>  $this->checkDataLength($this->goodsTitle,20)],//商品信息
            'date6'=>['value'=>   $this->timeData['send_time']],//发货时间
            //'keyword7'=>['value'=>  $this->order['express']['express_company_name'] ?: "暂无信息"],//快递公司
            'character_string3'=>['value'=> $this->checkDataLength($this->order['express']['express_sn'] ?: "",32)],//快递单号
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("订单发货通知");
        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }

        $this->organizeData();

        \Log::debug("新版小程序消息-发货1",$this->temp_id);
        \Log::debug("新版小程序消息-发货2",$this->miniFans->openid);
        \Log::debug("新版小程序消息-发货3",$this->data);

        $result =  (new AppletMessageNotice($this->temp_id,$this->miniFans->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}