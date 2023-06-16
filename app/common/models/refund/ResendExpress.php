<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/23
 * Time: 上午10:49
 */

namespace app\common\models\refund;

use app\common\models\BaseModel;

/**
 * Class ResendExpress
 * @package app\common\models\refund
 */
class ResendExpress extends BaseModel
{
    public $table = 'yz_resend_express';
    protected $fillable = [];
    protected $guarded = ['id'];

    protected $attributes = [
        'pack_goods' => [],
    ];

    protected $casts = [
        'pack_goods' => 'json',
    ];


//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     */
//    public function packageGoods()
//    {
//        return $this->hasMany(RefundGoodsLog::class, 'resend_id', 'id');
//    }


}