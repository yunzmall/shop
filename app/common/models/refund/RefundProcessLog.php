<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/23
 * Time: 14:14
 */

namespace app\common\models\refund;


use app\common\models\BaseModel;

class RefundProcessLog extends BaseModel
{
    protected $table = 'yz_order_refund_process_log';

    protected $fillable = [];

    protected $guarded = ['id'];

    protected $hidden = ['updated_at',];

    protected $appends = ['operate_name'];


    protected $casts = [
        'detail' => 'json',
        'remark' => 'json',
    ];


    const OPERATOR_SHOP = 0;  //操作者 商城

    const OPERATOR_MEMBER = 1; //操作者 会员

    const OPERATE_CANCEL = -2;
    const OPERATE_REJECT = -1;
    const OPERATE_APPLY = 0;
    const OPERATE_AGREE_APPLY = 1;
    const OPERATE_USER_SEND_BACK = 2;
    const OPERATE_SHOP_BATCH_RESEND = 3;
    const OPERATE_SHOP_RESEND = 4;
    const OPERATE_REFUND_COMPLETE = 6;
    const OPERATE_REFUND_CONSENSUS = 7;
    const OPERATE_APPLY_SHOP = 8;

    //其他操作从10开启，10以下为退款表状态
    const OPERATE_CHANGE_APPLY = 10;
    const OPERATE_CHANGE_AMOUNT = 11;
    const OPERATE_EDIT = 12;


    /**
     * @param RefundApply $refund
     * @param int $operator
     * @return static
     */
    public static function logInstance(RefundApply $refund, $operator = 0)
    {

        $operator_id = $operator == static::OPERATOR_MEMBER ? $refund->order->uid : \YunShop::app()->uid;

        $processLog = new static([
            'refund_id' => $refund->id,
            'order_id' => $refund->order->id,
            'operator' => $operator,
            'operator_id' => $operator_id?:0,
        ]);

        return $processLog;
    }

    /**
     *
     * @param array $detail
     * @param array $remark
     */
    public function saveLog($detail, $remark = [])
    {
        $this->setAttribute('detail', $detail);
        $this->setAttribute('remark', $remark);
        return $this->save();
    }


    public function getOperateNameAttribute()
    {
        return $this->getOperateNameComment($this->attributes['operate_type']);
    }

    public function getOperateNameComment($attribute)
    {
        return isset($this->operateComment()[$attribute]) ? $this->operateComment()[$attribute] : "未知类型";
    }

    public function operateComment()
    {
        return [
            self::OPERATE_CANCEL                    => '用户关闭申请',
            self::OPERATE_REJECT                    => '商家驳回申请',
            self::OPERATE_APPLY                     => '用户发起申请',
            self::OPERATE_AGREE_APPLY               => '商家同意申请',
            self::OPERATE_USER_SEND_BACK            => '用户已退货，等待商家收货',
            self::OPERATE_SHOP_BATCH_RESEND         => '商家分批发货，等待买家收货',
            self::OPERATE_SHOP_RESEND               => '商家已发货，等待买家收货',
            self::OPERATE_REFUND_COMPLETE           => '售后完成',
            self::OPERATE_REFUND_CONSENSUS          => '商家手动完成退款',
            self::OPERATE_APPLY_SHOP                => '商家操作退款',
            self::OPERATE_CHANGE_APPLY              => '用户修改申请',
            self::OPERATE_CHANGE_AMOUNT             => '商家修改退款金额',
            self::OPERATE_EDIT                      => '商家编辑售后',

        ];
    }

}