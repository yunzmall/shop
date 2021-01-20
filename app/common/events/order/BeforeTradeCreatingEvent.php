<?php


namespace app\common\events\order;


use app\common\events\Event;
use app\common\modules\trade\models\Trade;

class BeforeTradeCreatingEvent extends Event
{
    /**
     * @var Trade
     */
    protected $trade;

    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    public function getTrade()
    {
        return $this->trade;
    }

}