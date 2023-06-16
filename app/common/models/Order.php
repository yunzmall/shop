<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/28
 * Time: 上午11:32
 */

namespace app\common\models;


use app\backend\modules\order\observers\OrderObserver;
use app\common\events\order\AfterOrderCreatedImmediatelyEvent;
use app\common\events\order\AfterOrderPaidEvent;
use app\common\events\order\AfterOrderPaidImmediatelyEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\events\order\AfterOrderReceivedImmediatelyEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;
use app\common\events\order\AfterOrderSentImmediatelyEvent;
use app\common\events\order\BeforeOrderCreateEvent;
use app\common\exceptions\AppException;
use app\common\facades\SiteSetting as SiteSettingFacades;
use app\common\models\member\MemberCancel;
use app\common\models\order\Express;
use app\common\models\order\ManualRefundLog;
use app\common\models\order\OrderChangePriceLog;
use app\common\models\order\OrderCoinExchange;
use app\common\models\order\OrderCoupon;
use app\common\models\order\OrderDeduction;
use app\common\models\order\OrderDiscount;
use app\common\models\order\OrderFee;
use app\common\models\order\OrderFreightDeduction;
use app\common\models\order\OrderInvoice;
use app\common\models\order\OrderServiceFee;
use app\common\models\order\OrderSetting;
use app\common\models\order\OrderTaxFee;
use app\common\models\order\Plugin;
use app\common\models\order\Remark;
use app\common\models\refund\RefundApply;
use app\common\models\refund\RefundGoodsLog;
use app\common\models\refund\RefundProcessLog;
use app\common\modules\order\OrderOperationsCollector;
use app\common\modules\payType\events\AfterOrderPayTypeChangedEvent;
use app\common\modules\refund\services\RefundService;
use app\common\modules\shop\ShopConfig;
use app\common\services\PayFactory;
use app\common\traits\HasProcessTrait;
use app\frontend\modules\order\OrderCollection;
use app\frontend\modules\order\services\OrderService;
use app\frontend\modules\order\services\status\StatusFactory;
use app\frontend\modules\orderPay\models\PreOrderPay;
use app\host\HostManager;
use app\Jobs\OrderCreatedEventQueueJob;
use app\Jobs\OrderPaidEventQueueJob;
use app\Jobs\OrderReceivedEventQueueJob;
use app\Jobs\OrderSentEventQueueJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Yunshop\JdSupply\models\JdSupplyOrderGoods;
use Yunshop\PackageDeliver\model\PackageDeliverOrder;
use Yunshop\StoreCashier\common\models\CashierOrder;
use Yunshop\StoreCashier\common\models\StoreOrder;
use Yunshop\Supplier\common\models\InsuranceOrder;
use app\common\services\SystemMsgService;
use Yunshop\Supplier\supplier\models\SupplierOrder;

/**
 * Class Order
 * @package app\common\models
 * @property int uniacid
 * @property int id
 * @property int uid
 * @property string order_sn
 * @property int price
 * @property string statusName
 * @property string statusCode
 * @property int status
 * @property int pay_type_name
 * @property int pay_type_id
 * @property int order_pay_id
 * @property int is_pending
 * @property int is_virtual
 * @property int dispatch_type_id
 * @property int refund_id
 * @property int no_refund
 * @property float deduction_price
 * @property float order_goods_price
 * @property float discount_price
 * @property float dispatch_price
 * @property float change_price
 * @property float cost_amount
 * @property float change_dispatch_price
 * @property float fee_amount
 * @property float service_fee_amount
 * @property int plugin_id
 * @property int is_plugin
 * @property Collection orderGoods
 * @property Collection hasManyOrderGoods
 * @property Collection allStatus
 * @property Collection orderCoinExchanges
 * @property Member belongsToMember
 * @property OrderDiscount discount
 * @property Collection orderPays
 * @property OrderPay hasOneOrderPay
 * @property PayType hasOnePayType
 * @property RefundApply hasOneRefundApply
 * @property Carbon finish_time
 * @property OrderCreatedJob orderCreatedJob
 * @property OrderPaidJob orderPaidJob
 * @property OrderReceivedJob orderReceivedJob
 * @property OrderSentJob orderSentJob
 * @property Address address
 * @property Address orderAddress
 * @property Express express
 * @method static self isPlugin()
 * @method static self orders(array $searchParam)
 * @method static self cancelled()
 */
class Order extends BaseModel
{
    use HasProcessTrait, DispatchesJobs;

