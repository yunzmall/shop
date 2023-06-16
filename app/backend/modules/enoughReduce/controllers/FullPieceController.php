<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2018/5/18
 * Time: 下午17:28
 */

namespace app\backend\modules\enoughReduce\controllers;

use app\backend\modules\goods\models\Goods;
use app\common\components\BaseController;
use app\common\helpers\Url;

class FullPieceController extends BaseController
{
    public function index()
    {
        $setting = \Setting::get('shop.fullPieceNew');
        if ($setting && $setting['fullPiece']) {
            $goodsIds = [];
            array_walk_recursive(array_column($setting['fullPiece'],'goods'), function($value) use (&$goodsIds) {
                array_push($goodsIds, $value);
            });
            $goods = collect(Goods::uniacid()
                ->select('id','title','thumb')
                ->where('status',1)
                ->whereIn('id',$goodsIds)
                ->get()->toArray());
            foreach ($setting['fullPiece'] as &$fullPiece) {
                $fullPiece['goods'] = array_values($goods->whereIn('id',$fullPiece['goods'])->all());
            }
            unset($fullPiece);
        }

        return view('goods.enoughReduce.full_piece.index', [
            'setting' => json_encode($setting),
        ])->render();
    }

    public function getGoods()
    {
        $keyword = request()->keyword;
        if (!$keyword) {
            return $this->errorJson('请输入查询条件');
        }
        $model = Goods::uniacid()
            ->select('id','title','thumb')
            ->where('status',1);

        $model->where(function ($query) use ($keyword) {
            $query->where('yz_goods.id',$keyword)
                ->orWhere('title','like','%'.$keyword.'%');
        });

        $model = $model->whereIn('plugin_id',[0])
            ->orderBy('id', 'desc')->paginate(20);//平台

        foreach ($model as $goods) {
            $goods->thumb = yz_tomedia($goods->thumb);
        }
        return $this->successJson('ok',$model->toArray());
    }

    public function store()
    {
        $setting = request()->setting;
        $data = [
            "open" => $setting['open'] ? true : false,
            "fullPiece" => $setting['fullPiece'] ? : [],
        ];

        $has_goods = [];
        foreach ($data['fullPiece'] as $k=>$fullPiece) {
            foreach ($fullPiece['rules'] as $rule) {
                if (empty($rule['enough']) || empty($rule['reduce'])) {
                    return $this->errorJson('规则中的条件以及折扣必填');
                }
                if ($fullPiece['discount_type'] == 1 && ($rule['reduce'] < 0 || $rule['reduce'] > 10)) {
                    return $this->errorJson('折扣必须大于0折小于10折');
                }
            }
            if (empty($fullPiece['goods']) ) {
                return $this->errorJson('规则必须选择商品');
            }
            $data['fullPiece'][$k]['goods'] = array_column($fullPiece['goods'],'id') ? : [];
            $intersect = array_intersect($has_goods,$data['fullPiece'][$k]['goods']);
            if (!empty($intersect)) {
                return $this->errorJson('一个商品不能设置在两个优惠规则中');
            }
            $has_goods = array_merge($has_goods,$data['fullPiece'][$k]['goods']);
        }

        \Setting::set('shop.fullPieceNew',$data);
        return $this->successJson("设置保存成功", Url::absoluteWeb('goods.enoughReduce.full_piece.index'));
    }
}