<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/5/2
 * Time: 上午10:59
 */
namespace app\frontend\modules\finance\listeners;

use app\common\events\withdraw\WithdrawAppliedEvent;
use app\common\events\withdraw\WithdrawAuditedEvent;
use app\common\events\withdraw\WithdrawPayedEvent;
use app\common\events\withdraw\WithdrawPayingEvent;
use app\common\services\finance\MessageService;

use app\common\services\SystemMsgService;

use app\common\services\notice\applet\withdraw\WithDrawArrivalNotice;
use app\common\services\notice\applet\withdraw\WithDrawAuditNotice;


class IncomeWithdraw
{
    /**
     * 提现申请
     * @param WithdrawAppliedEvent $event
     */
    public function withdrawApplied($event)
    {
        $withdrawModel = $event->getWithdrawModel();

        (new MessageService($withdrawModel))->applyNotice();
        //[系统消息通知]
        (new SystemMsgService())->withdrawNotice($withdrawModel);
    }

    /**
     * 提现审核
     * @param WithdrawAuditedEvent $event
     */
    public function withdrawCheck($event)
    {
        $withdrawModel = $event->getWithdrawModel();

        (new MessageService($withdrawModel))->auditNotice();

        $withdrawNotice = new WithDrawAuditNotice($withdrawModel);
        $withdrawNotice->sendMessage();
    }

    /**
     * 提现打款支付
     * @param WithdrawPayingEvent $event
     */
    public function withdrawPay($event)
    {
        $withdrawModel = $event->getWithdrawModel();

        (new MessageService($withdrawModel))->payedNotice();
        $arrivalNotice = new WithDrawArrivalNotice($withdrawModel);
        $arrivalNotice->sendMessage();
    }

    /**
     * 提心打款到账
     * @param WithdrawPayedEvent $event
     */
    public function withdrawArrival($event)
    {
        $withdrawModel = $event->getWithdrawModel();

        (new MessageService($withdrawModel))->arrivalNotice();
    }

    public function subscribe($events)
    {
        $events->listen(
            WithdrawAppliedEvent::class,
            self::class . '@withdrawApplied'
        );
        $events->listen(
            WithdrawAuditedEvent::class,
            self::class . '@withdrawCheck'
        );
        $events->listen(
            WithdrawPayingEvent::class,
            self::class . '@withdrawPay'
        );
        $events->listen(
            WithdrawPayedEvent::class,
            self::class . '@withdrawArrival',
            -999
        );
    }
}
