<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/3
 * Time: 10:35
 */

namespace app\backend\modules\order\models;

use app\backend\modules\member\models\MemberParent;
use app\backend\modules\order\services\OrderService;
use app\backend\modules\order\services\OrderViewService;
use app\common\models\ExpeditingDelivery;
use app\common\models\Goods;
use app\common\models\GoodsOption;
use app\common\models\Member;
use app\common\models\order\FirstOrder;
use app\common\models\order\OrderDeliver;
use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Support\Facades\DB;
use app\common\models\PayTypeGroup;
use app\common\models\MemberCertified;
use Yunshop\ElectronicsBill\common\models\ElectronicsBillTemplate;
use Yunshop\PackageDeliver\model\PackageDeliverOrder;
use Yunshop\PackageDelivery\models\DeliveryOrder;

/**
 * Class VueOrder
 * @package app\backend\modules\order\models
 */
class VueOrder extends \app\common\models\Order
{

    protected $appends = ['backend_button_models', 'status_name', 'pay_type_name', 'row_bottom'];


    protected $orderType;

    /**
     * @return \app\backend\modules\order\services\type\NoneOrder|\app\backend\modules\order\services\type\OrderTypeFactory
     */
    public function getOrderType()
    {
        if (!isset($this->orderType)) {
            $this->orderType = $this->_getOrderType();
        }
        return $this->orderType;

    }
    protected function _getOrderType()
    {
        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-list.type');

        // 从配置文件中载入,按优先级排序
        $orderTypeConfigs = collect($configs)->sortBy('priority');


        //遍历取到第一个通过验证的订单类型返回
        foreach ($orderTypeConfigs as $configItem) {
            /**
             * @var \app\backend\modules\order\services\type\OrderTypeFactory $orderType
             */
            $orderType = call_user_func($configItem['class'], $this);
            //通过验证返回
            if (isset($orderType) && $orderType->isBelongTo()) {
                return $orderType;
            }

        }
        //没有对应订单类型，返回默认订单类型
        return new \app\backend\modules\order\services\type\NoneOrder($this);

    }

    /**
     * 订单后端操作按钮
     * @return array
     */
    public function getBackendButtonModelsAttribute()
    {
        return  $this->getOrderType()->buttonModels();
    }

    /**
     * 订单列表行顶部显示
     * @return array
     */
    public function getTopRowAttribute()
    {
        return $this->getOrderType()->rowHeadShow();
    }

    public function getRowBottomAttribute()
    {
        return $this->getOrderType()->rowBottom();
    }

    //订单链接操作
    public function getFixedLinkAttribute()
    {
        return $this->getOrderType()->fixedLink();
    }

    /**
     * 订单列表固定按钮操作
     * @return array
     */
    public function getFixedButtonAttribute()
    {
        return $this->getOrderType()->fixedButton();
    }


    public function scopeStatusCode($query, $code = null)
    {
        switch ($code) {
            case 'waitPay':
                $query->waitPay(); //待支付
                break;
            case 'waitSend':
                $query->waitSend(); //待发货
                break;
            case 'waitReceive':
                $query->waitReceive(); //待收货
                break;
            case 'completed':
                $query->completed(); //已完成
                break;
            case 'cancelled':
                $query->cancelled(); //已关闭
                break;
            case 'expeditingSend':
                $query->waitSend()->join('yz_order_expediting_delivery', 'yz_order_expediting_delivery.order_id', '=', 'yz_order.id'); //催发货
                break;
            case 'callbackFail':
                $orderIds = DB::table('yz_order as o')->join('yz_order_pay_order as opo', 'o.id', '=', 'opo.order_id')
                    ->join('yz_order_pay as op', 'op.id', '=', 'opo.order_pay_id')
                    ->join('yz_pay_order as po', 'po.out_order_no', '=', 'op.pay_sn')
                    ->where('op.status', 0)
                    ->where('o.pay_time', 0)
                    ->where('po.status', 2)
                    ->distinct()->pluck('o.id');
                $query->whereIn('id', $orderIds); //支付异常订单
                break;
            case 'payFail':
                $orderIds = DB::table('yz_order as o')->join('yz_order_pay_order as opo', 'o.id', '=', 'opo.order_id')
                    ->join('yz_order_pay as op', 'op.id', '=', 'opo.order_pay_id')
                    ->whereIn('o.status', [0, -1])
                    ->where('op.status', 1)
                    ->pluck('o.id');
                $query->whereIn('id', $orderIds); //支付回调异常订单
                break;
            case 'all':
                break;
            default:
        }

        if ($code == 'all') {
            //todo 暂时的，只显示有设置config配置的订单
            foreach ((new OrderViewService())->getOrderType() as $orderType) {
                $pluginIds[] = $orderType['plugin_id'];
            }
            if ($pluginIds) {
                $query->whereIn('plugin_id', $pluginIds);
            }
        } else {
            $query->pluginId();
        }


        return $query;
    }

