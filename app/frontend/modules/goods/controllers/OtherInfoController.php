<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/27
 * Time: 16:24
 */

namespace app\frontend\modules\goods\controllers;


use app\common\components\ApiController;
use app\frontend\models\GoodsOption;

class OtherInfoController extends ApiController
{
    public function optionVpiPrice()
    {
        $option_id =  intval(request()->input('option_id'));

        $goodsOption = GoodsOption::where('id', $option_id)->first();

        if (!$goodsOption) {
            return $this->errorJson('规格不存在或已被删除');
        }

        $vipPrice = $goodsOption->vip_price;

        return $this->successJson('商品规格vip价格', ['vip_price'=> $vipPrice]);
    }
}