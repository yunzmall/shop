<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/6
 * Time: 上午11:42
 */

namespace app\common\models;

use app\backend\modules\goods\observers\SaleObserver;

/**
 * Class Sale
 * @package app\common\models
 * @property float ed_num
 * @property float ed_money
 */
class Sale extends BaseModel
{
    public $table = 'yz_goods_sale';

    public $attributes = [
        'max_point_deduct' => '',
        'min_point_deduct' => '',
        'max_balance_deduct' => '',
        'min_balance_deduct' => '',
        'has_all_point_deduct' => 0,
        'all_point_deduct' => 0,
        'is_sendfree' => 0,
        'ed_num' => '',
        'ed_full' => 0,
        'ed_reduction' => 0,
        'ed_money' => '',
        'point' => '',
        'bonus' => 0,
        'award_balance' => 0,
        'pay_reward_balance' => 0,
        'point_type' => 0,
        'max_once_point' => 0,

        'ed_areas' => '',
        'push_goods_ids' => 0,
        'is_push' => 0,
    ];


    protected $appends = ['point_deduct_type'];

    protected $guarded = [''];


    public function getPointDeductTypeAttribute()
    {
        if (isset($this->attributes['point_deduct_type'])) {
            return $this->attributes['point_deduct_type'];
        }

        if (strexists($this->getOriginal('max_point_deduct'), '%') || strexists($this->getOriginal('min_point_deduct'), '%')) {
            return 1;
        }

        return 0;
    }

    public static function boot()
    {
        parent::boot();
        //注册观察者
        static::observe(new SaleObserver);
    }
}