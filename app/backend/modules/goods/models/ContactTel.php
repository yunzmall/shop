<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2023/5/9
 * Time: 14:54
 */

namespace app\backend\modules\goods\models;

class ContactTel extends \app\common\models\goods\ContactTel
{
    public static function relationSave($goodsId, $data, $operate = '')
    {
        if (!$goodsId) {
            return false;
        }
        if (!$data) {
            return false;
        }

        $model = self::where('goods_id', $goodsId)->first();

        if (!$model) {
            $model = new self();
            $model->uniacid = \YunShop::app()->uniacid;
            $model->goods_id = $goodsId;

        }

        $model->contact_tel = $data['contact_tel'];
        $model->save();

        if ($operate == 'deleted') {
            return $model->delete();
        }

        return true;
    }
}