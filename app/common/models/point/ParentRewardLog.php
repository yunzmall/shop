<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/6/18
 * Time: 13:37
 */

namespace app\common\models\point;


use app\common\models\BaseModel;
use app\common\models\Member;
use app\common\models\Order;

/**
 * Class RechargeModel
 * @package app\common\models\point
 */
class ParentRewardLog extends BaseModel
{

    protected $table = 'yz_parent_point_reward_log';

    protected $guarded = [];

    public function hasOneOrder()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function hasOneMember()
    {
        return $this->hasOne(Member::class, 'uid', 'uid');
    }

}
