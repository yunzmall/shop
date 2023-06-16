<?php
/**
 * Created by PhpStorm.
 *
 *
 *
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