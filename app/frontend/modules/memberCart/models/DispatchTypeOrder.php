<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/6/10
 * Time: 11:33
 */

namespace app\frontend\modules\memberCart\models;


use app\common\models\BaseModel;

use app\common\models\Member;
use app\framework\Database\Eloquent\Collection;
use app\frontend\modules\memberCart\services\DispatchTypeGoodsCollection;
use Illuminate\Http\Request;

class DispatchTypeOrder extends BaseModel
{

    protected $request;

    public function __construct(array $attributes = [])
    {
        parent::__construct([]);
    }


    /**
     * 相同类型商品相交的配送方式
     * @return mixed
     */
    public function getDispatchTypeIds()
    {
        $result =  $this->goods->map(function (Goods $aGoods) {
            return $aGoods->goodsDispatchTypeIds();
        })->reduce(function ($result, $item) {

            if ($result && $item) {
                $intersect = array_intersect($result, $item);

                return $intersect?: $result;
            }
            return array_merge($result,$item);
        }, []);

        return $result;
    }


    public function init(Member $member, DispatchTypeGoodsCollection $goodsCollection, Request $request)
    {
        $this->setRequest($request);

        $this->setMember($member);

        $this->setGoods($goodsCollection);

        $this->initAttributes();



        return $this;
    }

    /**
     * 初始化属性
     */
    protected function initAttributes()
    {
        $attributes = array(
            'uid' => $this->member->uid,
            'uniacid' => \YunShop::app()->uniacid,
            'is_virtual' => $this->isVirtual(),//是否是虚拟商品订单
            'plugin_id' => $this->goods->getPluginId(),
            'dispatch_type_ids' =>  $this->getDispatchTypeIds(),
        );

        $attributes = array_merge($this->getAttributes(), $attributes);
        $this->setRawAttributes($attributes);

    }

    /**
     * 统计订单商品是否有虚拟商品
     * @return bool
     */
    public function isVirtual()
    {
        if ($this->is_virtual == 1) {
            return true;
        };
        return $this->hasVirtual();
    }

    /**
     * 订单商品集合中包含虚拟物品
     * @return bool true 包含 false 不包含
     */
    protected function hasVirtual() {
        $bool = $this->goods->contains(function ($aGoods) {
            return $aGoods->type == 1;
        });
        return !$bool;
    }

    /**
     * 获取request对象
     * @return Request
     */
    public function getRequest()
    {
        if (!isset($this->request)) {
            $this->request = request();
        }
        return $this->request;
    }

    /**
     * 载入订单商品集合
     * @param  $orderGoods
     */
    public function setGoods(DispatchTypeGoodsCollection $goods)
    {
        $this->setRelation('goods', $goods);
    }


    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $member
     */
    public function setMember($member)
    {
        $this->setRelation('member', $member);
    }
}