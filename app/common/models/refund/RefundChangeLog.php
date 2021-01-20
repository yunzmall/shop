<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/31
 * Time: 17:59
 */

namespace app\common\models\refund;


use app\common\models\BaseModel;

class RefundChangeLog extends BaseModel
{
    public $table = 'yz_order_refund_change_log';
    protected $fillable = [];
    protected $guarded = ['id'];
}