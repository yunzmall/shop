<?php

namespace app\frontend\modules\order\models;

use app\frontend\modules\dispatch\discount\EnoughReduce;
use app\frontend\modules\order\coinExchange\OrderCoinExchangeManager;
use app\frontend\modules\order\OrderFee;
use app\frontend\modules\order\serviceFee\OrderServiceFeeManager;
use Illuminate\Http\Request;
use app\common\models\BaseModel;
use app\common\models\DispatchType;
use app\common\models\Member;
use app\common\models\OrderRequest;
use app\common\modules\orderGoods\OrderGoodsCollection;
use app\frontend\models\Order;
use app\frontend\models\order\PreOrderDeduction;
use app\frontend\modules\deduction\OrderDeductManager;
use app\frontend\modules\deduction\OrderDeductionCollection;
use app\frontend\modules\dispatch\models\OrderDispatch;
use app\frontend\modules\dispatch\models\PreOrderAddress;
use app\frontend\modules\order\discount\BaseDiscount;
use app\frontend\modules\order\discount\OrderDiscountPriceNode;
use app\frontend\modules\order\discount\OrderMinDeductionPriceNode;
use app\frontend\modules\order\discount\OrderRestDeductionPriceNode;
use app\frontend\modules\order\OrderDiscount;
use app\common\modules\orderGoods\models\PreOrderGoods;
use app\frontend\modules\order\PriceNode;
use app\frontend\modules\order\PriceNodeTrait;
use app\frontend\modules\order\services\OrderService;
use app\frontend\modules\orderGoods\models\PreOrderGoodsCollection;
use Illuminate\Support\Collection;

/**
 * 订单生成类
 * Class preOrder
 * @package app\frontend\modules\order\services\models
 * @property OrderDeductionCollection orderDeductions
 * @property Collection orderDiscounts
 * @property Collection orderFees
 * @property Collection orderCoupons
 * @property Collection orderSettings
 * @property int id
 * @property string mark
 * @property string pre_id
 * @property float price
 * @property float goods_price
 * @property float order_goods_price
 * @property float discount_price
 * @property float deduction_price
 * @property float dispatch_price
 * @property int goods_total
 * @property string order_sn
 * @property int create_time
 * @property int uid
 * @property PreOrderAddress orderAddress
 * @property int uniacid
 * @property PreOrderGoodsCollection orderGoods
 * @property Member belongsToMember
 * @property DispatchType hasOneDispatchType
 */
class PreOrder extends Order
{
    use PreOrderTrait;
    use PriceNodeTrait;

    protected $appends = ['pre_id'];
    /**
     * @var Member $belongsToMember
     */
    public $belongsToMember;
    /**
     * @var OrderDispatch 运费类
     */
    protected $orderDispatch;
    /**
     * @var OrderDiscount 优惠类
     */
    protected $discount;
    /**
     * @var OrderFee 手续费类
     */
    protected $orderFeeManager;
    /**
     * @var OrderDeductManager 抵扣类
     */
    protected $orderDeductManager;
    /**
     * @var OrderCoinExchangeManager
     */
    protected $orderCoinExchangeManager;


    /**
     * @var OrderServiceFeeManager 服务费类
     */
    protected $orderServiceFeeManager;

    /**
     * @var  \app\frontend\modules\order\dispatch\order\BaseOrderDispatchType 配送方式
     */
    protected $orderDispatchType;

