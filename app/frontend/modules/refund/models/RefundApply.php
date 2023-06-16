<?php

namespace app\frontend\modules\refund\models;

use app\common\models\refund\RefundGoodsLog;
use app\frontend\models\Order;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/12
 * Time: 下午9:53
 */
class RefundApply extends \app\common\models\refund\RefundApply
{

    protected $appends = [
        'refund_type_name', 'status_name', 'is_refunded', 'is_refunding', 'is_refund_fail', 'plugin_id',
        'receive_status_name', 'refund_way_type_name','button_models',
    ];


    public function scopeFrontendSearch($query, $search = [])
    {
        $model = $query;


        if ($search['sn']) {

            $tag = substr($search['sn'], 0, 2);

            if ('RN' == strtoupper($tag)) {
                $model->where('yz_order_refund.refund_sn', $search['sn']);
            } else {
                $order_id = Order::where('order_sn', $search['sn'])->value('id');
                $model->where('yz_order_refund.order_id', $order_id);
            }

        }

        if ($search['order_goods_id']) {
            $refundId = RefundGoodsLog::withTrashed()->where('order_goods_id', $search['order_goods_id'])->pluck('refund_id')->unique()->toArray();

            $model->whereIn('yz_order_refund.id', $refundId);
        }


        if ($search['refund_id']) {
            $model->where('yz_order_refund.id', $search['refund_id']);
        }


        $model->with([
            'order' => self::orderBuilder(), 'refundOrderGoods',
        ]);


        $model->orderBy('yz_order_refund.id', 'desc');

        return $model;
    }




    /**
     * 前端获取退款按钮 todo 转移到前端的model
     * @return array
     */
    public function getButtonModelsAttribute()
    {
        $result = [];
        if ($this->status == self::WAIT_CHECK) {
            $result[] = [
                'name' => '修改申请',
                'api' => 'refund.edit.index',
                'value' => 1
            ];
            $result[] = [
                'name' => '取消申请',
                'api' => 'refund.operation.cancel',
                'value' => 4
            ];
        }
        if ($this->status == self::WAIT_RETURN_GOODS) {

            if (!($this->order->plugin_id == 40)) {
                $result[] = [
                    'name' => '填写快递',
                    'api' => 'refund.operation.send',
                    'value' => 2
                ];
            }
        }
        if ($this->status == self::WAIT_RECEIVE_RESEND_GOODS) {
            $result[] = [
                'name' => '确认收货',
                'api' => 'refund.operation.complete',
                'value' => 3
            ];
        }

        if ($this->refund_type == self::REFUND_TYPE_EXCHANGE_GOODS && $this->hasManyResendExpress->isNotEmpty()) {
            $result[] = [
                'name' => '查看物流',
                'api' => 'refund.express.resend-list',
                'value' => 6
            ];
        }

        return $result;
    }

    public function scopeDetail($query)
    {
        return $query->with([
            'order',
            'returnExpress',
            'hasManyResendExpress',
            'refundOrderGoods',
        ]);
    }


    protected static function boot()
    {
        parent::boot();
        self::addGlobalScope(function ($query) {
            return $query->where('uid', \YunShop::app()->getMemberId());
        });
    }

    public function scopeDefaults($query)
    {
        return $query->with([
            'order' => self::orderBuilder(),
            'refundOrderGoods',
        ])->orderBy('id', 'desc');
    }

    private static function orderBuilder()
    {
        return function ($order) {
            return $order->select(['id', 'order_sn', 'plugin_id', 'status','refund_id']);
        };
    }

    public function hasOneCstoreOrder()
    {
        return $this->hasOne(\Yunshop\CouponStore\models\StoreOrder::class, 'order_id', 'order_id');
    }

}