    public function  scopeSearch(Builder $order_builder, $search = [])
    {

        $order_builder->select('yz_order.*');

        //订单首单搜索
        if ($search['first_order']) {
            $order_builder->whereHas('hasManyFirstOrder');
            //$order_builder->join('yz_first_order', 'yz_first_order.order_id', '=', 'yz_order.id');
        }

        if (isset($search['plugin_id']) && $search['plugin_id'] != '') {
            $order_builder->where('yz_order.plugin_id', $search['plugin_id']);
        }


        if (isset($search['order_type']) && $search['order_type'] != '') {
            $order_builder->where('yz_order.plugin_id', $search['order_type']);
        }



        if ($search['order_id']) {
            $order_builder->where('yz_order.id', $search['order_id']);
        }


        if ($search['order_status']) {
            if (is_array($search['order_status'])) {
                if ($status = array_intersect([-1, 1, 2, 3, 'waitPay'], $search['order_status'])) {
                    if (($key = array_search('waitPay', $status)) !== false) {
                        $status[$key] = 0;
                    }
                    $order_builder->whereIn('yz_order.status', $status);
                }
            } else {
                $status = $search['order_status'] == 'waitPay' ? 0 : $search['order_status'];
                $order_builder->where('yz_order.status', $status);
            }
        }

        if ($search['order_sn']) {
            $order_builder->where('yz_order.order_sn', trim($search['order_sn']));
        }

        if ($search['pay_sn']) {
            $order_builder->join('yz_order_pay', 'yz_order_pay.id', '=', 'yz_order.order_pay_id')
                ->where('yz_order_pay.pay_sn', trim($search['pay_sn']));
        }

        // 营销码标签搜索
        if ($search['marketing_qr_label']) {
            $order_builder->join('yz_marketing_qr_log', 'yz_order.id', '=', 'yz_marketing_qr_log.order_id')
                ->join('yz_marketing_qr', 'yz_marketing_qr.id', '=', 'yz_marketing_qr_log.marketing_qr_id')
                ->where('yz_marketing_qr.label', 'like', "%" . trim($search['marketing_qr_label']) . "%");;
        }

        //根据会员id搜索
        if ($search['member_id']) {
            $order_builder->where('yz_order.uid', $search['member_id']);
        }

        //根据上级id搜索
        if (isset($search['parent_id'])) {
            // 去空
            $parent_id = str_replace(' ', '', $search['parent_id']);

            // 0为搜索上级总店
            if ($parent_id > 0) {
                $parent_id = (int)$search['parent_id'];
            }else{
                // 参数为字符串时,查询不存在记录返回空.
                if($parent_id) {
                    $parent_id = -99;
                }

                // 查询为0的上级(总店)
                if ($parent_id === '0') {
                    $parent_id = 0;
                }

                // 空字符串返回所有
                if ($parent_id === '') {
                    $parent_id = 'all';
                }
            }

            if ($parent_id !== 'all'){
                $order_builder->join('yz_member', function ($join) use ($parent_id) {
                    return $join->on('yz_member.member_id', '=', 'yz_order.uid')->where('parent_id', $parent_id);
                });
            }
        }

        if ($search['member_info']) {
            $order_builder->join('mc_members', function ($join)  use ($search) {
                $join->on('mc_members.uid', '=', 'yz_order.uid')->where(function ($where) use ($search) {
                    return $where->where('mc_members.realname', 'like', "%{$search['member_info']}%")
                        ->orWhere('mc_members.mobile', 'like', "%{$search['member_info']}%")
                        ->orWhere('mc_members.nickname', 'like', "%{$search['member_info']}%");
                });
            });
        }


        //增加地址,姓名，手机号搜索
        if ($search['address_mobile'] || $search['address'] || $search['address_name']) {
            $order_builder->join('yz_order_address', 'yz_order_address.order_id', '=', 'yz_order.id')->where(function ($query) use ($search) {
                if ($search['address_mobile']) {
                    $query->where('yz_order_address.mobile', 'like', "%{$search['address_mobile']}%");
                }
                if ($search['address_name']) {
                    $query->where('yz_order_address.realname', 'like', "%{$search['address_name']}%");
                }
                if ($search['address']) {
                    $query->where('yz_order_address.address', 'like', "%{$search['address']}%");
                }
            });
             // $order_builder->whereHas('address', function ($query) use ($search) {
             //     return $query->where('mobile', 'like', '%' . $search['address_mobile'] . '%')
             //       ->where('realname', 'like', '%' . $search['address_name'] . '%');
             // });
        }

        //商品id  商品名称
        if ($search['goods_id'] || $search['goods_title'] || $search['product_sn'] || $search['goods_sn']) {
            $orderGoodsModel = OrderGoods::uniacid();
            if ($search['goods_id']) {
                $orderGoodsModel->where('goods_id', $search['goods_id']);
            }
            if ($search['goods_title']) {
                $orderGoodsModel->where('title', 'like', "%".trim($search['goods_title'])."%");
            }
            if ($search['product_sn']) {
                $goods_ids = array_values(array_unique(array_merge(
                    GoodsOption::uniacid()->where('product_sn', $search['product_sn'])->pluck('goods_id')->toArray(),
                    Goods::uniacid()->where('product_sn', $search['product_sn'])->pluck('id')->toArray()
            )));
                $goods_ids = $goods_ids ? : [-1];
                $orderGoodsModel->whereIn('goods_id', $goods_ids);
            }
            if ($search['goods_sn']) {
                $goods_ids = array_values(array_unique(array_merge(
                    GoodsOption::uniacid()->where('goods_sn', $search['goods_sn'])->pluck('goods_id')->toArray(),
                    Goods::uniacid()->where('goods_sn', $search['goods_sn'])->pluck('id')->toArray()
            )));
                $goods_ids = $goods_ids ? : [-1];
                $orderGoodsModel->whereIn('goods_id', $goods_ids);
            }
            $goods_order_ids = $orderGoodsModel->pluck('order_id')->unique()->toArray();

            $order_builder->whereIn('yz_order.id', $goods_order_ids);
        }

        if ($search['note']) {
            $order_builder->where('note', 'like', "%{$search['note']}%");
        }

        //快递单号
        if ($search['express']) {
            $order_builder->whereHas('express', function ($query) use ($search) {
                $query->where('express_sn',$search['express']);
            });
        }


        if ($search['pay_type']) {
            $order_builder->where('yz_order.pay_type_id', $search['pay_type']);
        } else {
            //支付方式：先按分组查询，如分组不存在按支付类型查询
            if (array_get($search, 'pay_type_group', '')) {

                //改为支付分组查询，前端传入支付分组id，在该处通过分组id获取组中所有成员，这些成员就是确切的支付方式
                //如前端传入的分组id为2，对应的是支付宝支付分组，然后查找属于支付宝支付组的支付方式，找到如支付宝，支付宝-yz这些具体支付方式
                //获取到确切的支付方式后，对查询条件进行拼接
                $payTypeGroup = PayTypeGroup::with('hasManyPayType')->find($search['pay_type_group']);

                if($payTypeGroup) {
                    $payTypes = $payTypeGroup->toArray();
                    if($payTypes['has_many_pay_type']) {
                        $pay_type_ids = array_column($payTypes['has_many_pay_type'], 'id');
                        $order_builder->whereIn('yz_order.pay_type_id', $pay_type_ids);
                    }
                }
            }
        }


        //操作时间范围
        if ($search['start_time'] && $search['end_time'] && $search['time_field']) {
            $range = [strtotime($search['start_time']), strtotime($search['end_time'])];

            if (in_array($search['time_field'],['refund_create_time','refund_finish_time'])) {
                $refund_time_field = ['refund_create_time'=> 'create_time', 'refund_finish_time'=> 'refund_time'];
                $order_builder->join('yz_order_refund', 'yz_order_refund.id', '=', 'yz_order.refund_id')
                    ->whereBetween('yz_order_refund.'.$refund_time_field[$search['time_field']], $range);

            } else {
                $order_builder->whereBetween('yz_order.'.$search['time_field'], $range);
            }
        }


        //自提点条件搜索
        if ($search['package_deliver_id']) {
            $order_builder->join('yz_package_deliver_order', 'yz_package_deliver_order.order_id', '=', 'yz_order.id')
                ->where('yz_package_deliver_order.deliver_id', $search['package_deliver_id']);
        }

        if ($search['package_deliver_name']) {
            $deliver_ids = \Yunshop\PackageDeliver\model\Deliver::uniacid()
                ->where('deliver_name', 'like', "%{$search['package_deliver_name']}%")
                ->pluck('id')->all();
            $order_builder->join('yz_package_deliver_order', 'yz_package_deliver_order.order_id', '=', 'yz_order.id')
                ->whereIn('yz_package_deliver_order.deliver_id', $deliver_ids);
        }

        if($search['bill_print']){
            if(app('plugins')->isEnabled('electronics-bill')){
                $table_prefix = app('db')->getTablePrefix();
                if ($search['bill_print'] == 'print') {
                    $order_builder->whereRaw($table_prefix . "yz_order.id in (select distinct order_id from " . $table_prefix . "yz_electronics_bill_template)");
                } elseif ($search['bill_print'] == 'not_print') {
                    $order_builder->whereRaw($table_prefix . "yz_order.id not in (select distinct order_id from " . $table_prefix . "yz_electronics_bill_template)");
                }
            }
        }

        if ($search['dispatch_type_id']) {
            $order_builder->where('dispatch_type_id', $search['dispatch_type_id']);
        }

        if (app('plugins')->isEnabled('shop-clerk') && ($search['shop_clerk_kwd'] || $search['shop_clerk_uid'])) {
            $clerk_query = \Yunshop\ShopClerk\models\ShopClerk::uniacid()->select('id','uid');
            if ($search['shop_clerk_kwd']) {
                $clerk_query->whereHas('hasOneMember', function ($query) use ($search) {
                    $query->where('nickname', 'like', "%{$search['shop_clerk_kwd']}%")
                        ->orWhere('mobile', 'like', "%{$search['shop_clerk_kwd']}%")
                        ->orWhere('realname', 'like', "%{$search['shop_clerk_kwd']}%");
                });
            }
            if ($search['shop_clerk_uid']) {
                $clerk_query->where('uid', $search['shop_clerk_uid']);
            }
            $clerk_ids = $clerk_query->pluck('id')->toArray() ?: [-1];
            $clerk_order_ids = \Yunshop\ShopPos\models\PosOrder::uniacid()->whereIn('clerk_id', $clerk_ids)->select('order_id')->pluck('order_id')->toArray() ?: [-1];
            $order_builder->whereIn('id', $clerk_order_ids);
        }

        if (app('plugins')->isEnabled('package-delivery')) {
            $package_where = [];
            if ($search['package_delivery_clerk_kwd']) {
                $clerk_uids = \Yunshop\PackageDelivery\models\DeliveryClerk::uniacid()->select('uid')->whereHas('hasOneMember',function ($query)use($search){
                    $query->where('nickname', 'like', "%{$search['package_delivery_clerk_kwd']}%")
                        ->orWhere('mobile', 'like', "%{$search['package_delivery_clerk_kwd']}%")
                        ->orWhere('realname', 'like', "%{$search['package_delivery_clerk_kwd']}%");
                })->pluck('uid')->toArray() ?: [-1];
                $package_where[] = [function ($query) use ($clerk_uids) {
                    $query->whereIn('confirm_uid', $clerk_uids);
                }];
            }

            if ($search['package_delivery_clerk_uid']) {
                $package_where[] = ['confirm_uid', $search['package_delivery_clerk_uid']];
            }

            if ($package_where) {
                $package_order_ids = \Yunshop\PackageDelivery\models\DeliveryOrder::uniacid()->where($package_where)->select('order_id')->pluck('order_id')->toArray() ?: [-1];
                $order_builder->whereIn('id', $package_order_ids);
            }
        }

        return $order_builder;
    }



