<?php

namespace app\frontend\controllers;

use app\common\components\BaseController;
use app\common\models\ImportGoods;


class ImportGoodsController extends BaseController
{
    public function getGoods()
    {
        $goods_id = \YunShop::request()->goods_id;

        $goodsData = ImportGoods::getGoodsByIdAll($goods_id)->first();
        if($goodsData){
            $goodsData['complete_thumb'] = yz_tomedia($goodsData['thumb']);
            //商品其它图片反序列化
            $thumb_url = !empty($goodsData['thumb_url']) ? unserialize($goodsData['thumb_url']) : [];
            $goodsData['thumb_link'] = collect($thumb_url)->map(function ($item) {
                return yz_tomedia($item);
            })->values()->all();
            return $this->successJson('ok', $goodsData);
        }

        return $this->errorJson('商品不存在');
    }

}