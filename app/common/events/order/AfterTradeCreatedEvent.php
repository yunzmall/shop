<?php


namespace app\common\events\order;


use app\common\events\Event;
use app\common\modules\trade\models\Trade;

class AfterTradeCreatedEvent extends Event
{
    /**
     * @var Trade
     */
    public $trade;

    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }
}