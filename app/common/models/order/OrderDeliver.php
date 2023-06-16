<?php
/**
 * Author:
 * Date: 2019-11-19
 * Time: 17:10
 */

namespace app\common\models\order;


use app\common\models\BaseModel;
use app\common\models\OrderAddress;
use Yunshop\PackageDeliver\model\Deliver;
use Yunshop\PackageDeliver\model\DeliverClerk;

class OrderDeliver extends BaseModel
{
    public $table = 'yz_order_deliver';
    public $timestamps = true;
    protected $guarded = [''];

    // 不调用不执行 自提点
    public function deliver()
    {
        return $this->hasOne(Deliver::class, 'id', 'deliver_id');
    }
    // 不调用不执行 核销员
    public function clerk()
    {
        return $this->hasOne(DeliverClerk::class, 'id', 'clerk_id');
    }

    public function hasOneOrderAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id', "order_id");
    }

}
