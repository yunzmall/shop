<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022-12-07
 * Time: 11:40
 */

namespace app\backend\modules\goods\models;


use app\common\models\BaseModel;

class GoodsTradeSet extends BaseModel
{
    public $table = 'yz_goods_trade_set';
    public $guarded = [];

    public static function relationSave($goodsId, $data, $operate)
    {
        if (!$goodsId || !$data) {
            return false;
        }
        $model = self::getModel($goodsId, $operate);
        if ($operate == 'deleted') {
            return $model->delete();
        }
        $data['goods_id'] = $goodsId;
        $data['uniacid'] = \YunShop::app()->uniacid;
        $model->setRawAttributes($data);
        return $model->save();
    }

    public static function getModel($goodsId, $operate)
    {
        $model = false;
        if ($operate != 'created') {
            $model = static::where(['goods_id' => $goodsId])->first();
        }
        if (!$model) {
            $model = new static;
        }
        return $model;
    }
}