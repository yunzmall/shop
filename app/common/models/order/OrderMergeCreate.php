<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/12/22
 * Time: 13:48
 */

namespace app\common\models\order;

use app\common\models\BaseModel;

/**
 * Class OrderMergeCreate
 * @package app\common\models\order
 * @property array order_ids
 * @property int uniacid
 */
class OrderMergeCreate extends BaseModel
{
    public $table = 'yz_order_merge_create';
    public $timestamps = true;
    protected $guarded = [''];
    protected $appends = [];

    public function getOrderIdsAttribute($value)
    {
        return explode(',',$value);
    }

    public static function saveData($order_ids = '')
    {
        if (!$order_ids) {
            return false;
        }
        $data = [
            'uniacid' => \YunShop::app()->uniacid,
            'order_ids' => $order_ids
        ];
        $model = new self();
        $model->fill($data);
        return $model->save();
    }
}