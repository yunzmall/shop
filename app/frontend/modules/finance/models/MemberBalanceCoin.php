<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/22
 * Time: 14:00
 */

namespace app\frontend\modules\finance\models;

use app\common\exceptions\AppException;
use app\common\models\VirtualCoin;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\PointService;
use app\frontend\models\MemberCoin;

class MemberBalanceCoin extends MemberCoin
{
    /**
     * 获取最多可用积分
     * @return mixed
     */
    public function getMaxUsableCoin()
    {
        return (new BalanceCoin())->setCoin($this->member->credit2);
    }

    public function lockCoin($coin)
    {
        if (bccomp($coin, $this->member->credit2) == 1) {
            $name = (new BalanceCoin())->getName();

            throw new AppException("用户(ID:{$this->member->uid})" . $name . "不足");
        }

        $this->member->credit2 -= $coin;
    }

    /**
     * @param VirtualCoin $coin
     * @param $data
     * @return bool
     * @throws \app\common\exceptions\ShopException
     */
    function consume(VirtualCoin $coin, $data)
    {
        $data = [
            'member_id' => $this->member->uid,
            'remark' => '订单[' . $data['order_sn'] . ']抵扣[' . $coin->getMoney() . ']元',
            'relation' => $data['order_sn'],
            'operator' => ConstService::OPERATOR_MEMBER,
            'operator_id' =>  $this->member->uid,
            'change_value' => $coin->getCoin(),
        ];
        $result = (new BalanceChange())->deduction($data);

        return $result;
    }
}