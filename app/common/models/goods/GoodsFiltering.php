<?php
/**
 * Author:
 * Date: 2018/3/30
 */

namespace app\common\models\goods;

use app\common\models\BaseModel;
use app\common\models\SearchFiltering;

class GoodsFiltering extends BaseModel
{

    public $table = 'yz_goods_filtering';

    public $timestamps = false;

    protected $guarded = [];

    public function scopeOfGoodsId($query, $goodsId)
    {
        return $query->where('goods_id', $goodsId);
    }

    public function hasOneSearchFilter()
    {
        return $this->hasOne(SearchFiltering::class,'id','filtering_id');
    }
}