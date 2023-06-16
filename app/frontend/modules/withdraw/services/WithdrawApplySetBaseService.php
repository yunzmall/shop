<?php

namespace app\frontend\modules\withdraw\services;

abstract class WithdrawApplySetBaseService
{
    final public function __set($name,$value)
    {
        if (!property_exists($this,$name)) {
            throw new \Exception('属性不存在!');
        }
        $this->$name = $value;
    }

    /**
     * 判断该提现方式是否开启
     * @param $payWay //提现方式
     * @return bool
     */
    abstract function checkPayWay($payWay):bool;

    //todo 获取可取提现方式按钮也可移植过来
}