<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 14:10
 */

namespace app\common\services\notice\official\buyer;

use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\official\OfficialMessageNotice;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerPayedNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate,OrderNoticeData;

    public $orderModel;

    public function __construct($order)
    {
        $this->orderModel = $order;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '粉丝昵称', 'value' => $this->order->belongsToMember->nickname],
            ['name' => '订单号', 'value' => $this->order->order_sn],
            ['name' => '下单时间', 'value' => $this->timeData['create_time']],
            ['name' => '订单金额', 'value' => $this->order['price']],
            ['name' => '运费', 'value' => $this->order['dispatch_price']],
            ['name' => '商品详情（含规格）', 'value' => $this->goodsTitle],
            ['name' => '支付方式', 'value' => $this->order->pay_type_name],
            ['name' => '支付时间', 'value' => $this->timeData['pay_time']],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate('order_pay_success');
        $this->organizeData();
        \Log::debug("新版公众号消息-支付1",$this->template_id);
        \Log::debug("新版公众号消息-支付2",$this->fans->openid);
        \Log::debug("新版公众号消息-支付3",$this->data);
        $result = (new OfficialMessageNotice($this->temp_id,$this->fans->openid,$this->data,[],1,$this->url))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}