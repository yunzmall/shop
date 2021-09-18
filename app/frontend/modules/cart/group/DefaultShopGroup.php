<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/7
 * Time: 14:29
 */

namespace app\frontend\modules\cart\group;



class DefaultShopGroup extends BaseShopGroup
{

    //按插件分组ID
    public function getGroupId()
    {
       return 0;
    }

    public function validate()
    {
       return true;
    }

    public function getPluginPathName()
    {
        return null;
    }
}