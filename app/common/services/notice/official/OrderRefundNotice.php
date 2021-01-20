<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 11:31
 */

namespace app\common\services\notice\official;


use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderRefundNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate,OrderNoticeData,BackstageNoticeMember;
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
            ['name' => '粉丝昵称', 'value' => $this->member->nickname],
            ['name' => '退款单号', 'value' => $this->refund->refund_sn],
            ['name' => '退款申请时间', 'value' => $this->refund->create_time],
            ['name' => '退款类型', 'value' => $this->refund->refund_type_name],
            ['name' => '退款方式', 'value' => $this->order->pay_type_name],
            ['name' => '退款原因', 'value' => $this->refund->reason],
            ['name' => '订单编号', 'value' => $this->order->order_sn],
            ['name' => '退款金额', 'value' => $this->refund->price],
            ['name' => '商品详情（含规格）', 'value' => $this->goodsTitle],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate('order_refund_apply_to_saler');
        $this->organizeData();
        $this->getBackMember();
        \Log::debug("新版公众号消息-退款1",$this->template_id);
        \Log::debug("新版公众号消息-退款2",$this->openids);
        \Log::debug("新版公众号消息-退款3",$this->data);

        $result = (new OfficialMessageNotice($this->temp_id,0,$this->data,$this->openids,1,$this->url))->sendMessage();
        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}