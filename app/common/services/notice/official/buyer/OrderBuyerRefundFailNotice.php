<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 14:30
 */

namespace app\common\services\notice\official\buyer;

use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\official\OfficialMessageNotice;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerRefundFailNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate,OrderNoticeData;

    public $orderModel;
    public $refund;

    public function __construct($refund,$order)
    {
        $this->orderModel = $order;
        $this->refund = $refund;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '粉丝昵称', 'value' => $this->member['nickname']],
            ['name' => '退款单号', 'value' => $this->refund->refund_sn],
            ['name' => '退款申请时间', 'value' => $this->refund->create_time],
            ['name' => '退款方式', 'value' => $this->order->pay_type_name],
            ['name' => '退款金额', 'value' => $this->refund->price],
            ['name' => '退款原因', 'value' => $this->refund->reason],
            ['name' => '驳回原因', 'value' => $this->refund->reject_reason],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate('order_refund_reject');
        $this->organizeData();
        \Log::debug("新版公众号消息-退款失败1",$this->template_id);
        \Log::debug("新版公众号消息-退款失败2",$this->fans->openid);
        \Log::debug("新版公众号消息-退款失败3",$this->data);
        $result = (new OfficialMessageNotice($this->temp_id,$this->fans->openid,$this->data,[],1,$this->url))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}