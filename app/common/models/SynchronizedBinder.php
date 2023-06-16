<?php
/**
 * Created by PhpStorm.
 * Class Withdraw
 * Author: Yitan
 * Date: 2017/11/06
 * @package app\common\models
 */

namespace app\common\models;


use Illuminate\Support\Facades\Config;
use app\common\traits\CreateOrderSnTrait;

/**
 * Class Withdraw
 * @package app\common\models
 * @property int id
 * @property int type_id
 * @property float amounts
 * @property int status
 */
class SynchronizedBinder extends BaseModel
{
    protected $table = 'yz_synchronized_binder';
    protected $guarded = [''];
    public $timestamps = false;

    public static function searchLog($search)
    {
        $model = self::select(['id', 'old_uid', 'new_uid', 'created_at'])->uniacid();

        if ($search['member_id']) {
            $model->where('old_uid', $search['member_id']);
        }

        if ($search['mark_member_id']) {
            $model->where('new_uid', $search['mark_member_id']);
        }

        return $model;
    }
}