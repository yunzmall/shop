<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/7
 * Time: 20:01
 */

namespace app\common\services\notice\official;

use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderCreateNotice extends BaseMessageBody
{

    public $orderModel;
    protected $orderStatus;//订单状态

    use OrderNoticeData,OfficialNoticeTemplate,BackstageNoticeMember;

    public function __construct($order, $status)
    {
        $this->orderModel = $order;
        $this->orderStatus = $status;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '粉丝昵称', 'value' => $this->member->nickname],
            ['name' => '订单号', 'value' => $this->order->order_sn],
            ['name' => '下单时间', 'value' => $this->timeData['create_time']],
            ['name' => '订单金额', 'value' => $this->order['price']],
            ['name' => '运费', 'value' => $this->order['dispatch_price']],
            ['name' => '商品详情（含规格）', 'value' => $this->goodsTitle],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate('seller_order_create');
        $this->getBackMember();
        $this->organizeData();

        // 卖家创建消息验证
        if(
            (empty(\Setting::get('shop.notice')['notice_enable']['created']) && $this->orderStatus == 1) ||
            (empty(\Setting::get('shop.notice')['notice_enable']['paid']) && $this->orderStatus == 2) ||
            (empty(\Setting::get('shop.notice')['notice_enable']['received']) && $this->orderStatus == 3)
        ){
            return;
        }

        \Log::debug("新版公众号消息-卖家创建1",$this->template_id);
        \Log::debug("新版公众号消息-卖家创建2",$this->openids);
        \Log::debug("新版公众号消息-卖家创建3",$this->data);

        $result = (new OfficialMessageNotice($this->temp_id,0,$this->data,$this->openids,1,$this->url))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}