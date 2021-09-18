<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/9
 * Time: 10:35
 */

namespace app\common\events\order;


use app\common\events\Event;
use app\common\models\Order;

class OrderPayVoiceEvent extends Event
{
    protected $data;
    protected $voiceText;
    /**
     * @var Order
     */
    protected $order;

    /**
     * OrderPayVoiceEvent constructor.
     * @param $order
     * @param $voiceText
     */
    public function __construct($order, $voiceText)
    {
        $this->order = $order;
        $this->voiceText = $voiceText;
    }

    public function getOrderModel()
    {
        return $this->order;
    }

    public function getVoiceText()
    {
        return $this->voiceText;
    }

    public function changeText($voiceText)
    {
        $this->voiceText = $voiceText;
    }
}