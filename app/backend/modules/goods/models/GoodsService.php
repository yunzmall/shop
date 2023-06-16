<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/18
 * Time: 17:07
 */

namespace app\backend\modules\goods\models;


use Illuminate\Support\Carbon;

class GoodsService extends \app\common\models\goods\GoodsService
{
    public static function relationSave($goodsId, $data, $operate = '')
    {
        if (!$goodsId) {
            return false;
        }
        if (!$data) {
            return false;
        }
        $model = self::getGoodsModel($goodsId, $operate);
        //判断deleted
        if ($operate == 'deleted') {
            return $model->delete();
        }
        $attr['goods_id'] = $goodsId;
        $attr['uniacid'] = \YunShop::app()->uniacid;
        $attr['serviceFee'] = $data['service_fee'];
        $attr['is_automatic'] = is_null($data['is_automatic'])?0:$data['is_automatic'];
        $attr['on_shelf_time'] = $data['starttime'];
        $attr['lower_shelf_time'] = $data['endtime'];
        if (isset($data['is_refund'])) {
            $attr['is_refund'] = $data['is_refund'];
        };
        $attr['time_type'] = $data['time_type'];
        $attr['loop_date_start'] = Carbon::createFromTimestamp($data['loop_date_start'])->startOfDay()->timestamp;
        $attr['loop_date_end'] = Carbon::createFromTimestamp($data['loop_date_end'])->endOfDay()->timestamp;
        $attr['loop_time_up'] = $data['loop_time_up'];
        $attr['loop_time_down'] = $data['loop_time_down'];
        $attr['auth_refresh_stock'] = $data['auth_refresh_stock'];
        $attr['original_stock'] = $data['original_stock'];
        $model->fill($attr);
        return $model->save();
    }

    public static function getGoodsModel($goodsId, $operate)
    {
        $model = false;
        if ($operate != 'created') {
            $model = static::where(['goods_id' => $goodsId])->first();
        }
        !$model && $model = new static;
        return $model;
    }
}