    public $table = 'yz_order';
    public $setting = null;
    private $StatusService;
    protected $guarded = ['id'];
    protected $appends = ['status_name', 'pay_type_name'];
    protected $with = ['process', 'hasOnePayType'];
    protected $search_fields = ['yz_order.id', 'yz_order.order_sn'];
    protected $attributes = [
        'plugin_id' => 0,
        'is_virtual' => 0,
    ];
    static protected $needLog = true;
    //protected $attributes = ['discount_price'=>0];
    const CLOSE = -1;
    const WAIT_PAY = 0;
    const WAIT_SEND = 1;
    const WAIT_RECEIVE = 2;
    const COMPLETE = 3;
    const REFUND = 11;

    /**
     * 时间类型字段
     * @return array
     */
    public function getDates()
    {
        return ['create_time', 'refund_time', 'operate_time', 'send_time', 'return_time', 'end_time', 'pay_time', 'send_time', 'cancel_time', 'create_time', 'cancel_pay_time', 'cancel_send_time', 'finish_time'] + parent::getDates();
    }


    /**
     * 获取用户消费次数
     *
     * @param $uid
     * @return mixed
     */
    public static function getCostTotalNum($uid)
    {
        return self::where('status', '>=', 1)
            ->Where('status', '<=', 3)
            ->where('uid', $uid)
            ->count('id');
    }

    /**
     * 隐藏插件订单
     * 订单流程和标准订单不一样的插件订单，不显示在前端我的订单里
     * @param $query
     * @return mixed
     */

    public function scopeHidePluginIds($query, $plugin_ids = [], $other_plugin_ids = [])
    {
        if (empty($plugin_ids)) {

            //酒店订单、租赁订单、网约车订单、服务站补货订单、拼团订单、拼购订单、抢团订单、
            //聚合CPS订单、门店余额充值订单,益生线下订单,圈仓订单(购买，提货),新拼团订单,蛋糕叔叔,
            //周边游订单,任务包复活订单,聚推联盟订单,随叫随到需求订单、随叫随到企业需求,随叫随到企业套餐购买,寄售商品,供应链租赁
            $plugin_ids = [
                33, 40, 41, 43, 54, 59, 69, 46, 70, 106, 96, 77, 78,
                115, 74, 99, 39, 127,128,62,147,151,133,144,154,155,
                156,157,158,159,161,63,64,65,160,164];
        }

        if ($other_plugin_ids) {
            $plugin_ids = array_values(array_diff($plugin_ids, $other_plugin_ids));
        }

        return $query->whereNotIn('plugin_id', $plugin_ids)->where('plugin_id', '<', '900');
    }


    /**
     * 获取用户消费总额
     *
     * @param $uid
     * @return mixed
     */
    public static function getCostTotalPrice($uid)
    {
        return self::where('status', '>=', 1)
            ->where('status', '<=', 3)
            ->where('uid', $uid)
            ->sum('price');
    }

    //获取发票信息
    public static function getInvoice($order)
    {

        //return self ::select('invoice_type','rise_type','call','company_number','invoice')
        return self::select('invoice_type', 'rise_type', 'collect_name', 'company_number', 'invoice')
            ->where('id', $order)
            ->first();
    }

    public function scopePayFail($query)
    {
        return $query->where('refund_id', '0');
    }

    /**
     * 订单状态:待付款
     * @param $query
     * @return mixed
     */
    public function scopeWaitPay($query)
    {
        //AND o.status = 0 and o.paytype<>3
        return $query->where([$this->getTable() . '.status' => self::WAIT_PAY]);
    }

    public function scopeNormal($query)
    {
        return $query->where('refund_id', 0)->where('is_pending', 0);
    }

    /**
     * 订单状态:待发货
     * @param $query
     * @return mixed
     */
    public function scopeWaitSend($query)
    {
        //AND ( o.status = 1 or (o.status=0 and o.paytype=3) )
        return $query->where([$this->getTable() . '.status' => self::WAIT_SEND]);
    }

    /**
     * 订单状态:待收货
     * @param $query
     * @return mixed
     */
    public function scopeWaitReceive($query)
    {
        return $query->where([$this->getTable() . '.status' => self::WAIT_RECEIVE]);
    }

    /**
     * 订单状态:完成
     * @param $query
     * @return mixed
     */
    public function scopeCompleted($query)
    {
        return $query->where([$this->getTable() . '.status' => self::COMPLETE]);
    }

