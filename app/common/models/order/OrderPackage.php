<?php
namespace app\common\models\order;

use app\common\models\BaseModel;
use app\common\models\OrderGoods;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OrderPackage
 * @package app\common\models\order
 * @property OrderGoods orderGoods
 */
class OrderPackage extends BaseModel
{
//    use SoftDeletes;

    public $table = 'yz_order_package';
    public $dateFormat = 'U';
    public $timestamps = true;
    public $hidden = ['updated_at', 'deleted_at'];
    protected $guarded = [''];

    /**
     * 获取物流包裹
     * @param int $order_id
     * @return \app\framework\Database\Eloquent\Collection
     */
    public static function getOrderPackage(int $order_id = 0)
    {
        return static::uniacid()->where('order_id', $order_id)->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function orderGoods()
    {
        return $this->hasOne(OrderGoods::class, 'id', 'order_goods_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneExpress()
    {
        return $this->hasOne(Express::class, 'id', 'order_express_id');
    }
}