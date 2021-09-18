<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/21
 * Time: 10:26
 */

namespace app\frontend\modules\finance\deduction;

use app\common\models\Goods;
use app\frontend\modules\deduction\DeductionSettingInterface;
use app\frontend\modules\deduction\DeductionSettingManagerInterface;
use app\frontend\modules\finance\deduction\deductionSettings\BalanceGoodsDeductionSetting;
use app\frontend\modules\finance\deduction\deductionSettings\BalanceShopDeductionSetting;
use Illuminate\Container\Container;

class BalanceDeductionSettingManager extends Container implements DeductionSettingManagerInterface
{
    public function __construct()
    {
        /**
         * 余额抵扣商品设置
         */
         $this->bind('goods', function (BalanceDeductionSettingManager $deductionSettingManager, array $params) {
             return new BalanceGoodsDeductionSetting($params[0]);
         });
        /**
         * 余额抵扣商城设置
         */
        $this->bind('shop', function (BalanceDeductionSettingManager $deductionSettingManager, array $params) {
            return new BalanceShopDeductionSetting();
        });
    }

    /**
     * @param Goods $goods
     * @return BalanceDeductionSettingCollection
     */
    public function getDeductionSettingCollection(Goods $goods)
    {
        $deductionSettingCollection = collect();
        foreach ($this->getBindings() as $key => $value) {
            $deductionSettingCollection->push($this->make($key, [$goods]));
        }
        // 按权重排序
        $deductionSettingCollection = $deductionSettingCollection->sortBy(function (DeductionSettingInterface $deductionSetting) {
            return $deductionSetting->getWeight();
        });

        return new BalanceDeductionSettingCollection($deductionSettingCollection);
    }
}