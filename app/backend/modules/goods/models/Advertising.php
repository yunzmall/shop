<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/11
 * Time: 15:05
 */

namespace app\backend\modules\goods\models;

use app\common\models\goods\GoodsAdvertising;
use app\common\traits\MessageTrait;

class Advertising extends GoodsAdvertising
{
    use MessageTrait;

    public static function relationSave($goodsId, $data, $operate)
    {
        if (!$goodsId) {
            return false;
        }
        if (!$data) {
            return false;
        }
        $saleModel = self::getModel($goodsId, $operate);

        if ($operate == 'deleted') {
            return $saleModel->delete();
        }

        $data['goods_id'] = $goodsId;
        $data['uniacid'] = \YunShop::app()->uniacid;
        $data['is_open'] = empty($data['is_open']) ? 0 : $data['is_open'];
        $data['font_size'] = empty($data['font_size']) ? 0 : $data['font_size'];

        $saleModel->setRawAttributes($data);
        $res = $saleModel->save();

        return $res;
    }

    public static function getModel($goodsId, $operate)
    {
        $model = false;
        if ($operate != 'created') {
            $model = static::where(['goods_id' => $goodsId])->first();
        }
        !$model && $model = new static;
        return $model;
    }

    public static function relationValidator($goodsId, $data, $operate)
    {
        $flag = false;
        $model = new static;
        $validator = $model->validator($data);
        if($validator->fails()){
            $model->error($validator->messages());
        }else{
            $flag = true;
        }
        return $flag;
    }

}