    //订单导出订单数据
    public static function getExportOrders($search)
    {
        $builder = Order::exportOrders($search);
        $orders = $builder->get()->toArray();
        return $orders;
    }

    public function hasManyOrderGoods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id', 'id');
    }

    public function hasManyFirstOrder()
    {
        return $this->hasMany(FirstOrder::class, 'order_id', 'id');
    }

    public function hasManyMemberCertified(){
        return $this->hasOne(MemberCertified::class,'order_id','id')->orderBy('updated_at','desc');
    }

    public function orderDeliver()
    {
        return $this->hasOne(OrderDeliver::class, 'order_id', 'id');
    }

    public function scopeExportOrders(Order $query, $search)
    {
        $order_builder = $query->search($search);

        $orders = $order_builder->with([
            'belongsToMember' => self::memberBuilder(),
            'hasManyOrderGoods' => self::orderGoodsBuilder(),
            'hasOneDispatchType',
            'address',
            'hasOneOrderRemark',
            'express',
            'hasOnePayType',
            'hasOneOrderPay',
            'hasManyFirstOrder',
            'hasManyMemberCertified',
        ]);
        return $orders;
    }

    public function scopeOrders(Builder $order_builder, $search = [])
    {
        $order_builder->search($search);

        $orders = $order_builder->with([
            'belongsToMember' => self::memberBuilder(),
            'hasManyOrderGoods' => self::orderGoodsBuilder(),
            'hasOneDispatchType',
            'hasOnePayType',
            'address',
            'hasManyOrderGoods.goods.belongsToCategorys',
            'express',
            'expressmany',
            'process',
            'hasOneRefundApply' => self::refundBuilder(),
            'hasOneOrderRemark',
            'manualRefundLog',
            'hasOneOrderPay'=> function ($query) {
                $query->orderPay();
            },
            'hasManyFirstOrder',
            'hasManyMemberCertified',
            'hasOneBehalfPay',
            'memberCancel' => self::memberCancel_v(),

        ]);

        if(app('plugins')->isEnabled('electronics-bill')){
            $orders->with([
                'hasManyBillTemp' => function($q){
                    $q->groupBy('order_id');
                }
            ]);
        }

        if (app('plugins')->isEnabled('city-delivery') && \Yunshop\CityDelivery\services\SettingService::getBaseSetting()['open_state']) {
            $orders->with(['hasOneCityDelivery.hasOneAnotherOrder'])->with('hasManyCityDeliveryAnother');
        }

        return $orders;
    }

    private static function memberCancel_v()
    {
        return function ($query) {
            return $query->select(['member_id','status']);
        };
    }

    public function scopeDetailOrders(Builder $order_builder)
    {
        $orders = $order_builder->with([
            'belongsToMember' => self::memberBuilder(),
            'hasManyOrderGoods' => function($orderGoods) {
                $orderGoods->orderDetailGoods();
            },
            'hasOneDispatchType',
            'hasOnePayType',
            'address',
            'express',
            'process',
            'hasOneRefundApply' => self::refundBuilder(),
            'hasManyRefundApply' => function ($query) {
                return $query->with(['processLog','refundOrderGoods']);
            },
            'refundProcessLog' => function ($query) {
                return $query->orderBy('id', 'desc');
            },
            'hasOneOrderRemark',
            'hasOneOrderPay'=> function ($query) {
                $query->orderPay();
            },
            'deductions',
            'coupons',
            'discounts',
            'orderFees',
            'orderServiceFees',
            'orderInvoice',
            'orderPays' => function ($query) {
                $query->with('payType');
            },
            'hasOneExpeditingDelivery',
            'hasManyMemberCertified',
        ]);
        if(app('plugins')->isEnabled('exchange-code')){
            $orders->with([
                'hasOneExchangeCode.record.code'
            ]);
        }


        return $orders;
    }


    private static function refundBuilder()
    {
        return function ($query) {
            return $query->with(['returnExpress','hasManyResendExpress','changeLog','refundOrderGoods']);
        };
    }

    private static function memberBuilder()
    {
        return function ($query) {
            return $query->select(['uid', 'mobile', 'nickname', 'realname','avatar','idcard']);
        };
    }

    private static function orderGoodsBuilder()
    {
        return function ($query) {
            $query->orderGoods();
        };
    }


    public static function getOrderDetailById($order_id)
    {
        return self::orders()->with(['deductions','coupons','discounts','orderFees', 'orderServiceFees','orderPays'=> function ($query) {
            $query->with('payType');
        },'hasOnePayType','hasOneExpeditingDelivery'])->find($order_id);
    }

    /**
     * @param $keyWord
     *
     */
    public static function getOrderByName($keyWord)
    {

        return \Illuminate\Support\Facades\DB::select('select title,goods_id,thumb from '.app('db')->getTablePrefix().'yz_order_goods where title like '."'%" .$keyWord ."%'");

//        return self::uniacid()
//            ->whereHas('OrderGoods', function ($query)use ($keyWord) {
//                $query->searchLike($keyWord);
//            })
//            ->with('OrderGoods')
//            ->get();
    }

    public function hasManyBillTemp()
    {
        return $this->hasMany(ElectronicsBillTemplate::class,'order_id','id');
    }
    public function hasOneCityDelivery()
    {
        return $this->hasOne(\Yunshop\CityDelivery\models\DeliveryOrder::class, 'order_id', 'id');
    }
    public function hasManyCityDeliveryAnother()
    {
        return $this->hasMany(\Yunshop\CityDelivery\models\AnotherOrder::class, 'order_id', 'id');
    }
    public function hasManyParentTeam()
    {
        return $this->hasMany(MemberParent::class, 'member_id', 'uid');
    }

    public function hasOneTeamDividend()
    {
        return $this->hasOne('Yunshop\TeamDividend\models\TeamDividendAgencyModel', 'uid', 'uid');
    }

    public function hasOneExpeditingDelivery()
    {
        return $this->hasOne(ExpeditingDelivery::class,'order_id','id');
    }

    public function hasOnePackageDeliveryOrder()
    {
        return $this->hasOne(DeliveryOrder::class, 'order_id', 'id');
    }

    public function hasOnePackageDeliverOrder()
    {
        return $this->hasOne(PackageDeliverOrder::class, 'order_id', 'id');
    }

    public function hasOneExchangeCode()
    {
        return $this->hasOne('Yunshop\ExchangeCode\common\models\ExchangeCodePayRelation', 'order_id', 'id');
    }

}
