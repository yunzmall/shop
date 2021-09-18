<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/11/28
 * Time: 16:44
 */

namespace app\common\modules\wechat\models;


use app\common\models\BaseModel;
use app\common\models\Order;

/**
 * @property int account_id
 * @property boolean profit_sharing
 * @property string transaction_id
 * Class WechatPayOrder
 * @package app\common\modules\wechat\models
 */
class WechatPayOrder extends BaseModel
{
    public $table = 'yz_wechat_pay_order';
    public $guarded = [''];

    public function scopeSearch($query, $search)
    {
        if ($search['transaction_id']) {
            $query->where('transaction_id', $search['transaction_id']);
        }
        if ($search['order_sn']) {
            $query->whereHas('hasOneOrder',function ($q2) use ($search) {
                $q2->where('order_sn', $search['order_sn']);
            });
        }
        if ($search['profit_sharing'] != '') {
            $query->where('profit_sharing', $search['profit_sharing']);
        }
        if ($search['is_time']) {
            $query->whereBetween('created_at', [strtotime($search['time']['start']), strtotime($search['time']['end'])]);
        }
        return $query;
    }
    public function hasOneOrder()
    {
        return $this->hasOne(Order::class,'id','order_id');
    }
}