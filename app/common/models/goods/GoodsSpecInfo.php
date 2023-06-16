<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/10
 * Time: 14:04
 */

namespace app\common\models\goods;

use app\common\models\BaseModel;

/**
 * Class GoodsSpecInfo
 * @package app\common\models\goods
 * @property int uniacid
 * @property int goods_id
 * @property int goods_option_id
 * @property string info_img
 * @property int sort
 * @property array content
 */
class GoodsSpecInfo extends BaseModel
{
    protected $table = 'yz_goods_spec_info';

    public $timestamps = true;

    protected $guarded = [''];

    protected $appends= ['info_img_src'];

    public function getContentAttribute($value)
    {
        return json_decode($value,true);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = json_encode($value);
    }

    public function getInfoImgSrcAttribute()
    {
        return yz_tomedia($this->info_img);
    }
}