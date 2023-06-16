<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/12/14
 * Time: 10:12
 */

namespace app\backend\modules\goods\models;


class GoodsSpecInfo extends \app\common\models\goods\GoodsSpecInfo
{
    public static function relationSave($goodsId, $data, $operate = '')
    {
        $data = $data['spec_info'];
        if (!$goodsId) {
            return false;
        }
        static::where('goods_id',$goodsId)->delete();
        if (!$data || $operate == 'deleted') {
            return false;
        }
        $goods = \app\common\models\Goods::select('has_option')->where('id',$goodsId)->first();
        if (!$goods) {
            return true;
        }
        $insert = [];
        foreach ($data as $item) {
            $insert[] = [
                'uniacid' => \YunShop::app()->uniacid,
                'goods_id' => $goodsId,
                'goods_option_id' => $goods->has_option?($item['goods_option_id'] ? : 0):0,
                'info_img' => $item['info_img'] ? : '',
                'sort' => $item['sort'] ? : 0,
                'content' => json_encode($item['content']),
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        if ($insert) {
            static::insert($insert);
        }
        return true;
    }
}