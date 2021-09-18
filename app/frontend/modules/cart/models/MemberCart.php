<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/7
 * Time: 10:18
 */

namespace app\frontend\modules\cart\models;


use app\frontend\modules\cart\group\BaseShopGroup;
use app\frontend\modules\cart\group\DefaultShopGroup;

class MemberCart extends \app\frontend\models\MemberCart
{

    protected $pluginGroup;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * todo 这里想要兼容plugin_id = 0，但又是特殊商品的
     * 不能根据商品的plugin_id来进行分组，需要重新写配置根据判断得知商品属于哪种插件
     * @return BaseShopGroup
     */
    public function getPluginGroup()
    {

        if (!isset($this->pluginGroup)) {
            $this->pluginGroup = $this->_getPluginGroup();
        }

        return $this->pluginGroup;
    }

    protected function _getPluginGroup()
    {


        $cartGroupManager = app('CartContainer')->make('CartGroupManager');
        /**
         * 根据商品获取不同的 CartGroup分组
         * @var $cartGoods CartGoods
         */
        $cartGroup = $cartGroupManager->getCartGroup($this);


        return $cartGroup;
    }

    public function getPluginApp()
    {
        if ($this->getPluginGroup()->getPluginPathName()) {
           return app('plugins')->getPlugin($this->getPluginGroup()->getPluginPathName());
        }

        return null;
    }

    /**
     * 获取购物车分组id
     * @return int
     */
    public function getGroupId()
    {
        return $this->getPluginGroup()->getGroupId();

//        if (!$this->goods->getPlugin()) {
//            return 0;
//        }
//        if (!$this->goods->getPlugin()->app()->bound('CartContainer')) {
//            return 0;
//        }
//
//        return $this->goods->getPlugin()->app()->make('CartContainer')->make('CartGroup',[$this])->getGroupId();
    }

    public function aaa()
    {
        $pluginGroups = collect();

        foreach (\app\common\modules\shop\ShopConfig::current()->get('member-cart.group') as $configItem) {

            $pluginGroups->push(call_user_func($configItem['class'], $this));
        }

        $pluginGroups = $pluginGroups->sortBy(function (BaseShopGroup $pluginGroup) {
            // 按照weight排序
            return $pluginGroup->getWeight();
        });

        foreach ($pluginGroups as $pluginGroup) {

            /**
             * @var BaseShopGroup $pluginGroup
             */
            if ($pluginGroup->validate()) {

                return $pluginGroup;
            }
        }

        return new DefaultShopGroup($this);
    }
}