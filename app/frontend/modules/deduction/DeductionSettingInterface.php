<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/10/13
 * Time: 上午11:01
 */

namespace app\frontend\modules\deduction;

/**
 * 抵扣设置
 * Interface DeductionSetting
 * @package app\frontend\modules\deduction
 */
interface DeductionSettingInterface
{
    /**
     * @return int
     */
    public function getWeight();

    /**
     * @return bool
     */
    public function isEnableDeductDispatchPrice();

    /**
     * @return bool 已禁用
     */
    public function isMaxDisable();
    public function isMinDisable();
    public function isDispatchDisable();

    /**
     * 最高抵扣比例和固定金额
     * @return mixed
     */
    public function getMaxFixedAmount();
    public function getMaxPriceProportion();
    /**
     * 最高抵扣
     * 根据这个方法判断实例化哪个金额类
     * @return mixed
     */
    public function getMaxDeductionType();


    /**
     * 抵扣计算方式类型，用于判断返回计算金额
     * @return mixed
     */
    public function getDeductionAmountType();

    /**
     * 最低抵扣比例和固定金额
     * @return mixed
     */
    public function getMinFixedAmount();
    public function getMinPriceProportion();
    /**
     * 最低抵扣
     * 根据这个方法判断实例化哪个金额类
     * @return mixed
     */
    public function getMinDeductionType();


    /**
     * 影响抵扣金额设置
     * @return mixed
     */
    public function getAffectDeductionAmount();
}