    /**
     * @var Request
     */
    protected $request;
    protected $attributes = ['id' => null];
    private $discountWeight = 0;

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function _getPriceNodes()
    {
        // 订单节点
        $nodeSettings = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-price-nodes');

        $nodes = collect($nodeSettings)->map(function ($nodeSetting) {

            return call_user_func($nodeSetting['class'], $this);
        });

        $this->discountWeight = 0;
        // 订单优惠的节点
        $discountNodes = $this->getDiscounts()->map(function (BaseDiscount $discount) {
            $this->discountWeight++;
            return new OrderDiscountPriceNode($this, $discount, 2000 + $this->discountWeight);
        });
        // 订单最低抵扣节点
        $deductionMinNodes = $this->getOrderDeductions()->map(function (PreOrderDeduction $orderDeduction) {

            return new OrderMinDeductionPriceNode($this, $orderDeduction, 9000 + $this->discountWeight);
        });

        // 订单剩余抵扣节点
        $deductionRestNodes = $this->getOrderDeductions()->map(function (PreOrderDeduction $orderDeduction) {
            $a = new OrderRestDeductionPriceNode($this, $orderDeduction, 9100 + $this->discountWeight);
            //dump($a->getKey());
            return $a;
        });

        // 按照weight排序
        $nodes = $nodes->merge($discountNodes)->merge($deductionMinNodes)->merge($deductionRestNodes)->sortBy(function (PriceNode $priceNode) {
            return $priceNode->getWeight() . $priceNode->getKey();
        })->values();

        return $nodes;
    }

