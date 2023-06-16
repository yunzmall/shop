<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 14:13
 */

namespace app\common\services\notice\official\buyer;

use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\official\OfficialMessageNotice;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderBuyerPackageSendNotice extends OrderBuyerSendNotice
{

    public function organizeData()
    {
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '粉丝昵称', 'value' => $this->member->nickname],
            ['name' => '订单号', 'value' => $this->order->order_sn],
            ['name' => '下单时间', 'value' => $this->timeData['create_time']],
            ['name' => '订单金额', 'value' => $this->order['price']],
            ['name' => '运费', 'value' => $this->order['dispatch_price']],
            ['name' => '商品详情（含规格）', 'value' => $this->packageGoodsTitle],
            ['name' => '发货时间', 'value' => $this->timeData['package_send_time']?:$this->timeData['send_time']],
            ['name' => '快递公司', 'value' => $this->order['expressmany']->last()['express_company_name'] ?: "暂无信息"],
            ['name' => '快递单号', 'value' => $this->order['expressmany']->last()['express_sn'] ?: "暂无信息"],
        ];
    }
}