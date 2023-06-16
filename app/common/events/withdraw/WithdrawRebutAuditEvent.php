<?php
/**
 * 提现驳回审核事件
 *
 * Created by PhpStorm.
 *
 * Date: 2023/1/4
 */

namespace app\common\events\withdraw;


use app\common\events\Event;
use app\common\models\Withdraw;

class WithdrawRebutAuditEvent extends Event
{
    private $withdraw;

    private $rebut_ids;

    public function __construct(Withdraw $withdraw,$rebut_ids)
    {
        $this->withdraw = $withdraw;
        $this->rebut_ids = $rebut_ids;
    }


    public function getWithdrawModel()
    {
        return $this->withdraw;
    }

    public function getRebutIds()
    {
        return $this->rebut_ids;
    }
}
