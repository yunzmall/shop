<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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