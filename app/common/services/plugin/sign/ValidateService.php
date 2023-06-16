<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021-05-17
 * Time: 13:55
 */

namespace app\common\services\plugin\sign;



abstract class ValidateService
{
    abstract function validate();
    abstract function openSettingFun();

    public function __construct()
    {

    }

    protected function checkShopSignOpen()
    {
        return app('plugins')->isEnabled('shop-esign') ?: false;
    }
}