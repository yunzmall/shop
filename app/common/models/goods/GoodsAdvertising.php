<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/11
 * Time: 11:31
 */

namespace app\common\models\goods;

use app\common\models\BaseModel;
use app\common\models\Goods;

class GoodsAdvertising extends BaseModel
{
    protected $table = 'yz_goods_advertising';

    protected $guarded = [''];

    public static function getGoodsData($goods_id)
    {
        return self::uniacid()
            ->where('goods_id', $goods_id)
            ->first();
    }

    public function goods(){
        return $this->belongsTo(Goods::class);
    }
}