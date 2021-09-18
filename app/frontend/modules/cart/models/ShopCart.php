<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/12
 * Time: 16:45
 */

namespace app\frontend\modules\cart\models;


use app\common\models\BaseModel;
use app\framework\Http\Request;
use app\frontend\modules\cart\discount\models\PreCartDiscount;
use app\frontend\modules\cart\manager\CartGoodsCollection;

/**
 * Class ShopCart
 * @property CartGoodsCollection carts
 * @package app\frontend\modules\cart\models
 */
class ShopCart extends BaseModel
{

    protected $member;

    protected $request;

    protected $disable;

    public function init(CartGoodsCollection $carts, $member = null, $request = null)
    {

        $this->member = $member;

        $this->setRequest($request);

        $this->setCarts($carts);

        $this->cartValidate();

        $this->setInitAttributes();


    }

    public function cartValidate()
    {
        $this->carts->cartValidate();
    }


    public function setRequest(Request $request)
    {
        $this->request = $request;
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
     * 获取url中关于本订单的参数
     * @param null $key
     * @return mixed
     */
    public function getParams($key = null)
    {
        $result = collect(json_decode($this->getRequest()->input('orders'), true))->where('pre_id', $this->pre_id)->first();
        if (isset($key)) {
            return $result[$key];
        }

        return $result;
    }


    //注入店铺的购物车商品记录
    public function setCarts(CartGoodsCollection $carts)
    {
        $this->setRelation('carts', $carts);
        $this->carts->setShop($this);
    }


    public function isCheckedCartGoods()
    {
        return $this->carts->isCheckedCartGoods();
    }

    /**
     * 设置前端是否可选状态
     * @param ShopCart $firstShop 首选购物车店铺
     *
     */
    public function setDisable($firstShop)
    {
        $isDisable = is_null($firstShop)?false:$firstShop->isAlone();

        //是特殊店铺、已有勾选商品
        if ($this->isAlone() && !is_null($firstShop)) {
            //勾选的商品与未勾选的商品得店铺对应
            $bool = !($isDisable && $firstShop->getShopId() == $this->getShopId());
            $this->special($bool); //特殊商品
        } else {
            $this->normal($isDisable); //正常商品
        }
    }

    /**
     * 特殊商品是否可选状态
     */
    public function special($isDisable)
    {
        $this->disable = $isDisable;
        $this->setCartDisable($isDisable);
    }

    /**
     * 正常商品是否可选状态
     */
    public function normal($isDisable)
    {
        $this->disable = $isDisable;
        $this->setCartDisable($isDisable);
    }


    /**
     * 禁用购物车商品选择
     * @return mixed
     */
    public function setCartDisable($isDisable)
    {
        return $this->carts->setCartDisable($isDisable);
    }


    /**
     * 判断这个店铺只能单独下单
     * @return bool
     */
    public function isAlone()
    {
        return false;
    }

    //店铺活动优惠满减
    public function getDiscountActivity()
    {
        if(!\Setting::get('enoughReduce.open')){
            return [];
        }

        // 获取满减设置,按enough倒序
        $settings = \Setting::get('enoughReduce.enoughReduce');
        if (empty($settings)) {
            return [];
        }
        $str = '';
        foreach ($settings as $setting) {
            $str .= empty($str) ? '满'.$setting['enough'].'减'.$setting['reduce']:'，满'.$setting['enough'].'减'.$setting['reduce'];
        }

        if (\Setting::get('enoughReduce.freeFreight.open')) {
            // 设置为0 全额包邮
            if (\Setting::get('enoughReduce.freeFreight.enough') === 0 || \Setting::get('enoughReduce.freeFreight.enough') === '0') {
                $str.= empty($str) ? '全额包邮':'，全额包邮';
            } elseif(\Setting::get('enoughReduce.freeFreight.enough')) {
                $str.= empty($str) ? '满额'.\Setting::get('enoughReduce.freeFreight.enough').'包邮':'，满额'.\Setting::get('enoughReduce.freeFreight.enough').'包邮';
            }
        }

        return [[
            'name'=> '满减',
            'code'=> 'enoughReduce',
            'type'=> 'string',
            'desc'=> $str,
        ]];

    }

    /**
     * 获取总金额
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getDiscountAmount($total)
    {

        if(!\Setting::get('enoughReduce.open')){
            return 0;
        }
        // 获取满减设置,按enough倒序
        $settings = collect(\Setting::get('enoughReduce.enoughReduce'));

        if (empty($settings)) {
            return 0;
        }

        $settings = $settings->sortByDesc(function ($setting) {
            return $setting['enough'];
        });

        // 订单总价满足金额,则返回优惠金额
        foreach ($settings as $setting) {

            if ($total >= $setting['enough']) {
                return min($setting['reduce'],$total);
            }
        }
        return 0;
    }


    protected $cartDiscounts;

    public function cartDiscounts()
    {

        if (isset($this->cartDiscounts)) {
            return $this->cartDiscounts;
        }


        $cartGoodsDiscounts = $this->carts->getCartGoodsDiscounts();
        $discountsItems = collect([]);
        // 按每个种类的优惠分组 求金额的和
        $cartGoodsDiscounts->each(function ($cartGoodsDiscount) use ($discountsItems) {
            // 新类型添加
            if ($discountsItems->where('code', $cartGoodsDiscount->code)->isEmpty()) {
                $preCartDiscount = new PreCartDiscount([
                    'code' => $cartGoodsDiscount->code,
                    'amount' => $cartGoodsDiscount->amount,
                    'name' => $cartGoodsDiscount->name,

                ]);
                $discountsItems->push($preCartDiscount);
                return;
            }

            // 已存在的类型累加
            $discountsItems->where('code', $cartGoodsDiscount->code)->first()->amount += $cartGoodsDiscount->amount;
        });

       return  $this->cartDiscounts = $discountsItems;
    }

    protected  $cartExtraCharges;


    /**
     * 其他费用
     * @return \Illuminate\Support\Collection
     */
    public function cartExtraCharges()
    {
        if (isset($this->cartExtraCharges)) {
            return $this->cartExtraCharges;
        }


        $cartGoodsExtraCharges = $this->carts->getCartGoodsExtraCharges();
        $itemsAggregate  = collect([]);
        // 按每个种类分组 求金额的和
        $cartGoodsExtraCharges->each(function ($cartGoodsItem) use ($itemsAggregate) {
            // 新类型添加
            if ($itemsAggregate->where('code', $cartGoodsItem->code)->isEmpty()) {
                $model = new CartBaseModel([
                    'code' => $cartGoodsItem->code,
                    'amount' => $cartGoodsItem->amount,
                    'name' => $cartGoodsItem->name,

                ]);
                $itemsAggregate->push($model);
                return;
            }

            // 已存在的类型累加
            $itemsAggregate->where('code', $cartGoodsItem->code)->first()->amount += $cartGoodsItem->amount;
        });

        return  $this->cartExtraCharges = $itemsAggregate;
    }

    protected  $cartDeductions;

    /**
     * 抵扣
     * @return \Illuminate\Support\Collection
     */
    public function cartDeductions()
    {
        if (isset($this->cartDeductions)) {
            return $this->cartDeductions;
        }


        $cartDeductions = $this->carts->getCartGoodsDeductions();
        $itemsAggregate  = collect([]);
        // 按每个种类分组 求金额的和
        $cartDeductions->each(function ($cartGoodsItem) use ($itemsAggregate) {
            // 新类型添加
            if ($itemsAggregate->where('code', $cartGoodsItem->code)->isEmpty()) {
                $model = new CartBaseModel([
                    'code' => $cartGoodsItem->code,
                    'amount' => $cartGoodsItem->amount,
                    'name' => $cartGoodsItem->name,

                ]);
                $itemsAggregate->push($model);
                return;
            }

            // 已存在的类型累加
            $itemsAggregate->where('code', $cartGoodsItem->code)->first()->amount += $cartGoodsItem->amount;
        });

        return  $this->cartDeductions = $itemsAggregate;
    }


    public function getGoodsPrice()
    {
        return $this->carts->getPrice();
    }

    public function getPrice()
    {
        //商品总价
       return $this->getEstimatedPrice();

    }

    public function getEstimatedPrice()
    {
        return $this->carts->getEstimatedPrice();
    }


    public function getCode()
    {
        return 'shop';
    }

    public function getName()
    {
        return \Setting::get('shop.shop')['name']?:'自营';
    }
    public function getShopId()
    {
        return md5('shop');
    }

    public function getLink()
    {
        return '';
    }

    public function getMerchantId()
    {
        return 0;
    }


    public function setInitAttributes()
    {
        $attributes = array(
            'is_alone' => $this->isAlone(),
            'link' => $this->getLink(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'merchant_id' => $this->getMerchantId(),
            'shop_id' => $this->getShopId(),
            'discount_activity' => $this->getDiscountActivity(),
            'price' => sprintf('%.2f', $this->getPrice()),
        );

        $this->setRawAttributes($attributes);
    }

    public function beforeFormat()
    {
        $this->setAttribute('disable',$this->disable);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->beforeFormat();
        return parent::toArray();
    }


    public function goods()
    {
        return $this->carts->first()->goods;
    }


    public function getPriceAttribute()
    {
        return  sprintf('%.2f', $this->getPrice());
    }

    public function getDiscountActivityAttribute()
    {
        return $this->getDiscountActivity();
    }

    public function getCodeAttribute()
    {
        return $this->getCode();
    }

    public function getNameAttribute()
    {

        return $this->getName();
    }

    public function getShopIdAttribute()
    {
        return $this->getShopId();
    }
}