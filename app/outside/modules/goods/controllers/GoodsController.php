<?php
/**
 * Created by PhpStorm.
 * Date: 2023/4/14
 * Time: 10:40
 */

namespace app\outside\modules\goods\controllers;


use app\common\models\Goods;
use app\outside\controllers\OutsideController;

class GoodsController extends OutsideController
{
    public function index()
    {
        $model = Goods::uniacid()->select('id', 'title', 'thumb', 'sku', 'goods_sn', 'price', 'stock', 'created_at');

        $created_at = request()->input('created_at');
        //创建时间
        if ($created_at && is_array($created_at)) {
            $model->whereBetween('created_at', $created_at);
        }

        //商品名称
        if ($title = trim(request()->input('title'))) {
            $model->where('title', 'like', '%' . $title . '%');
        }

        //商品ID
        if ($goods_id = intval(request()->input('goods_id'))) {
            $model->where('id', $goods_id);
        }

        $list = $model->orderBy('created_at', 'desc')->paginate(15);

        $list->map(function ($goods) {
            $goods->setHidden(['status_name']);
            $goods->thumb = yz_tomedia($goods->thumb);
        });

        return $this->successJson('list', $list);
    }
}