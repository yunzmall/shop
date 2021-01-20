<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/5
 * Time: 17:34
 */

namespace app\common\services\notice\applet\store;

use app\common\models\Order;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OrderNoticeData;
use app\common\services\notice\share\MiniNoticeTemplate;

class StoreOrderPayedMinNotice extends BaseMessageBody
{
    use OrderNoticeData,MiniNoticeTemplate;

    public $orderModel;
    public $store;

    public function __construct($order,$store)
    {
        $this->orderModel = $order;
        $this->store = $store;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'date6'=>['value'=> $this->timeData['pay_time']],// 成交时间
            'character_string1'=>['value'=> $this->order->order_sn],//订单号
            'thing3'=>['value'=>  $this->checkDataLength($this->member->nickname,20)],//购买者
            'thing4'=>['value'=> $this->checkDataLength($this->goodsTitle,20)],// 购买商品
            'number5'=>['value'=> $this->goodsNum],// 购买数量
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("新订单提醒");

        if (empty($this->temp_open)) {
            \Log::debug($this->temp_title."消息通知未开启");
            return ;
        }

        $this->organizeData();

        \Log::debug("新版小程序消息-门店订单1",$this->temp_id);
        \Log::debug("新版小程序消息-门店订单2",$this->store->hasOneStore->hasOneMember->hasOneMiniApp->openid);
        \Log::debug("新版小程序消息-门店订单3",$this->data);

        $result =  (new AppletMessageNotice($this->temp_id,$this->store->hasOneStore->hasOneMember->hasOneMiniApp->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}