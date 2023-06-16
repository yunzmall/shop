<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
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