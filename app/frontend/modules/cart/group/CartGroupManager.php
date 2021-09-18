<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/5/27
 * Time: 9:36
 */

namespace app\frontend\modules\cart\group;

use app\frontend\modules\cart\models\MemberCart;
use Illuminate\Container\Container;

class CartGroupManager extends Container
{
    public function __construct()
    {

    }

    public function getCartGroup(MemberCart $memberCart)
    {
        $pluginConfigCollection = collect();
        foreach ($this->getBindings() as $key => $value) {
            $pluginConfigCollection->push($this->make($key, [$memberCart]));
        }
        // 按权重排序
        $pluginGroups = $pluginConfigCollection->sortBy(function (BaseShopGroup $pluginGroup) {
            return $pluginGroup->getWeight();
        });

        foreach ($pluginGroups as $key => $pluginGroup) {

            /**
             * @var BaseShopGroup $pluginGroup
             */
            if ($pluginGroup->validate()) {

                return $pluginGroup;
            }
        }

        return new DefaultShopGroup($memberCart);
    }
}