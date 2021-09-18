<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/20 0020
 * Time: 下午 3:48
 */

namespace app\common\models\goods;


use app\common\exceptions\AppException;
use app\common\models\BaseModel;
use app\common\models\Goods;
use Carbon\Carbon;

/**
 * Class GoodsLimitBuy
 * @package app\common\models\goods
 * @property Goods $goods
 */
class GoodsLimitBuy extends BaseModel
{
    protected $table = 'yz_goods_limitbuy';

    public $timestamps = false;

    protected $guarded = [''];

    protected $appends= [];

    public function getDisplayNameAttribute($value)
    {
        if (empty($value)) {
            return '限时购';
        }

        return $value;
    }

    static function getDataByGoodsId($goods_id)
    {
        return self::uniacid()
            ->where('goods_id', $goods_id)
            ->first();
    }
    public function goods(){
        return $this->belongsTo(Goods::class);
    }
    public function check(){
        if ($this->status) {
            $startTime = Carbon::createFromTimestamp($this->start_time);
            if (Carbon::now()->lessThan($startTime)) {
                throw new AppException('此商品将于' . $startTime->toDateTimeString() . '开启限时购买');
            }
            $endTime = Carbon::createFromTimestamp($this->end_time);

            if (Carbon::now()->greaterThanOrEqualTo($endTime)) {
                throw new AppException('商品['.$this->goods->title.']已于' . $endTime->toDateTimeString() . '结束限时购买');
            }
        }
    }
}