    function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setRelation('orderSettings', $this->newCollection());
    }

    /**
     * @param Member $member
     * @param OrderGoodsCollection $orderGoods
     * @param Request|null $request
     * @return $this
     * @throws \app\common\exceptions\ShopException
     */
    public function init(Member $member, OrderGoodsCollection $orderGoods, Request $request)
    {
        $this->setRequest($request);
        $this->setMember($member);
        $this->beforeCreating();
        $this->setOrderGoods($orderGoods);

        $this->afterCreating();

        $this->initAttributes();

        return $this;
    }

    public function getOrderFeeManager()
    {
        if (!isset($this->orderFeeManager)) {
            $this->orderFeeManager = new OrderFee($this);
        }
        return $this->orderFeeManager;
    }

    public function getOrderCoinExchangeManager()
    {
        if (!isset($this->orderCoinExchangeManager)) {
            $this->orderCoinExchangeManager = new OrderCoinExchangeManager($this);
        }
        return $this->orderCoinExchangeManager;
    }

    public function getOrderCoinExchanges()
    {
        return $this->getOrderCoinExchangeManager()->getOrderCoinExchangeCollection();
    }

    public function getDiscount()
    {
        if (!isset($this->discount)) {
            $this->discount = new OrderDiscount($this);
        }
        return $this->discount;
    }

    public function getOrderDispatch()
    {
        if (!isset($this->orderDispatch)) {
            $this->orderDispatch = new OrderDispatch($this);
        }
        return $this->orderDispatch;
    }

    /**
     * @return OrderDeductManager
     */
    public function getOrderDeductManager()
    {
        if (!isset($this->orderDeductManager)) {
            $this->orderDeductManager = new OrderDeductManager($this);
        }
        return $this->orderDeductManager;
    }

    /**
     * @return OrderDeductionCollection|static
     * @throws \app\common\exceptions\AppException
     */
    public function getCheckedOrderDeductions()
    {
        return $this->getOrderDeductManager()->getCheckedOrderDeductions();
    }

    /**
     * @return OrderDeductionCollection
     * @throws \app\common\exceptions\AppException
     */
    public function getOrderDeductions()
    {
        if (!$this->getRelation('orderDeductions')) {
            $this->setRelation('orderDeductions', $this->getOrderDeductManager()->getOrderDeductions());
        }
        return $this->orderDeductions;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    //todo 为了兼容插件单独的默认配送模版，从order类获取，这样插件订单类好复写
    public function getPluginDefaultDispatchId()
    {
        $defaultDispatch = \app\backend\modules\goods\models\Dispatch::getOneByDefault();

        //todo 如果没有默认配送模版 如何处理
        trace_log()->freight('订单模板运费', '不存在默认的配送模板');

        return $defaultDispatch->id ?: 0;
    }

    /**
     * @param $member
     */
    public function setMember($member)
    {
        $this->belongsToMember = $member;
        $this->uid = $this->belongsToMember->uid;
        $this->uniacid = $this->getUniacid();
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
     * 依赖对象传入之前
     * @throws \app\common\exceptions\ShopException
     */
    public function beforeCreating()
    {

        $this->dispatch_type_id = $this->getRequest()->input('dispatch_type_id', 0);

        $this->setOrderInvoice();


        //临时处理，无扩展性
        if ($this->getRequest()->input('mark') !== 'undefined') {
            $this->mark = $this->getRequest()->input('mark', '');
        }

        $this->setOrderDispatchType();

        $this->setOrderAddress();

        if(app('plugins')->isEnabled('pc-terminal') && request()->pc && request()->type == 5){
            //pc端限制下单必须绑定手机
            $status = \Yunshop\PcTerminal\service\SetService::bindMobileStatus();
            if(!$status){
                return;
            }
            $member = \app\frontend\models\Member::current();
            if(empty($member)){
                throw new \app\common\exceptions\AppException('会员信息错误');
            }
            if(empty($member->mobile)){
                throw new \app\common\exceptions\AppException('请先绑定手机号再下单');
            }
        }
    }

    /**
     * 订单发票信息
     * @throws \app\common\exceptions\ShopException
     */
    public function setOrderInvoice()
    {

        $orderInvoice = new PreOrderInvoice();

        $orderInvoice->setOrder($this);
    }

    public function getOrderDispatchType()
    {
        if (isset($this->orderDispatchType)) {
            return $this->orderDispatchType;
        }

        return $this->setOrderDispatchType();
    }

    /**
     *
     */
    public function setOrderDispatchType()
    {

        $dispatchTypeManager = new \app\frontend\modules\order\dispatch\order\OrderDispatchTypeManager($this);

        $this->orderDispatchType = $dispatchTypeManager->getOrderDispatchTypeClass();

        return $this->orderDispatchType;

    }

    /**
     * @throws \app\common\exceptions\ShopException
     */
    public function setOrderAddress()
    {

        if (!is_null($this->orderDispatchType)) {
            $orderAddress = $this->orderDispatchType->preOrderAddress();
        }

        /**
         * @var PreOrderAddress $orderAddress
         */
        if (!$orderAddress) {
            $orderAddress = app('OrderManager')->make('PreOrderAddress');
        }
        $orderAddress->setOrder($this);
    }

    /**
     * 载入订单商品集合
     * @param OrderGoodsCollection $orderGoods
     */
    public function setOrderGoods(OrderGoodsCollection $orderGoods)
    {
        $this->setRelation('orderGoods', $orderGoods);
        $this->orderGoods->each(function ($aOrderGoods) {
            /**
             * @var PreOrderGoods $aOrderGoods
             */
            $aOrderGoods->init($this);
        });

    }

    /**
     *
     */
    public function afterCreating()
    {

    }


    /**
     * 显示订单数据
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes = $this->formatAmountAttributes($attributes);
        return $attributes;
    }

    /**
     * 初始化属性
     * @throws \app\common\exceptions\AppException
     */
    protected function initAttributes()
    {
        $attributes = array(
            'price' => $this->getPrice(),//订单最终支付价格
            'order_goods_price' => $this->getOrderGoodsPrice(),//订单商品成交价
            'goods_price' => $this->getGoodsPrice(),//订单商品原价
            'cost_amount' => $this->getCostPrice(),//订单商品原价
            'discount_price' => $this->getDiscountAmount(),//订单优惠金额
            'fee_amount' => $this->getFeeAmount(),//订单手续费金额
            'service_fee_amount' => $this->getServiceFeeAmount(),//订单服务费金额
            'deduction_price' => $this->getDeductionAmount(),//订单抵扣金额
            'dispatch_price' => $this->getDispatchAmount(),//订单运费
            'goods_total' => $this->getGoodsTotal(),//订单商品总数

            'is_virtual' => $this->isVirtual(),//是否是虚拟商品订单
            'no_refund' => $this->noRefund(),
            'order_sn' => OrderService::createOrderSN(),//订单编号
            'create_time' => time(),
            'note' => $this->getParams('note'),//订单备注
            'shop_name' => $this->getShopName(),// 店铺名称
            'need_address' => $this->isNeedAddress(),//订单不需要填写地址
            //发票信息应该保存到订单发票表上
            'invoice_type' => $this->getRequest()->input('invoice_type'),//发票类型
            'rise_type' => $this->getRequest()->input('rise_type'),//收件人或单位
            // 'call'=>$this->getRequest()->input('call'),//抬头或单位名称
            'collect_name' => $this->getRequest()->input('call'),//抬头或单位名称
            'company_number' => $this->getRequest()->input('company_number'),//单位识别号
        );
//dd($this->priceCache);
        $attributes = array_merge($this->getAttributes(), $attributes);
        $this->setRawAttributes($attributes);

    }

    protected function noRefund()
    {
        foreach ($this->orderGoods as $goods) {
            if ($goods->goods->no_refund) {
                return 1;
            }
        }
        return 0;
    }

    public function beforeSaving()
    {
        //订单保存前，验证订单地址是否有填写
        if (!is_null($this->orderAddress) &&
            method_exists($this->orderAddress, 'validateAddress')
            && is_callable([$this->orderAddress, 'validateAddress'])
        ) {
            $this->orderAddress->validateAddress();
        }

        $this->setOrderRequest($this->getRequest()->input());
    }

    public function setOrderRequest(array $input)
    {
        $orderRequest = new OrderRequest();
        if ($input['address']) {
            $input['address'] = urldecode($input['address']);
        }
        $orderRequest->request = $input;
        $orderRequest->ip = $this->request->getClientIp();
        $this->setRelation('orderRequest', $orderRequest);

    }

    public function getCostPrice()
    {
        //累加所有商品数量
        $result = $this->orderGoods->sum(function (PreOrderGoods $aOrderGoods) {
            return $aOrderGoods->goods_cost_price;
        });

        return $result;
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

    /**
     * 订单生成前 分组订单的标识(规则: 将goods_id 排序之后用a连接)
     * @return string
     */
    public function getPreIdAttribute()
    {
        return md5($this->orderGoods->pluck('goods_id')->toJson());
    }

    /**
     * 计算订单成交价格
     * 外部调用只计算一次,方法内部计算过程中递归调用会返回计算过程中的金额
     * @return float|mixed
     * @throws \app\common\exceptions\AppException
     */
    protected function getPrice()
    {

        $price = max($this->getPriceAfter($this->getPriceNodes()->last()->getKey()), 0);
        return $price;
    }

    /**
     * @return float|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getPriceAttribute()
    {
        return $this->getPrice();
    }

    /**
     * 获取所有优惠
     * @return Collection
     */
    protected function getDiscounts()
    {
        return $this->getDiscount()->getDiscounts();
    }

    /**
     * 获取总优惠金额
     * @return Collection
     */
    protected function getDiscountAmount()
    {
        return $this->getDiscount()->getAmount();
    }

    /**
     * 获取总手续费金额
     * @return Collection
     */
    protected function getFeeAmount()
    {
        return $this->getOrderFeeManager()->getAmount();
    }

    /**
     * 获取总服务费金额
     * @return Collection
     */
    protected function getServiceFeeAmount()
    {
        return $this->getOrderServiceFeeManager()->getAmount();
    }

    public function getOrderServiceFeeManager()
    {
        if (!isset($this->orderServiceFeeManager)) {
            $this->orderServiceFeeManager = new OrderServiceFeeManager($this);
        }
        return $this->orderServiceFeeManager;
    }

    /**
     * 获取订单抵扣金额
     * @return int
     * @throws \app\common\exceptions\AppException
     */
    public function getDeductionAmount()
    {
        return $this->getCheckedOrderDeductions()->sum('amount') ?: 0;
    }

    /**
     * 计算订单运费
     * @return int|number
     */
    public function getDispatchAmount()
    {

        return $this->getOrderDispatch()->getFreight();
    }

    public function getDispatchAmountAttribute()
    {
        return $this->getDispatchAmount();
    }

    /**
     * 获取满额包邮类
     * @return EnoughReduce
     * @author: Merlin
     * @Time: 2020/11/24   17:27
     */
    public function getEnoughReduce()
    {
        return new EnoughReduce($this);
    }

    /**
     * 公众号
     * @return int
     */
    private function getUniacid()
    {
        return $this->belongsToMember->uniacid;
    }

    /**
     * 店铺名
     * @return string
     */
    protected function getShopName()
    {
        return \Setting::get('shop.shop.name') ?: '平台自营';
    }


    /**
     * 是否禁用订单抵扣
     * @return bool true 禁用 false 正常
     */
    public function isDeductionDisable()
    {

        return false;
    }

    /**
     * 是否禁用订单优惠
     * @return bool true 禁用 false 正常
     */
    public function isDiscountDisable()
    {

        return false;
    }

    /**
     * 统计订单商品是否有虚拟商品
     * @return bool
     */
    public function isVirtual()
    {
        if ($this->is_virtual == 1) {
            return true;
        }

        return $this->orderGoods->hasVirtual();
    }

    /**
     * 订单是否需要填写地址
     * @return bool true 不需要 false 需要
     */
    public function isNeedAddress()
    {

        if ($this->need_address == 1) {
            return true;
        }

        if (!is_null($this->hasOneDispatchType) && !$this->hasOneDispatchType->need_send) {
            return false;
        }

        return $this->orderGoods->hasNeedAddress();
    }


    /**
     * @var array 需要批量更新的字段
     */
    private $batchSaveRelations = ['orderGoods', 'orderSettings', 'orderCoupons', 'orderDiscounts', 'orderDeductions', 'orderFees', 'orderServiceFees'];

    /**
     * 保存关联模型
     * @return bool
     * @throws \Exception
     */
    public function push()
    {
        foreach ($this->relations as $models) {
            $models = $models instanceof Collection
                ? $models->all() : [$models];
            /**
             * @var BaseModel $model
             */
            foreach (array_filter($models) as $model) {
                if (!isset($model->order_id) && $model->hasColumn('order_id')) {
                    $model->order_id = $this->id;
                }

            }
        }

        /**
         * 一对一关联模型保存
         */
        $relations = array_except($this->relations, $this->batchSaveRelations);

        foreach ($relations as $models) {
            $models = $models instanceof Collection
                ? $models->all() : [$models];

            foreach (array_filter($models) as $model) {
                if (!$model->push()) {
                    return false;
                }
            }
        }
        /**
         * 多对多关联模型保存
         */
        $this->insertRelations($this->batchSaveRelations);

        return true;
    }

    /**
     * 保存每一种 多对多的关联模型集合
     * @param array $relations
     */
    private function insertRelations($relations = [])
    {
        foreach ($relations as $relation) {
            if ($this->$relation->isNotEmpty()) {
                $this->saveManyRelations($relation);
            }
        }
    }

    /**
     * 保存一种 多对多的关联模型集合
     * @param $relation
     */
    private function saveManyRelations($relation)
    {
        $attributeItems = $this->$relation->map(function (BaseModel $relation) {
            $relation->updateTimestamps();

            $beforeSaving = $relation->beforeSaving();
            if ($beforeSaving === false) {
                return [];
            }
            return $relation->getAttributes();
        });

        $attributeItems = collect($attributeItems)->filter();

        $this->$relation->first()->insert($attributeItems->toArray());
        /**
         * @var Collection $ids
         */
        $ids = $this->$relation()->pluck('id');
        $this->$relation->each(function (BaseModel $item) use ($ids) {
            $item->id = $ids->shift();
            $item->afterSaving();
        });
    }
}