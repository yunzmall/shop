<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/22
 * Time: 14:28
 */

namespace app\frontend\modules\finance\listeners;

use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use Illuminate\Foundation\Bus\DispatchesJobs;
use app\common\events\order\AfterOrderCanceledEvent;

class OrderDeductionRollback
{
    use DispatchesJobs;

    /**
     * @var
     */
    private $orderModel;

    public function subscribe($events)
    {
        // 订单关闭 积分抵扣回滚
        $events->listen(
            AfterOrderCanceledEvent::class, self::class. '@orderCancel'
        );

    }


    public function orderCancel(AfterOrderCanceledEvent $event)
    {

        $this->orderModel = $event->getOrderModel();

        //订单关闭返还抵扣的余额
        $this->orderBalanceRollback();

    }

    protected function orderBalanceRollback()
    {
        if (! \Setting::get('finance.balance.balance_deduct_rollback')) {
            return;
        }

        $balanceDeduction = $this->getOrderPointDeduction('balance');
        if (!$balanceDeduction) {
            return;
        }
        $this->balanceRollback($balanceDeduction);
    }

    protected function balanceRollback($coin)
    {
        $data = [
            'member_id' => $this->orderModel->uid,
            'remark' => '订单：' . $this->orderModel->order_sn . '关闭，返还余额抵扣余额' . $coin,
            'relation' =>  $this->orderModel->order_sn,
            'operator' => ConstService::OPERATOR_ORDER,
            'operator_id' =>  $this->orderModel->id,
            'change_value' => $coin,
        ];
        $result = (new BalanceChange())->cancelDeduction($data);
        return $result;
    }

    private function getOrderPointDeduction($code)
    {
        $coin = 0;
        if ($this->orderModel->deductions) {
            foreach ($this->orderModel->deductions as $key => $deduction) {
                if ($deduction['code'] == $code) {
                    $coin = $deduction['coin'];
                    break;
                }
            }
        }

        return $coin;
    }
}
