<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/7/25
 * Time: 下午7:33
 */

namespace app\frontend\models\order;

use app\backend\modules\order\services\models\ExcelModel;
use app\common\exceptions\MinOrderDeductionNotEnough;
use app\common\models\order\OrderDeduction;
use app\common\models\VirtualCoin;
use app\frontend\models\MemberCoin;
use app\frontend\modules\deduction\models\Deduction;
use app\frontend\modules\deduction\OrderGoodsDeductionCollection;
use app\frontend\modules\deduction\orderGoods\PreOrderGoodsDeduction;
use app\frontend\modules\order\models\PreOrder;

/**
 * 订单抵扣类
 * Class PreOrderDeduction
 * @package app\frontend\models\order
 * @property int uid
 * @property int coin
 * @property int amount
 * @property int name
 * @property int code
 */
class PreOrderDeduction extends OrderDeduction
{
    protected $appends = ['checked'];
    /**
     * @var PreOrder
     */
    public $order;
    /**
     * @var Deduction
     */
    private $deduction;
    /**
     * @var MemberCoin
     */
    private $memberCoin;
    /**
     * @var OrderGoodsDeductionCollection
     */
    private $orderGoodsDeductionCollection;
    /**
     * 订单实付使用商品抵扣
     * @var VirtualCoin
     */
    private $usablePoint;

    /**
     * 实际使用运费抵扣
     * @var VirtualCoin
     */
    protected $usableFreightDeduction;

    /**
     * @param Deduction $deduction
     * @param PreOrder $order
     * @param OrderGoodsDeductionCollection $orderGoodsDeductionCollection
     */
    public function init(
        Deduction $deduction,
        PreOrder $order,
        OrderGoodsDeductionCollection $orderGoodsDeductionCollection)
    {
        $this->deduction = $deduction;

        $this->setOrder($order);
        $this->setOrderGoodsDeductionCollection($orderGoodsDeductionCollection);
        $this->orderGoodsDeductionCollection->each(function (PreOrderGoodsDeduction $orderGoodsDeduction) {
            $orderGoodsDeduction->setOrderDeduction($this);
        });

    }

    public function getUidAttribute()
    {
        return $this->order->uid;
    }

    public function getCodeAttribute()
    {
        return $this->getCode();
    }

    public function getNameAttribute()
    {
        return $this->getName();
    }

