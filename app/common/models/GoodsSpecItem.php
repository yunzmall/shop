<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/1
 * Time: 09:41
 */

namespace app\common\models;

/**
 * Class GoodsSpecItem
 * @package app\common\models
 * @property int uniacid
 * @property int specid
 */
class GoodsSpecItem extends \app\common\models\BaseModel
{
    public $table = 'yz_goods_spec_item';

    public $guarded = [];

    //public $timestamps = true;

    public function hasManyOption()
    {
        return $this->hasMany('app\common\models\GoodsOption');
    }

    public function hasOneSpec()
    {
        return $this->hasOne(GoodsSpec::class, 'id', 'specid');
    }

}