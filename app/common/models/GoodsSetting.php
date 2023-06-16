<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/28
 * Time: 13:48
 */

namespace app\common\models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class GoodsSpec
 * @package app\common\models
 * @property int uniacid
 * @property int goods_id
 */
class GoodsSetting extends \app\common\models\BaseModel
{
    use SoftDeletes;

    public $timestamps = true;

    public $table = 'yz_goods_setting';
    
    public $guarded = [];

    public static function getSet($column = '')
    {
        if ($column) {
            return static::uniacid()->first()->$column;
        } else {
            return static::uniacid()->first();
        }
    }
}