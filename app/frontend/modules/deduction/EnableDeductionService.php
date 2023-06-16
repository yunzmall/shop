<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/3/1
 * Time: 14:51
 */

namespace app\frontend\modules\deduction;

use app\frontend\models\Member;
use app\frontend\models\MemberCoin;
use app\common\exceptions\AppException;
use app\common\modules\orderGoods\models\PreOrderGoods;
use app\framework\Database\Eloquent\Collection;
use app\frontend\models\order\PreOrderDeduction;
use app\frontend\models\order\PreOrderDiscount;
use app\frontend\modules\deduction\models\Deduction;
use app\frontend\modules\order\models\PreOrder;

class EnableDeductionService
{

    protected $deductions;

    protected $instances = [];

    protected static $instance = null;

    private function __construct(){}

    /**
     * 单例缓存
     * @return null|self
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance =  new self();

        }
        return self::$instance;
    }

    /**
     * 开启的抵扣项
     * @return Collection
     */
    public function getEnableDeductions(PreOrder $preOrder)
    {
        //blank not deduction
        if ($preOrder->isDeductionDisable()) {
            trace_log()->deduction('订单关闭的抵扣类型','');
            return collect();
        }

        $deductions = $this->getDeductions($preOrder);

        $sort = array_flip($preOrder->getParams('deduction_ids'));
        $deductions = $deductions->sortBy(function ($deduction) use ($sort) {
            return array_get($sort, $deduction->code, 999);
        });

        return $deductions;
    }

    protected function getDeductions(PreOrder $preOrder)
    {
        if (!isset($this->deductions)) {

            /**
             * 商城开启的抵扣
             * @var Collection $deductions
             */
            $deductions = Deduction::getEnable();

            trace_log()->deduction('开启的抵扣类型', $deductions->pluck('code')->toJson());
            if ($deductions->isEmpty()) {
                return collect();
            }
            // 过滤调无效的
            $deductions = $deductions->filter(function (Deduction $deduction) use ($preOrder) {

                /**
                 * @var MemberCoin $memberCoin
                 */
                $memberCoin = $deduction->memberCoin($preOrder->belongsToMember);
                if ($memberCoin) {
                    $this->setMemberCoin($deduction->getCode(), $memberCoin);
                }

                /**
                 * @var Deduction $deduction
                 */
                return $deduction->valid() && $memberCoin && $memberCoin->useLimit();
            });

            $this->deductions = $deductions;
        }

        return $this->deductions;
    }

    protected function setMemberCoin($code, $class)
    {
        if (!$this->instances[$code]) {
            $this->instances[$code] = $class;
        }
    }

    //缓存抵扣用户模型
    //解决分单每个订单都独立获取用户抵扣值模型问题
    public function getMemberCoin($code)
    {
        if ($this->instances[$code]) {
            return $this->instances[$code];
        }
        trace_log()->deduction('抵扣不存在：'.$code,"");
        //这里以防万一没有缓存，重新获取
        return app('CoinManager')->make('MemberCoinManager')->make($code, [Member::current()]);
    }
}