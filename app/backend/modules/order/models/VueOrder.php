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
use app\common\models\order\FirstOrder;
use app\common\models\order\OrderDeliver;
use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Support\Facades\DB;
use app\common\models\PayTypeGroup;
use app\common\models\MemberCertified;

/**
 * Class VueOrder
 * @package app\backend\modules\order\models
 */
class VueOrder extends \app\common\models\Order
{

    protected $appends = ['backend_button_models', 'status_name', 'pay_type_name'];


    protected $orderType;

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
        return  $this->getOrderType()->rowHeadShow();
    }

    /**
     * 订单列表固定按钮
     * @return array
     */
    public function getFixedButtonAttribute()
    {
        return  $this->getOrderType()->fixedButton();
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
            $status =  $search['order_status'] == 'waitPay' ? 0 : $search['order_status'];
            $order_builder->where('yz_order.status', $status);
        }

        if ($search['order_sn']) {
            $order_builder->where('yz_order.order_sn', 'like', "%".trim($search['order_sn'])."%");
        }

        if ($search['pay_sn']) {
            $order_builder->join('yz_order_pay', 'yz_order_pay.id', '=', 'yz_order.order_pay_id')
                ->where('yz_order_pay.pay_sn', 'like', "%".trim($search['pay_sn'])."%");
        }



        //根据会员id搜索
        if ($search['member_id']) {
            $order_builder->where('yz_order.uid', $search['member_id']);
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
        if ($search['goods_id'] || $search['goods_title']) {
            $orderGoodsModel = OrderGoods::uniacid();
            if ($search['goods_id']) {
                $orderGoodsModel->where('goods_id', $search['goods_id']);
            }
            if ($search['goods_title']) {
                $orderGoodsModel->where('title', 'like', "%".trim($search['goods_title'])."%");
            }
            $goods_order_ids = $orderGoodsModel->pluck('order_id')->unique()->toArray();

            if ($goods_order_ids) {
                $order_builder->whereIn('yz_order.id', $goods_order_ids);
            }
            //todo 同一笔订单商品相同规格不同会查询出两条数据
            // if ($search['goods_id']) {
            //     $order_builder->join('yz_order_goods', 'yz_order_goods.order_id', '=', 'yz_order.id')->where(function ($query) use ($search) {
            //         $query->where('yz_order_goods.goods_id', $search['goods_id']);
            //     });
            //
            //     $order_builder->whereHas('hasManyOrderGoods', function ($query) use ($search) {
            //         $query->where('goods_id', $search['goods_id']);
            //     });
            // }
            //
            // if ($search['goods_title']) {
            //
            //     $order_builder->join('yz_order_goods', 'yz_order_goods.order_id', '=', 'yz_order.id')->where(function ($query) use ($search) {
            //         $query->where('yz_order_goods.title', 'like', "%{$search['goods_title']}%");
            //     });
            //
            //     $order_builder->whereHas('hasManyOrderGoods', function ($query) use ($search) {
            //         $query->where('title', 'like', "%{$search['goods_title']}%");
            //     });
            // }
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
            'express',
            'process',
            'hasOneRefundApply' => self::refundBuilder(),
            'hasOneOrderRemark',
            'hasOneOrderPay'=> function ($query) {
                $query->orderPay();
            },
            'hasManyFirstOrder',
            'hasManyMemberCertified',
            'hasOneBehalfPay',
            'memberCancel' => self::memberCancel_v(),

        ]);
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
            'hasOneOrderRemark',
            'hasOneOrderPay'=> function ($query) {
                $query->orderPay();
            },
        ]);
        return $orders;
    }


    private static function refundBuilder()
    {
        return function ($query) {
            return $query->with('returnExpress')->with('resendExpress')->with('changeLog');
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

}