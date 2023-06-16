<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/8/24
 * Time: 17:04
 */

namespace app\common\models;


class WechatMinAppPayOrder extends BaseModel
{
    protected $table = 'yz_min_app_pay_manage_order';

    protected $guarded = [''];

    protected $casts = [
        'notice_params' => 'json',
    ];

    protected $attributes = [
        'status' => 0,
    ];

    /**
     * @param $trade_no
     * @return WechatMinAppPayOrder
     */
    public static function existOrNew($trade_no)
    {
        $model = self::where('trade_no',$trade_no)->first();

        if (is_null($model)) {
            $model = new self(['uniacid' => \YunShop::app()->uniacid]);
        }

        return $model;
    }

    public function getPayTimeStrAttribute()
    {
        return $this->pay_time?date('Y-m-d H:i:s', $this->pay_time) : '';
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 1:
                $status_name = '待分账';
                break;
            case 2:
                $status_name = '已分账';
                break;

            default:
                $status_name = '未支付';
        }

        return $status_name;
    }
}