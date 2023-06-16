<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/2/20
 * Time: 15:39
 */

namespace app\common\models;
use app\common\modules\refund\RefundPayAdapter;


/**
 * Class PayCallbackException
 * @property string pay_sn
 * @property integer frequency
 * @property integer pay_type_id
 * @property integer error_code
 * @property string error_msg
 * @property array result
 * @property array response
 * @property integer status
 * @package app\common\models
 */
class PayCallbackException extends BaseModel
{
    public $table = 'yz_pay_callback_exception';

    protected $guarded = ['id'];

    protected $hidden = ['updated_at','deleted_at'];

    protected $dates = ['record_at'];


    protected $appends = ['status_name','pay_type_name'];


    protected $attributes = [
        'status' => 0,
    ];

    protected $casts = [
        'result' => 'json',
        'response' => 'json',
    ];

    const ORDER_CLOSE = 1;

    const INITIAL = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = -1;

    public static function getList($search = [])
    {
        $model = self::uniacid();

        if ($search['pay_sn']) {
            $model->where('pay_sn', trim($search['pay_sn']));
        }

        if ($search['order_sn']) {

            $pay_sn_array = OrderPay::whereHas('orders', function ($order) use ($search) {
                $order->where('order_sn', trim($search['order_sn']));
            })->pluck('pay_sn')->unique()->toArray();

            $model->whereIn('pay_sn', $pay_sn_array);
        }

        if (isset($search['status']) && is_numeric($search['status'])) {
            $model->where('status', $search['status']);
        }


        if ($search['start_time'] && $search['end_time']) {
            $range = [strtotime($search['start_time']), strtotime($search['end_time'])];
            $model->whereBetween('record_at', $range);
        }

        return $model;
    }


    /**
     * @return array
     */
    public function refund()
    {
        $payAdapter = new RefundPayAdapter($this->pay_type_id);

        $totalmoney = $this->orderPay->amount; //订单总金额

        try {
            $result =  $payAdapter->pay($this->pay_sn, $totalmoney, $totalmoney);

            if ($result['status']) {
                $this->orderPay->updateRefund($this->orderPay->id);
                $this->status = self::STATUS_SUCCESS;
                $this->save();
            } else {
                $this->status = self::STATUS_FAIL;
                $this->handle_msg = $result['msg'];
                $this->save();
            }

            return $result;
        } catch (\Exception $e) {

            \Log::debug('支付回调异常退款失败:'.$e->getMessage(), $result);

            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }



    public function getCodeNameAttribute()
    {
        switch ($this->error_code) {
            case self::ORDER_CLOSE:
                $name = '订单已关闭';
                break;
            default:
                $name = '默认';
                break;
        }

        return $name;
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 1:
                $name = '已处理';
                break;
            case -1:
                $name = '退款失败';
                break;
            default:
                $name = '未处理';
                break;
        }

        return $name;
    }

    /**
     * 支付类型汉字
     * @return string
     */
    public function getPayTypeNameAttribute()
    {
        if ($this->pay_type_id == PayType::CREDIT) {
            $set = \Setting::get('shop.shop');
            return ($set['credit'] ?: '余额');
        }
        return $this->hasOnePayType->name;
    }

    public function hasOnePayType()
    {
        return $this->hasOne(PayType::class,'id', 'pay_type_id');
    }


    public function orderPay()
    {
        return $this->hasOne(OrderPay::class,'pay_sn', 'pay_sn');
    }
}