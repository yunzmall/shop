<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/9
 * Time: 14:13
 */

namespace app\frontend\modules\memberCart\services;


use app\common\modules\memberCart\MemberCartCollection;
use app\common\exceptions\AppException;
use app\common\models\BaseModel;
use app\common\models\Member;
use app\common\services\Plugin;
use app\framework\Http\Request;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\services\OrderService;

class DispatchOrderCollection extends MemberCartCollection
{
    /**
     * 根据自身创建plugin_id对应类型的订单,当member已经实例化时传入member避免重复查询
     * @param Member|null $member
     * @param Plugin|null $plugin
     * @param Request $request
     * @return PreOrder|bool
     * @throws AppException
     * @throws \Exception
     */
    public function getGroupOrder(Plugin $plugin = null,$member = null, $request = null)
    {
        $request = $request ?: request();
        if ($this->isEmpty()) {
            return false;
        }
        if (!isset($member)) {
            $member = $this->getMember();
        }

        $orderGoodsCollection = OrderService::getOrderGoods($this);
        /**
         * @var PreOrder $order
         */
        $app = $plugin && $plugin->app()->bound('OrderManager') ? $plugin->app() : app();

        $order = $app->make('OrderManager')->make('PreOrder');

        $order->setRequest($request);
        $order->setMember($member);
        $order->beforeCreating();
        $order->setOrderGoods($orderGoodsCollection);
        $order->afterCreating();
//        $order->init($member, $orderGoodsCollection, $request);

        return $order;
    }
}