<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/10
 * Time: 9:27
 */

namespace app\frontend\modules\memberCart\services;

use app\common\exceptions\AppException;
use app\framework\Http\Request;
use app\common\models\Member;
use app\common\models\MemberCart;
use app\common\services\Plugin;
use app\framework\Database\Eloquent\Collection;
use app\frontend\modules\memberCart\models\DispatchTypeOrder;
use app\frontend\modules\memberCart\models\Goods;

class DispatchTypeGoodsCollection extends Collection
{
    /**
     * @var [Goods]
     */
    protected $items;

    /**
     * 载入管理模型
     * @return $this
     */
    public function loadRelations()
    {
        $with = ['hasOnePrivilege', 'hasOneGoodsDispatch', 'hasOneSale'];

        $this->expansionLoad($with);

        return $this;
    }

    /**
     * 将商品集合按groupId分组
     * @return static
     */
    public function groupByGroupId()
    {
        $groups = $this->groupBy(function (Goods $goods) {
            return $this->getGroupId($goods);
        });
        $groups->map(function (DispatchTypeGoodsCollection $dispatchTypeGoodsCollection) {
            return $dispatchTypeGoodsCollection;
        });
        return $groups;
    }


    /**
     * 根据自身创建plugin_id对应类型的订单,当member已经实例化时传入member避免重复查询
     * @param Member|null $member
     * @param null $request
     * @return DispatchTypeOrder|bool
     */
    public function getOrder(Member $member = null, $request = null)
    {
        $request = $request ?: request();
        if ($this->isEmpty()) {
            return false;
        }
        if (!isset($member)) {
            $member = $this->getMember();
        }


        $dispatchTypeOrder = new DispatchTypeOrder();

        $dispatchTypeOrder->init($member,$this, $request);

        return $dispatchTypeOrder;
    }

    /**
     * 所属用户对象
     * @return Member
     */
    public function getMember()
    {
        return Member::current();
    }

    /**
     * @return null
     * @throws AppException
     */
    public function getPluginId()
    {

        return $this->first()->plugin_id;
    }

    /**
     * 根据商品plugin_id 分组id
     * @return int
     */
    public function getGroupId(Goods $goods)
    {

        return $goods->plugin_id;

//        if (!$goods->getPlugin()) {
//            return 0;
//        }
//        if (!$goods->getPlugin()->app()->bound(MemberCart::class)) {
//            return $goods->getPlugin()->getId();
//        }
//        return $goods->getPlugin()->app()->make(MemberCart::class, [['goods_id'=> $goods->id,'total'=>1,'option_id'=> 0]])->getGroupId();
    }
}