    /**
     * 最终抵扣值 = (最低抵扣+最高商品抵扣+运费抵扣)
     * @return float|int
     * @throws \app\common\exceptions\AppException
     */
    public function getCoinAttribute()
    {
        //todo 为了保证显示不超过两位小数，这里使用了四舍五入
        $coin = $this->getMinDeduction()->getCoin() + $this->getUsablePoint()->getCoin() + $this->getUsableFreightDeduction()->getCoin();

        //return $coin;

        return round($coin,2,PHP_ROUND_HALF_EVEN);
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getAmountAttribute()
    {
        //return $this->getAmount();

        return round($this->getAmount(),2,PHP_ROUND_HALF_EVEN);
    }

    /**
     * 最终抵扣金额 = (最低抵扣+最高商品抵扣+运费抵扣)
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getAmount()
    {
//        dump($this->getMinDeduction()->getMoney(),$this->getUsablePoint()->getMoney(),$this->getUsableFreightDeduction()->getMoney());

        return $this->getMinDeduction()->getMoney() + $this->getUsablePoint()->getMoney() + $this->getUsableFreightDeduction()->getMoney();
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Deduction
     */
    public function getDeduction()
    {
        return $this->deduction;
    }

    /**
     * @param PreOrder $order
     */
    private function setOrder(PreOrder $order)
    {
        $this->order = $order;
    }

    /**
     * 下单时此抵扣可选
     * @return bool
     */
    public function deductible()
    {
        return $this->amount > 0;
    }

    /**
     * 实例化并绑定所有的订单商品抵扣实例,集合  并将集合绑定在订单抵扣上
     * @param OrderGoodsDeductionCollection $orderGoodsDeductionCollection
     */
    private function setOrderGoodsDeductionCollection(OrderGoodsDeductionCollection $orderGoodsDeductionCollection)
    {
        $this->orderGoodsDeductionCollection = $orderGoodsDeductionCollection;
    }


    /**
     * 下单用户此抵扣对应虚拟币的余额
     * @return MemberCoin
     */
    public function getMemberCoin()
    {
        if (isset($this->memberCoin)) {
            return $this->memberCoin;
        }
        $code = $this->getCode();

        return \app\frontend\modules\deduction\EnableDeductionService::getInstance()->getMemberCoin($code);
        //return app('CoinManager')->make('MemberCoinManager')->make($code, [$this->order->belongsToMember]);
    }

    /**
     * 此抵扣对应的虚拟币
     * @return VirtualCoin
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function newCoin()
    {
        return app('CoinManager')->make($this->getCode());
    }


    public function openFreightDeduction()
    {
        return $this->getDeduction()->isEnableDeductDispatchPrice();
    }

    /**
     * 订单剩余运费，最高抵扣金额
     * @return VirtualCoin
     */
    public function getMaxFreightPriceDeduction()
    {
        $result = $this->newCoin();

        trace_log()->freight("监听抵扣", $this->getDeduction()->getName() . '运费抵扣开启状态' . $this->openFreightDeduction());
        //开关
        if ($this->openFreightDeduction()) {

            $amount = $this->order->getFreightManager()->getFinalFreightAmount();

            $result->setMoney($amount);
            trace_log()->freight("监听抵扣运费", $this->getDeduction()->getName() . '运费可抵扣金额');
        }
        return $result;
    }




    /**
     * 订单实际可用的此抵扣运费
     * @return VirtualCoin
     */
    public function getUsableFreightDeduction()
    {
        if (!isset($this->usableFreightDeduction)) {

            trace_log()->deduction('开始订单抵扣运费', "{$this->getName()} 计算可用运费抵扣金额");

            $this->usableFreightDeduction = $this->newCoin();

            // 购买者不存在虚拟币记录
            if (!$this->getMemberCoin()) {
                trace_log()->deduction('订单抵扣运费', "{$this->getName()} 用户没有对应虚拟币");
                return $this->usableFreightDeduction;
            }

            if (!$this->openFreightDeduction()) {
                trace_log()->deduction('订单抵扣运费', "{$this->getName()} 未开启运费抵扣");
                return $this->usableFreightDeduction;
            }

            //订单运费抵扣金额 不能超过订单运费当前抵扣项之前的金额
            $deductionAmount = min(
                $this->order->getDispatchAmount(),
                $this->getMaxFreightPriceDeduction()->getMoney()
            );


            //如果资产设置的转化比例小数点过低会，造成资产小数位数超过两位这样会造成 抵扣值和抵扣金额不对应
            $memberMaxUsableCoin = floor($this->getMemberCoin()->getMaxUsableCoin()->getMoney() * 100) / 100;

            // 运费抵扣金额（用户可用虚拟币 - 最低抵扣金额） 与运费抵扣金额虚拟币的最小值
            $amount = min(
                $memberMaxUsableCoin - $this->getMinDeduction()->getMoney(),
                $deductionAmount
            );


            //判断是否开启运费抵扣,开启运费抵扣需要把抵扣添加到运费的抵扣节点
            $this->order->getFreightManager()->pushDeductionPricePipe($this);

            $this->usableFreightDeduction = $this->newCoin()->setMoney($amount);

            trace_log()->deduction("订单抵扣运费", "{$this->name} 可抵扣{$this->usableFreightDeduction->getMoney()}元");
        }

        return $this->usableFreightDeduction;
    }

    /**
     * 订单中实际可用的此抵扣
     * @return $this|VirtualCoin
     * @throws \app\common\exceptions\AppException
     */
    public function getUsablePoint()
    {

        if (!isset($this->usablePoint)) {

            trace_log()->deduction('开始订单抵扣', "{$this->getName()} 计算可用金额");

            $this->usablePoint = $this->newCoin();

            // 购买者不存在虚拟币记录
            if (!$this->getMemberCoin()) {
                trace_log()->deduction('订单抵扣', "{$this->getName()} 用户没有对应虚拟币");
                return $this->usablePoint;
            }

            // 商品金额抵扣 不能超过订单当前抵扣项之前(去除运费)的金额
            //todo 这个有问题，当没有设置最高抵扣时只设置了最低抵扣 这里（最高-最低）减了会变为负数，为什么之前为负数也能显示出抵扣呢？？？
            $deductionAmount = min(
                $this->order->getPriceBefore($this->getCode() . 'RestDeduction') - $this->order->getDispatchAmount(),
                $this->getMaxDeduction()->getMoney() - $this->getMinDeduction()->getMoney()
            );

            //todo 修复抵扣金额为负数，这样就一定要保证最高抵扣的设置要大于最低抵扣，如小于可能会有问题
            //只会显示最低抵扣的金额
            $deductionAmount = max($deductionAmount, 0);

            //如果资产设置的转化比例小数点过低会，造成资产小数位数超过两位这样会造成 抵扣值和抵扣金额不对应
            $memberMaxUsableCoin = floor($this->getMemberCoin()->getMaxUsableCoin()->getMoney() * 100) / 100;

            // 用户可用虚拟币-最低抵扣-运费抵扣 与订单抵扣虚拟币的最小值  todo 如果以后需要一种抵扣币抵扣两次时,会产生bug
            $amount = min(
                $memberMaxUsableCoin - $this->getMinDeduction()->getMoney() - $this->getUsableFreightDeduction()->getMoney(),
                $deductionAmount
            );

            //整数抵扣
            $handleType = $this->getDeduction()->getAffectDeductionAmount();
            if ($handleType == 'integer') {
                $amount = intval($amount);
            }

            $this->usablePoint = $this->newCoin()->setMoney($amount);
            trace_log()->deduction("订单抵扣", "{$this->name} 可抵扣{$this->usablePoint->getMoney()}元");
        }

        return $this->usablePoint;
    }

    /**
     * 获取订单商品占用的抵扣金额
     * @return float|int
     */
    public function getOrderGoodsDeductionAmount()
    {

        //blank 这里不能加运费，运费不分摊到商品金额里
        $amount = ($this->getMaxOrderGoodsDeduction()->getMoney() / $this->getMaxDeduction()->getMoney()) * $this->amount;
//dump($this->getMaxOrderGoodsDeduction()->getMoney(), $this->getMaxDeduction()->getMoney(), $this->getUsableFreightDeduction()->getMoney());
//dump($this->amount, $amount, '---');
//        $amount = ($this->getMaxOrderGoodsDeduction()->getMoney() / ($this->getMaxDeduction()->getMoney()+$this->getMaxDispatchPriceDeduction()->getMoney())) * $this->amount;
        return $amount;
    }

    /**
     * @var VirtualCoin
     */
    private $maxDeduction;

    /**
     * 订单中此抵扣可用最大值
     * @return VirtualCoin
     */
    private function getMaxDeduction()
    {
        if (!isset($this->maxDeduction)) {
            $this->maxDeduction = $this->getMaxOrderGoodsDeduction();
            trace_log()->deduction('订单抵扣', "{$this->getName()} 计算最大抵扣{$this->maxDeduction}");
        }

        return $this->maxDeduction;

    }

    /**
     * @var VirtualCoin
     */
    private $minDeduction;

    /**
     * 订单中此抵扣可用最小值
     * @return VirtualCoin
     */
    public function getMinDeduction()
    {
        if (!isset($this->minDeduction)) {
            $this->minDeduction = $this->getMinOrderGoodsDeduction();

            trace_log()->deduction('订单抵扣', "{$this->getName()} 计算最小抵扣{$this->minDeduction->getMoney()}元");

        }

        return $this->minDeduction;
    }

    /**
     * 最多可抵扣商品金额的虚拟币
     * 累加所有订单商品的可用虚拟币
     * @return VirtualCoin
     */
    public function getMaxOrderGoodsDeduction()
    {
        return $this->getOrderGoodsDeductionCollection()->getUsablePoint();
    }

    /**
     * 最低抵扣商品金额的虚拟币
     * 累加所有订单商品的可用虚拟币
     * @return VirtualCoin
     */
    public function getMinOrderGoodsDeduction()
    {
        return $this->getOrderGoodsDeductionCollection()->getMinPoint();
    }

    /**
     * @return OrderGoodsDeductionCollection
     */
    public function getOrderGoodsDeductionCollection()
    {
        return $this->orderGoodsDeductionCollection;

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getDeduction()->getCode();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getDeduction()->getName();
    }

    /**
     * @return bool
     */
    public function getCheckedAttribute()
    {
        return $this->isChecked();
    }

    private $isChecked;

    public function setChecked()
    {
        $this->isChecked = true;
    }
    private $mustBeChecked;
    /**
     * 必须选中
     * @return bool
     */
    public function mustBeChecked()
    {
        if(!$this->mustBeChecked){
            // 设置了最低抵扣必须选中
            return $this->getOrderGoodsDeductionCollection()->hasMinDeduction() > 0;
        }
        return $this->mustBeChecked;
    }

    /**
     * 选择了此抵扣
     * @return bool
     */
    public function isChecked()
    {
        if (!isset($this->isChecked)) {
        	
            if (!$this->order->uid) {
                return $this->isChecked = false;
            }
            if ($this->mustBeChecked()) {
                // 必须选中
                $this->isChecked = true;
            } elseif (!$this->order->getRequest()->no_deduction_ids &&
	            \Setting::get('point.set')['default_deduction'] &&
	            ($this->order->plugin_id == 0 || $this->order->plugin_id == 92)){//no_deduction_ids 新添参数,状态为空和设置为1 则开启默认抵扣按钮
            	$this->isChecked = $this->getCode() == 'point';//添加默认开启积分抵扣 ,暂时只做积分的
            }else {
                // 用户选中
                $deduction_codes = $this->order->getParams('deduction_ids');

                if (!is_array($deduction_codes)) {
                    $deduction_codes = json_decode($deduction_codes, true);
                    if (!is_array($deduction_codes)) {
                        $deduction_codes = explode(',', $deduction_codes);
                    }
                }
                $this->isChecked = in_array($this->getCode(), $deduction_codes);
            }
        }
        return $this->isChecked;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->code = (string)$this->code;
        $this->name = (string)$this->name;
        $this->amount = sprintf('%.2f', $this->amount);
        $this->coin = sprintf('%.2f', $this->coin);
        return parent::toArray();
    }

    /**
     * @throws \app\common\exceptions\AppException
     */
    public function lock()
    {
        // 抵扣被选中后,锁定要使用的虚拟币额度
        $this->getMemberCoin()->lockCoin($this->coin);
    }

    /**
     * @return bool
     */
    public function beforeSaving()
    {
        if (!$this->isChecked() || ($this->getOrderGoodsDeductionCollection()->getUsablePoint() <= 0 && $this->getUsableFreightDeduction()->getMoney() <= 0)) {
            return false;
        }

        //抵扣金额小于0不保存
        if (bccomp($this->amount,0,2) !== 1) {
            return false;
        }

        $this->getMemberCoin()->consume($this->newCoin()->setMoney($this->amount), ['order_sn' => $this->order->order_sn]);
        $this->code = (string)$this->code;
        $this->name = (string)$this->name;
        $this->amount = sprintf('%.2f', $this->amount);
        $this->coin = sprintf('%.2f', $this->coin);
        return parent::beforeSaving();
    }

    /**
     * @throws MinOrderDeductionNotEnough
     */
    public function validateCoin()
    {
        // 验证最低抵扣大于可用抵扣
        if (bccomp($this->getMemberCoin()->getMaxUsableCoin()->getMoney(), $this->getMinDeduction()->getMoney(),2) === -1) {
            throw new MinOrderDeductionNotEnough("会员[{$this->getName()}]抵扣余额可抵扣金额{$this->getMemberCoin()->getMaxUsableCoin()->getMoney()}元,不满足最低抵扣金额{$this->getMinDeduction()->getMoney()}元");
        }
//
//        if ($this->getMemberCoin()->getMaxUsableCoin()->getMoney() < $this->getMinDeduction()->getMoney()) {
//            throw new MinOrderDeductionNotEnough("会员[{$this->getName()}]抵扣余额可抵扣金额{$this->getMemberCoin()->getMaxUsableCoin()->getMoney()}元,不满足最低抵扣金额{$this->getMinDeduction()->getMoney()}元");
//        }

        //最低抵扣 + 运费抵扣 必须大于可用抵扣
//        $mustDeductionMoney = $this->getMinDeduction()->getMoney() + $this->getUsableFreightDeduction()->getMoney();
//        if (bccomp($this->getMemberCoin()->getMaxUsableCoin()->getMoney(), $mustDeductionMoney,2) === -1) {
//            throw new MinOrderDeductionNotEnough("会员[{$this->getName()}]抵扣余额可抵扣金额{$this->getMemberCoin()->getMaxUsableCoin()->getMoney()}元,不满足{$mustDeductionMoney}元(最低抵扣[{$this->getMinDeduction()->getMoney()}] + 运费抵扣[{$this->getUsableFreightDeduction()->getMoney()}])");
//        }

    }
}