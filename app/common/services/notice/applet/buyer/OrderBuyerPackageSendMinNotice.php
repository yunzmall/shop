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

class OrderBuyerPackageSendMinNotice extends OrderBuyerSendMinNotice
{
    public function organizeData()
    {
        $this->data = [
            'character_string7'=>['value'=> $this->checkDataLength($this->order->order_sn,32)],//订单号
            'amount9'=>['value'=> $this->order['price']],// 订单金额
            'thing5'=>['value'=>  $this->checkDataLength($this->packageGoodsTitle,20)],//商品信息
            'date6'=>['value'=> $this->timeData['package_send_time']?:$this->timeData['send_time']],//发货时间
            'character_string3'=>['value'=> $this->checkDataLength($this->order['expressmany']->last()['express_sn']?: "",32)],//快递单号
        ];
    }
}