<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 11:18
 */

namespace app\frontend\modules\order\serviceFee;


class TestServiceFee extends BaseOrderServiceFee
{

    protected $code = 'test-aaa';

    protected $name = '测试服务费';


    protected function _getAmount()
    {
        return 10;
    }

    /**
     * 是否开启
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * 是否选中
     * @return bool
     */
    public function isChecked()
    {
      return parent::isChecked();
    }

    /**
     * 是否显示
     */
    public function isShow()
    {
       return parent::isShow();
    }
}