    /**
     * 订单状态:退款中
     * @param $query
     * @return mixed
     */
    public function scopeRefund($query)
    {
        return $query->where('refund_id', '>', '0')->whereHas('hasOneRefundApply', function ($query) {
            return $query->refunding();
        });

    }

    /**
     * 订单状态:已退款
     * @param $query
     * @return mixed
     */
    public function scopeRefunded($query)
    {
        return $query->where('refund_id', '>', '0')->whereHas('hasOneRefundApply', function ($query) {
            return $query->refunded()->where('refund_type', '<', 2);
        });
    }

    /**
     * 订单状态:取消
     * @param $query
     * @return mixed
     */
    public function scopeCancelled($query)
    {
        return $query->where([$this->getTable() .'.status' => self::CLOSE]);
    }

    /**
     * 关联模型 1对多:订单商品
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @throws \app\common\exceptions\ShopException
     */
    public function hasManyOrderGoods()
    {
        return $this->hasMany(self::getNearestModel('OrderGoods'), 'order_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @throws \app\common\exceptions\ShopException
     */
    public function orderGoods()
    {
        return $this->hasMany(self::getNearestModel('OrderGoods'), 'order_id', 'id');
    }

    /**
     * 关联模型 1对多:订单优惠信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function discounts()
    {
        return $this->hasMany(app('OrderManager')->make('OrderDiscount'), 'order_id', 'id');
    }

    /**
     * 关联模型 1对多:订单抵扣信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deductions()
    {
        return $this->hasMany(app('OrderManager')->make('OrderDeduction'), 'order_id', 'id');
    }

    /**
     * 关联模型 1对多:订单信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coupons()
    {
        return $this->hasMany(app('OrderManager')->make('OrderCoupon'), 'order_id', 'id');
    }

    public function orderCoupons()
    {
        return $this->hasMany(app('OrderManager')->make('OrderCoupon'), 'order_id', 'id');
    }

    /**
     * 关联模型 1对多:改价记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderChangePriceLogs()
    {
        return $this->hasMany(OrderChangePriceLog::class, 'order_id', 'id');
    }

    public function hasOneStoreOrder()
    {
        return $this->hasOne(StoreOrder::class, 'order_id', 'id');
    }

    public function hasOneCashierOrder()
    {
        return $this->hasOne(CashierOrder::class, 'order_id', 'id');
    }

    public function hasManyInsOrder()
    {
        return $this->hasMany(InsuranceOrder::class, 'order_id', 'id');
    }

    public function hasManyJdOrderGoods()
    {
        return $this->hasMany(JdSupplyOrderGoods::Class, 'order_id', 'id');
    }

    /**
     * 关联模型 1对1:购买者
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToMember()
    {
        return $this->belongsTo(Member::class, 'uid', 'uid');
    }

    /**
     * 关联模型 1对1:进行中的退款申请记录
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneRefundApply()
    {
        return $this->hasOne(RefundApply::class, 'id', 'refund_id')->orderBy('created_at', 'desc');

    }

    /**
     * 关联模型 1对n:退款列表
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function hasManyRefundApply()
    {
        return $this->hasMany(RefundApply::class, 'order_id', 'id')->orderBy('created_at', 'desc');

    }

    // 关联模型 1对n:退款操作记录列表
    public function refundProcessLog()
    {
        return $this->hasMany(RefundProcessLog::class, 'order_id', 'id');

    }

    /**
     * 关联模型 1对多:订单运费抵扣信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function freightDeductions()
    {
        return $this->hasMany(OrderFreightDeduction::class, 'order_id', 'id');
    }

    /**
     * 关联模型 1对1:订单配送方式
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneDispatchType()
    {
        return $this->hasOne(DispatchType::class, 'id', 'dispatch_type_id');
    }

    /**
     * 关联模型 1对1:订单备注
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneOrderRemark()
    {
        return $this->hasOne(Remark::class, 'order_id', 'id');
    }

    /**
     * 关联模型 1对1:订单退款并关闭
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function manualRefundLog()
    {
        return $this->hasOne(ManualRefundLog::class, 'order_id', 'id');
    }

    /**
     * 关联模型 1对1:支付方式
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOnePayType()
    {
        return $this->hasOne(PayType::class, 'id', 'pay_type_id');
    }

    /**
     * 代付记录
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneBehalfPay()
    {
        return $this->hasOne(OrderBehalfPayRecord::class, 'order_pay_id', 'order_pay_id');
    }

    /**
     * 关联模型 1对1:订单支付信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hasOneOrderPay()
    {
        return $this->belongsTo(OrderPay::class, 'order_pay_id', 'id');
    }

    /**
     * 关联模型 1对1:订单快递
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function express()
    {
        return $this->hasOne(Express::class, 'order_id', 'id');
    }

    /**
     * 关联模型 1对多:订单快递
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expressmany()
    {
        return $this->hasMany(Express::class, 'order_id', 'id');
    }

    /**
     * 对应每个订单状态的状态类,过于啰嗦,考虑删除
     * @return \app\frontend\modules\order\services\status\Complete|\app\frontend\modules\order\services\status\WaitPay|\app\frontend\modules\order\services\status\WaitReceive|\app\frontend\modules\order\services\status\WaitSend
     */
    public function getStatusService()
    {
        if (!isset($this->StatusService)) {
            $this->StatusService = (new StatusFactory($this))->create();
        }
        return $this->StatusService;
    }

    /**
     * 关联模型 1对1:收货地址
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address()
    {
        return $this->hasOne(OrderAddress::class, 'order_id', 'id');
    }

    public function orderAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id', 'id');
    }


    /**
     * 关联模型 1对1:订单发票信息
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function orderInvoice()
    {
        return $this->hasOne(OrderInvoice::class, 'order_id', 'id');
    }

    /**
     * 关联模型 1对1:订单支付信息
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOnePay()
    {
        return $this->hasOne(OrderPay::class, 'order_id', 'id');
    }

    public function getStatusCodeAttribute()
    {
        return app('OrderManager')->setting('status')[$this->status];
    }

    /**
     * @return array
     */
    public function getOperationsSetting()
    {
        return [];
    }

    public function isClose()
    {
        return $this->status == self::CLOSE;
    }

    /**
     * 订单状态汉字
     * @return string
     */
    public function getStatusNameAttribute()
    {
        if (!$this->isClose() && $this->currentProcess()) {
            return $this->currentProcess()->status_name;
        }
        $statusName = $this->getStatusService()->getStatusName();
        if ($this->isPending()) {
            $statusName .= ' : 锁定';
        }

        return $statusName;
    }

    /**
     * 支付类型汉字
     * @return string
     */
    public function getPayTypeNameAttribute()
    {
        if ($this->pay_type_id != PayType::CASH_PAY && $this->status == self::WAIT_PAY) {
            return '未支付';
        }
        $append = '';
        if ($this->hasOneBehalfPay) {
            $append = "（代付:{$this->hasOneBehalfPay->behalf_id}）";
        }
        if ($this->pay_type_id == 3) {
            $set = \Setting::get('shop.shop');
            return ($set['credit'] ?: '余额') . $append;
        }
        return $this->hasOnePayType->name . $append;
    }


    /**
     * 订单可点的按钮
     * @return array
     */
    public function getButtonModelsAttribute()
    {
        $result = $this->memberButtons();

        return $result;
    }


    private function memberButtons()
    {
        return app('OrderManager')->make(OrderOperationsCollector::class)->getOperations($this);
    }

    /**
     * 按状态分组获取订单数量
     * @param $query
     * @param array $status
     * @return array
     */
    public function scopeGetOrderCountGroupByStatus($query, $status = [])
    {

        $newQuery = $query->newQuery();
//dump($newQuery->dump());
        //$status = [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE, Order::REFUND];
        $status_counts = $query->select('status', DB::raw('count(*) as total'))
            ->whereIn('status', $status)->where('plugin_id', '<', 900)
            ->HidePluginIds()
            ->groupBy('status')->get()->makeHidden(['status_name', 'pay_type_name', 'has_one_pay_type', 'button_models'])
            ->toArray();
        $refund_status = [];
        $icon = [
            Order::REFUND => 'icon-fontclass-shouhouliebiao',
            Order::WAIT_PAY => 'icon-fontclass-daifukuan',
            Order::WAIT_SEND => 'icon-fontclass-daifahuo',
            Order::WAIT_RECEIVE => 'icon-fontclass-daishouhuo1',
            Order::COMPLETE => 'icon-fontclass-daishouhuo1',
        ];
        if (in_array(Order::REFUND, $status)) {

            $refund_count = \app\frontend\models\Order::select(DB::raw('count(*) as total'))
                ->where('status', '>', self::WAIT_PAY)->where('plugin_id', '<', 900)
                ->hidePluginIds()->refund()->count();

//            $refund_count = $query->refund()->count();
            $refund_status[] = [
                'status' => Order::REFUND,
                'status_name' => '售后列表',
                'class' => $icon[Order::REFUND],
                'total' => $refund_count
            ];
        }
        $status_counts = array_column($status_counts, null, 'status');
        foreach ($status as $state) {
            if (!in_array($state, array_column($refund_status, 'status'))) {
                $refund_status[] = [
                    'status' => $state,
                    'status_name' => $this->getAllStatusAttribute()->where('id', $state)->first()['name'] ?: '',
                    'class' => $icon[$state],
                    'total' => $status_counts[$state]['total'] ?: 0
                ];
            }
        }
        return $refund_status;
    }

    /**
     * 区分订单属于插件或商城,考虑使用新添加的scopePluginId方法替代
     * @param $query
     * @return mixed
     */
    public function scopeIsPlugin($query)
    {
        return $query->where('is_plugin', 0);
    }

    /**
     * 用来区分订单属于哪个.当插件需要查询自己的订单时,复写此方法
     * @param $query
     * @param int $pluginId
     * @return mixed
     */
    public function scopePluginId($query, $pluginId = 0)
    {
        return $query->where('plugin_id', $pluginId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderPlugin()
    {
        return $this->hasMany(Plugin::class);
    }

    /**
     * 用来区分订单属于哪个.当插件需要查询自己的订单时,复写此方法
     * @param $query
     * @param int $pluginId
     * @return mixed
     */
    public function scopeHasPluginId($query, $pluginId = 0)
    {
        if (!$pluginId) {
            return $query;
        }

        return $query->whereHas('orderPlugin', function ($query) use ($pluginId) {
            $query->where('plugin_id', $pluginId);
        });
    }

    /**
     * 通过会员ID获取订单信息
     * @param $member_id
     * @param $status
     * @return mixed
     */
    public static function getOrderInfoByMemberId($member_id, $status)
    {
        return self::where('uid', $member_id)->isComment($status);
    }

    /**
     * 关系链 指定商品
     *
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOrderListByUid($uid)
    {
        return self::select(['*'])
            ->where('uid', $uid)
            ->where('status', '>=', 1)
            ->where('status', '<=', 3)
            ->with(['hasManyOrderGoods' => function ($query) {
                return $query->select(['*']);
            }])
            ->get();
    }

    public function isVirtual()
    {
        return $this->is_virtual == 1;
    }

    public function orderDeduction()
    {
        return $this->hasMany(OrderDeduction::class, 'order_id', 'id');
    }

    public function orderDeductions()
    {
        return $this->hasMany(OrderDeduction::class, 'order_id', 'id');
    }

    public function orderCoupon()
    {
        return $this->hasMany(OrderCoupon::class, 'order_id', 'id');
    }

    public function orderDiscounts()
    {
        return $this->hasMany(OrderDiscount::class, 'order_id', 'id');
    }

    //订单手续费
    public function orderFees()
    {
        return $this->hasMany(OrderFee::class, 'order_id', 'id');
    }

    //订单税率优惠
    public function orderTaxFees()
    {
        return $this->hasMany(OrderTaxFee::class, 'order_id', 'id');
    }

    //订单服务费
    public function orderServiceFees()
    {
        return $this->hasMany(OrderServiceFee::class, 'order_id', 'id');
    }

    public function orderDiscount()
    {
        return $this->hasMany(OrderDiscount::class, 'order_id', 'id');
    }

    public function receive()
    {
        return \app\frontend\modules\order\services\OrderService::orderReceive(['order_id' => $this->id]);
    }

    public function orderPays()
    {
        return $this->belongsToMany(OrderPay::class, (new OrderPayOrder())->getTable(), 'order_id', 'order_pay_id');
    }

    public function memberCancel()
    {
        return $this->hasOne(MemberCancel::class, 'member_id', 'uid');
    }

    /**
     * 订单直接关闭
     * @return mixed
     */
    public function close()
    {
        return \app\backend\modules\order\services\OrderService::close($this);
    }

    /**
     * 取消订单退款状态
     * @return mixed
     */
    public function cancelRefund()
    {
        return \app\backend\modules\order\services\OrderService::cancelRefund($this);
    }

    /**
     * 初始化方法
     */
    public static function boot()
    {
        parent::boot();
        static::observe(new OrderObserver());
        // 添加了公众号id的全局条件.
        static::addGlobalScope(function (Builder $builder) {
            $builder->uniacid();
            $builder->hasPluginId();
        });
    }

    public function needSend()
    {
        return isset($this->hasOneDispatchType) && $this->hasOneDispatchType->needSend();
    }

    public function orderSettings()
    {
        return $this->hasMany(OrderSetting::class, 'order_id', 'id');
    }

    public function setPayTypeIdAttribute($value)
    {
        $this->attributes['pay_type_id'] = $value;
        if ($this->pay_type_id != $this->getOriginal('pay_type_id')) {
            event(new AfterOrderPayTypeChangedEvent($this));
        }
    }

    /**
     * @param $value
     * @throws AppException
     */
    public function setStatusAttribute($value)
    {
        if ($this->isPending()) {
            throw new AppException("订单已锁定,无法继续操作");
        }
        $this->attributes['status'] = $value;
    }

    public function isPending()
    {
        return $this->is_pending;
    }

    public function getSetting($key)
    {
        // 全局设置
        $result = \app\common\facades\Setting::get($key);

        if (isset($this->orderSettings) && $this->orderSettings->isNotEmpty()) {
            // 订单设置
            $keys = collect(explode('.', $key));
            $orderSettingKey = $keys->shift();
            if ($orderSettingKey == 'plugin') {
                // 获取第一个不为plugin的key
                $orderSettingKey = $keys->shift();
            }
            $orderSettingValueKeys = $keys;
            if ($orderSettingValueKeys->isNotEmpty()) {


                $orderSettingValue = array_get($this->orderSettings->where('key', $orderSettingKey)->first()->value, $orderSettingValueKeys->implode('.'));

            } else {
                $orderSettingValue = $this->orderSettings->where('key', $orderSettingKey)->first()->value;
            }

            if (isset($orderSettingValue)) {
                if (is_array($result)) {
                    // 数组合并
                    $result = array_merge($result, $orderSettingValue);
                } else {
                    // 其他覆盖
                    $result = $orderSettingValue;
                }
            }
        }

        return $result;
    }

    //关联商城订单表
    public function hasOneMemberShopInfo()
    {
        return $this->hasOne(MemberShopInfo::class, 'member_id', 'uid');

    }

    /**
     * 已退款
     * @return bool
     */
    public function isRefunded()
    {
        // 存在处理中的退款申请
        if (empty($this->refund_id) || !isset($this->hasOneRefundApply)) {
            return false;
        }
        if ($this->hasOneRefundApply->isRefunded()) {
            return true;
        }
        return false;
    }

    /**
     * 退款中
     * @return bool
     */
    public function isRefunding()
    {
        // 存在处理中的退款申请
        if (empty($this->refund_id) || !isset($this->hasOneRefundApply)) {
            return false;
        }
        if ($this->hasOneRefundApply->isRefunding()) {
            return true;
        }
        return false;
    }

    /**
     * 可以退款
     * @return bool
     */
    public function canRefund()
    {
        $shop_set = \Setting::get('shop.trade');
        //关闭后不许退款
        if (!RefundService::allowRefund()) {
            return false;
        }
        //收货后禁止退款
        if ($this->status>=2) {
            if (in_array($this->plugin_id, [31,32])) {
                if (app('plugins')->isEnabled('store-cashier')) {
                    $store_order = StoreOrder::where('order_id', $this->id)->first();
                    if ($store_order) {
                        $store_id = $store_order->store_id;
                    } else {
                        $cashier_order = CashierOrder::where('order_id', $this->id)->first();
                        $store_id = \Yunshop\StoreCashier\common\models\Store::uniacid()->where('cashier_id', $cashier_order->cashier_id)->value('id');
                    }
                    $store_trade_set = \Setting::get("store_cashier_{$store_id}.trade");
                    if ($store_trade_set['send_refund_status']) {
                        //0或空禁止退款
                        $store_send_refund_time = $store_trade_set['send_refund_time'];
                        if (!$store_send_refund_time) {
                            return false;
                        }
                        if ($this->send_time->addMinutes($store_send_refund_time)->timestamp < time()) {
                            return false;
                        }
                    } else {
                        if ($shop_set['send_refund_status']) {
                            //0或空禁止退款
                            $send_refund_time = $shop_set['send_refund_time'];
                            if (!$send_refund_time) {
                                return false;
                            }
                            if ($this->send_time->addMinutes($send_refund_time)->timestamp < time()) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($shop_set['send_refund_status']) {
                        //0或空禁止退款
                        $send_refund_time = $shop_set['send_refund_time'];
                        if (!$send_refund_time) {
                            return false;
                        }
                        if ($this->send_time->addMinutes($send_refund_time)->timestamp < time()) {
                            return false;
                        }
                    }
                }
            } elseif ($this->plugin_id == 92) {
                if (app('plugins')->isEnabled('supplier')) {
                    $supplier_order = SupplierOrder::uniacid()->where('order_id', $this->id)->first();
                    $supplier_trade_set = \Setting::get("plugin.supplier.trade_{$supplier_order->supplier_id}");
                    if ($supplier_trade_set['send_refund_status']) {
                        //0或空禁止退款
                        $store_send_refund_time = $supplier_trade_set['send_refund_time'];
                        if (!$store_send_refund_time) {
                            return false;
                        }
                        if ($this->send_time->addMinutes($store_send_refund_time)->timestamp < time()) {
                            return false;
                        }
                    } else {
                        if ($shop_set['send_refund_status']) {
                            //0或空禁止退款
                            $send_refund_time = $shop_set['send_refund_time'];
                            if (!$send_refund_time) {
                                return false;
                            }
                            if ($this->send_time->addMinutes($send_refund_time)->timestamp < time()) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($shop_set['send_refund_status']) {
                        //0或空禁止退款
                        $send_refund_time = $shop_set['send_refund_time'];
                        if (!$send_refund_time) {
                            return false;
                        }
                        if ($this->send_time->addMinutes($send_refund_time)->timestamp < time()) {
                            return false;
                        }
                    }
                }
            } else {
                if ($shop_set['send_refund_status']) {
                    //0或空禁止退款
                    $send_refund_time = $shop_set['send_refund_time'];
                    if (!$send_refund_time) {
                        return false;
                    }
                    if ($this->send_time->addMinutes($send_refund_time)->timestamp < time()) {
                        return false;
                    }
                }
            }
        }
        if ($this->status == self::COMPLETE) {
            // 完成后n天不许退款
            if ($this->finish_time && $this->finish_time->diffInDays() >= $shop_set['refund_days']) {
                return false;
            }
            // 完成后不许退款
            if ($shop_set['refund_days'] === '0') {
                return false;
            }
        }
        // 存在处理中的退款申请
        if (!empty($this->refund_id) || isset($this->hasOneRefundApply)) {
            return false;
        }
        if (app('plugins')->isEnabled('blind-box')) {
            $has = \Yunshop\BlindBox\models\BlindBoxSubOrderModel::where('sub_order_id',$this->id)->count();
            if ($has) {//盲盒子订单不给退
                return false;
            }
        }

        return true;
    }
    /**
     * 可以部分退款，用于后端列表部分退款按钮显示
     * @return bool
     */
    public function canPartRefund()
    {

        if (!$this->canRefund()) {
            return false;
        }

        // 如果商品为1则不部分退款
        if ($this->goods_total <= 1) {
            return false;
        }
        return true;
    }

    public function getAllStatusAttribute()
    {
        return collect([
            [
                'id' => self::CLOSE,
                'name' => '已关闭',
            ], [
                'id' => self::WAIT_PAY,
                'name' => '待支付',
            ], [
                'id' => self::WAIT_SEND,
                'name' => '待发货',
            ], [
                'id' => self::WAIT_RECEIVE,
                'name' => '待收货',
            ], [
                'id' => self::COMPLETE,
                'name' => '已完成',
            ], [
                'id' => self::REFUND,
                'name' => '已退款',
            ],

        ]);
    }

    /**
     * 后台支付
     * @throws AppException
     */
    public function backendPay()
    {
        // 生成支付记录 记录订单号,支付金额,用户,支付号
        $orderPay = new PreOrderPay(['pay_type_id' => PayType::BACKEND]);
        // 添加关联订单
        $orders = new OrderCollection([$this]);
        $orderPay->setOrders($orders);
        $orderPay->store();
        // 获取支付信息
        $orderPay->getPayResult(PayFactory::PAY_BACKEND);
        // 保存支付状态
        $orderPay->pay();
    }

    /**
     * 系统退款
     * @throws AppException
     */
    public function refund()
    {
        $result = $this->hasOneOrderPay->fastRefund($this);
        if (!$result['status']) {
            throw new AppException($result['msg']);
        }

        OrderService::orderForceClose(['order_id' => $this->id]);
        return $result;
    }

    /**
     * 系统退款(不管什么类型都退回余额)
     * @throws AppException
     */
    public function refund2()
    {
        $result = $this->hasOneOrderPay->fastRefund2($this);
        OrderService::orderForceClose(['order_id' => $this->id]);


        return $result;
    }

    public function fireCreatedEvent()
    {
        event(new AfterOrderCreatedImmediatelyEvent($this));

        OrderCreatedJob::create([
            'order_id' => $this->id,
        ]);
        $this->dispatch(new OrderCreatedEventQueueJob($this->id));
    }

    public function firePaidEvent()
    {
        event(new AfterOrderPaidImmediatelyEvent($this));

        //异步
        OrderPaidJob::create([
            'order_id' => $this->id,
        ]);
        $this->dispatch(new OrderPaidEventQueueJob($this->id));
    }

    public function fireSentEvent()
    {
        event(new AfterOrderSentImmediatelyEvent($this));

        //异步
        OrderSentJob::create([
            'order_id' => $this->id,
        ]);
        $this->dispatch(new OrderSentEventQueueJob($this->id));
    }

    public function fireReceivedEvent()
    {
        event(new AfterOrderReceivedImmediatelyEvent($this));

        // 去掉同步设置（已没用，相关设置也注释掉了，之前为了解决成为分销商和分销升级异步问题）
//        if (\Setting::get('shop.order.receive_process')) {
//            //同步
//            event(new AfterOrderReceivedEvent($this));
//
//        } else {
        //异步
        OrderReceivedJob::create([
            'order_id' => $this->id,
        ]);
        $this->dispatch(new OrderReceivedEventQueueJob($this->id));
//        }
    }

    //取消发货，删除队列记录
    public function delOrderSent()
    {
        OrderSentJob::where('order_id', $this->id)->delete();
    }

    public function orderCreatedJob()
    {
        return $this->hasOne(OrderCreatedJob::class, 'order_id');
    }

    public function orderSentJob()
    {
        return $this->hasOne(OrderSentJob::class, 'order_id');
    }

    public function orderPaidJob()
    {
        return $this->hasOne(OrderPaidJob::class, 'order_id');
    }

    public function orderReceivedJob()
    {
        return $this->hasOne(OrderReceivedJob::class, 'order_id');
    }

    public function stockEnough()
    {
        $this->orderGoods->each(function (OrderGoods $orderGoods) {
            // 付款后扣库存
            if ($orderGoods->goods->reduce_stock_method == 1) {
                $orderGoods->stockEnough();
            }
        });
    }

    public function orderRequest()
    {
        return $this->hasOne(OrderRequest::class, 'order_id');
    }

    public function orderCoinExchanges()
    {
        return $this->hasMany(OrderCoinExchange::class, 'order_id');
    }
    public function refundGoodsLog()
    {
        return $this->hasMany(refundGoodsLog::class, 'order_id');
    }

    static function queueCount()
    {
        $hostCount = count((new HostManager())->hosts() ?: []) ? : 1;
        if ($count = SiteSettingFacades::get('queue.order')) {
            return $count;
        }
        foreach (ShopConfig::current()->getItem('queue') as $item) {
            if ($item['key'] == 'order') {
                break;
            }
        }
        $diy_count = SiteSettingFacades::get('queue.order', $item['total']);
        if (!$diy_count) {
            return $item['total'] * $hostCount;
        }
        return $diy_count * $hostCount;
    }

    /**
     * 不发送消息通知的订单
     * @return bool
     */
    public function notSendMessage()
    {

        //门店自提
        if ($this->dispatch_type_id == DispatchType::SELF_DELIVERY) {
            return true;
        }

        //酒店有自己的消息通知
        if ($this->plugin_id == 33) {
            return true;
        }

        //聚合CPS的订单（不包括卡券订单）是每天凌晨定时任务请求第三方数据创建的，不发送消息通知
        if ($this->plugin_id == 70) {
            return true;
        }

        //芸cps的订单为同步第三方订单，不发送消息通知
        if ($this->plugin_id == 74) {
            return true;
        }

        //聚推联盟的订单为同步第三方订单，不发送消息通知
        if ($this->plugin_id == 161) {
            return true;
        }

        //珍惠拼
        if ($this->plugin_id == 115) {
            if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('zhp_group_lottery_sent_msg_status'))) {
                $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('zhp_group_lottery_sent_msg_status'), 'class');
                $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('zhp_group_lottery_sent_msg_status'), 'function');
                $ret = $class::$function($this->id, $this->uid);//true
                if ($ret) {
                    return $ret;
                }
            }
        }

        return false;
    }

    /**
     * 是否盲盒订单(todo 已经有别的插件覆盖了原来的物流按钮配置，无法重写)
     * @return bool
     */
    public function isBlindBox()
    {
        return $this->plugin_id == 107;
    }
}
