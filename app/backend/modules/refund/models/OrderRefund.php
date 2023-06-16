<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/10/20
 * Time: 16:18
 */

namespace app\backend\modules\refund\models;


use app\backend\modules\order\models\VueOrder;
use app\common\models\OrderGoods;
use app\common\models\refund\RefundGoodsLog;
use app\framework\Database\Eloquent\Builder;

/**
 * Class OrderRefund
 * @method static self backendSearch($search)
 * @package app\backend\modules\refund\models
 */
class OrderRefund extends \app\common\models\refund\RefundApply
{
    protected $appends = [
        'plugin_id','refund_type_name', 'status_name',
        'receive_status_name', 'refund_way_type_name', 'order_type_name',
    ];

    public function getOrderTypeNameAttribute()
    {
        return $this->order->getOrderType()->getName();
    }

    public function getBackendButtonModels()
    {
        return (new \app\backend\modules\refund\services\BackendRefundButtonService($this))->getButtonModels();
    }

    public function getBackendRefundSteps()
    {
        return (new \app\backend\modules\refund\services\steps\RefundStatusStepManager($this))->getStepItems();
    }

    public static function detail($id)
    {
        return self::with([
            'hasOneMember' => function ($member) {
                $member->select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
                    'credit1', 'credit2',]);
            },
            'order',
            'refundOrderGoods',
            'processLog',
            'returnExpress',
            'hasManyResendExpress',
            'changeLog',
        ])->find($id);
    }


    public function scopeBackendSearch(Builder $query, $search = [])
    {
        $model = $query->select('yz_order_refund.*');


        if ($search['order_sn']) {

            $model->leftJoin('yz_order', 'yz_order_refund.order_id', 'yz_order.id')
                ->where('yz_order.order_sn',  trim($search['order_sn']));
            //$order_id = Order::where('order_sn', $search['order_sn'])->value('id');
            //$model->where('yz_order_refund.order_id', $order_id);
        }

        if ($search['refund_sn']) {
            $model->where('yz_order_refund.refund_sn', trim($search['refund_sn']));

        }

        if (isset($search['refund_type']) && is_numeric($search['refund_type'])) {
            $model->where('refund_type', $search['refund_type']);
        }

        if (isset($search['status']) && is_numeric($search['status'])) {

            if ($search['status'] == 99) {
                $model->whereIn('status', [self::COMPLETE,self::CONSENSUS,self::CLOSE]);
            } else {
                $model->where('status', $search['status']);
            }
        }


        if ($search['member_id']) {
            $model->where('yz_order_refund.uid', $search['member_id']);
        }

        if (!empty($search['member_info']) && isset($search['member_type'])) {

            $model->whereHas('hasOneMember', function ($member) use ($search) {
                $member->select('uid', 'nickname', 'realname', 'mobile', 'avatar')
                    ->where(function ($query) use ($search) {
                        switch ($search['member_type']) {
                            case 1 :
                                $query->where('nickname', 'like', '%' . $search['member_info'] . '%');
                                break;
                            case 2 :
                                $query->where('realname', 'like', '%' . $search['member_info'] . '%');
                                break;
                            case 3 :
                                $query->where('mobile', 'like', '%' . $search['member_info'] . '%');
                                break;
                            default :
                        }
                    });
            });
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

            $order_ids = $orderGoodsModel->pluck('order_id')->unique()->toArray();

            $model->whereIn('yz_order_refund.order_id', $order_ids);
        }


        //操作时间范围
        if ($search['start_time'] && $search['end_time'] && $search['time_field']) {
            $range = [strtotime($search['start_time']), strtotime($search['end_time'])];

            $model->whereBetween('yz_order_refund.'.$search['time_field'], $range);
        }


        $model->with([
            'hasOneMember' => function ($member) {
                $member->select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
                    'credit1', 'credit2',]);
            },
            'order' => function ($order) {
                $order->with(['address']);
            },
            'refundOrderGoods',
        ]);



        return $model;
    }

    public function order()
    {
        return $this->belongsTo(VueOrder::class, 'order_id', 'id');
    }
}