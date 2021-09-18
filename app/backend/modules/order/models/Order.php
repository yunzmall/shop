<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/7
 * Time: 下午2:59
 */

namespace app\backend\modules\order\models;

use app\backend\modules\member\models\MemberParent;
use app\backend\modules\order\services\OrderService;
use app\common\models\ExpeditingDelivery;
use app\common\models\order\FirstOrder;
use app\common\models\order\OrderDeliver;
use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Support\Facades\DB;
use app\common\models\PayTypeGroup;
use app\common\models\MemberCertified;

/**
 * Class Order
 * @package app\backend\modules\order\models
 * @method static self exportOrders($search)
 * @method static self search($search)
 */
class Order extends \app\common\models\Order
{

    protected $appends = ['status_name', 'pay_type_name'];


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
        if ($search['first_order']) {
            $query->whereHas('hasManyFirstOrder');
        }

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
            'hasManyMemberCertified'
        ]);
        return $orders;
    }

    public function scopeOrders(Builder $order_builder, $search = [])
    {
        if ($search['first_order']) {
            $order_builder->whereHas('hasManyFirstOrder');
        }
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
            'orderDeliver',
            'hasManyMemberCertified'
        ]);
        return $orders;
    }



    private static function refundBuilder()
    {
        return function ($query) {
            return $query->with('returnExpress')->with('resendExpress');
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

    public function scopeSearch($order_builder, $params)
    {
//        print_r($params['ambiguous']['field']);exit;
        if (array_get($params, 'ambiguous.field', '') && array_get($params, 'ambiguous.string', '')) {
            //订单.支付单号
            if ($params['ambiguous']['field'] == 'order') {
                call_user_func(function () use (&$order_builder, $params) {
                    list($field, $value) = explode(':', $params['ambiguous']['string']);
                    if (isset($value)) {
                        return $order_builder->where($field, $value);
                    } else {
//                        return $order_builder->where(function ($query) use ($params) {
//                            $query->orWhere('yz_order.order_sn', $params['ambiguous']['string']);
//                            $query->orWhereHas('hasOneOrderPay', function ($query) use ($params) {
//                                $query->where('pay_sn', $params['ambiguous']['string']);
//                            });
//                        });

                        return $order_builder->addSelect('yz_order.*')->leftjoin('yz_order_pay', 'yz_order_pay.id', '=', 'yz_order.order_pay_id')->where(function ($query) use ($params) {
                                $query->orWhere('yz_order.order_sn', 'like', "%{$params['ambiguous']['string']}%")
                                    ->orWhere('yz_order_pay.pay_sn', 'like', "%{$params['ambiguous']['string']}%");
                        });


                    }
                });

            }

            //用户
            if ($params['ambiguous']['field'] == 'member') {
                call_user_func(function () use (&$order_builder, $params) {
                    list($field, $value) = explode(':', $params['ambiguous']['string']);
                    if (isset($value)) {
                        return $order_builder->where($field, $value);
                    } else {
                        return $order_builder->join('mc_members', function ($join)  use ($params) {
                            $join->on('mc_members.uid', '=', 'yz_order.uid')->where(function ($where) use ($params) {
                                return $where->whereRaw('LOCATE(?, realname)', [$params['ambiguous']['string']])
                                    ->orWhereRaw('LOCATE(?, mobile)', [$params['ambiguous']['string']])
                                    ->orWhereRaw('LOCATE(?, nickname)', [$params['ambiguous']['string']]);
                            });
                        });


//                        return $order_builder->whereHas('belongsToMember', function ($query) use ($params) {
//                            return $query->where('realname', 'like', '%' . $params['ambiguous']['string'] . '%')
//                                ->orWhere('mobile', 'like', '%' . $params['ambiguous']['string'] . '%')
//                                ->orWhere('nickname', 'like', '%' . $params['ambiguous']['string'] . '%');
//                        });
                    }
                });

            }

            //根据会员id搜索
            if ($params['ambiguous']['field'] == 'member_id') {
                call_user_func(function () use (&$order_builder, $params) {
                    return $order_builder->where('uid', $params['ambiguous']['string']);
                });

            }

            //增加地址,姓名，手机号搜索
            if ($params['ambiguous']['field'] == 'address') {
                call_user_func(function () use (&$order_builder, $params) {
                    list($field, $value) = explode(':', $params['ambiguous']['string']);
                    if (isset($value)) {
                        return $order_builder->where($field, $value);
                    } else {
                        return $order_builder->whereHas('address', function ($query) use ($params) {
                            return $query->where('address','like', '%' . $params['ambiguous']['string'] . '%')
                                          ->orWhere('mobile','like','%' . $params['ambiguous']['string'] . '%')
                                          ->orWhere('realname','like','%' . $params['ambiguous']['string'] . '%');
                        });
                    }
                });
            }

            //增加根据优惠券名称搜索订单
            if($params['ambiguous']['field'] == 'coupon'){
                call_user_func(function () use (&$order_builder, $params) {
                    list($field, $value) = explode(':', $params['ambiguous']['string']);
                    if (isset($value)) {
                        return $order_builder->where($field, $value);
                    } else {
                        return $order_builder->whereHas('coupons', function ($query) use ($params) {
                            return $query->where('name','like','%' . $params['ambiguous']['string'] . '%');
                        });
                    }
                });
            }

            //订单商品
//            if ($params['ambiguous']['field'] == 'order_goods') {
//                $order_builder->whereHas('hasManyOrderGoods', function ($query) use ($params) {
//                    $query->searchLike($params['ambiguous']['string']);
//                });
//            }

            //商品id
            if ($params['ambiguous']['field'] == 'goods_id') {

                $order_builder->whereHas('hasManyOrderGoods', function ($query) use ($params) {
                    $query->where('goods_id',$params['ambiguous']['string']);
                });
            }

            if ($params['ambiguous']['field'] == 'goods_title') {
                $order_builder->whereHas('hasManyOrderGoods', function ($query) use ($params) {
                    $query->where('title', 'like','%' . $params['ambiguous']['string'] . '%');
                });
            }

            //快递单号
            if ($params['ambiguous']['field'] == 'dispatch') {
                $order_builder->whereHas('express', function ($query) use ($params) {
                    $query->searchLike($params['ambiguous']['string']);
                });
            }

        }
        //支付方式
        if (array_get($params, 'pay_type', '')) {
            /* 改为按支付分组方式查询后，该部分被替换
            $order_builder->where('pay_type_id', $params['pay_type']);
            */
            //改为支付分组查询，前端传入支付分组id，在该处通过分组id获取组中所有成员，这些成员就是确切的支付方式
            //如前端传入的分组id为2，对应的是支付宝支付分组，然后查找属于支付宝支付组的支付方式，找到如支付宝，支付宝-yz这些具体支付方式
            //获取到确切的支付方式后，对查询条件进行拼接
            $payTypeGroup = PayTypeGroup::with('hasManyPayType')->find($params['pay_type']);

            if($payTypeGroup) {
                $payTypes = $payTypeGroup->toArray();
                if($payTypes['has_many_pay_type']) {
                    $pay_type_ids = array_column($payTypes['has_many_pay_type'], 'id');
                    $order_builder->whereIn('yz_order.pay_type_id', $pay_type_ids);
                }
            } else {
                $order_builder->where('yz_order.pay_type_id', $params['pay_type']);
            }
        }
        //操作时间范围
        if (array_get($params, 'time_range.field', '') && array_get($params, 'time_range.start', 0) && array_get($params, 'time_range.end', 0)) {
            $range = [strtotime($params['time_range']['start']), strtotime($params['time_range']['end'])];
            $order_builder->whereBetween('yz_order'.'.'.$params['time_range']['field'], $range);
        }
        return $order_builder;
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

