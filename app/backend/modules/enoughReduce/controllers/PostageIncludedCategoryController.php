<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/11/19
 * Time: 20:35
 */

namespace app\backend\modules\enoughReduce\controllers;

use app\common\components\BaseController;
use app\common\models\Goods;
use app\common\models\goods\PostageIncludedCategory;

class PostageIncludedCategoryController extends BaseController
{
    public function index()
    {
        return view('goods.enoughReduce.postage_included_category.index')->render();
    }

    public function generate()
    {
        return view('goods.enoughReduce.postage_included_category.generate')->render();
    }

    public function records()
    {
        $categories = PostageIncludedCategory::records()->paginate();
        return $this->successJson('ok', $categories);
    }

    public function record()
    {
        $category = PostageIncludedCategory::find(request()->input('id'))->append('goods');
        return $this->successJson('ok', $category);
    }

    public function update()
    {
        $category = PostageIncludedCategory::find(request()->input('id'));

        if (request()->input('name') !== null) {
            $category->name = request()->input('name');
        }

        if (request()->input('sort') !== null) {
            $category->sort = request()->input('sort');
        }

        if (request()->input('is_display') !== null) {
            $category->is_display = request()->input('is_display');
        }

        try {
            $category->save();
            if (request()->input('add_goods_ids')) {
                $fill = [];
                foreach (request()->input('add_goods_ids') as $goods_ids) {
                    $fill[$goods_ids] = [
                        'uniacid' => \Yunshop::app()->uniacid,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                $category->hasManyGoods()->attach($fill);
            }
        } catch (\Exception $e) {
            return $this->errorJson('操作失败');
        }
        return $this->successJson('操作成功');
    }

    public function generation()
    {
        try {
            $category = PostageIncludedCategory::create([
                'uniacid' => \Yunshop::app()->uniacid,
                'sort' => request()->input('sort'),
                'name' => request()->input('name'),
                'is_display' => request()->input('is_display')
            ]);

            if ($category && request()->input('add_goods_ids')) {
                $fill = [];
                foreach (request()->input('add_goods_ids') as $goods_ids) {
                    $fill[$goods_ids] = [
                        'uniacid' => \Yunshop::app()->uniacid,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                $category->hasManyGoods()->attach($fill);
            }

        } catch (\Exception $e) {
            return $this->errorJson('创建失败');
        }

        return $this->successJson('创建包邮分类成功');
    }

    public function destroy()
    {
        $category = PostageIncludedCategory::find(request()->input('id'));
        $category->hasManyGoods()->sync([]);
        $category->delete();
        return $this->successJson('操作成功');
    }

    public function goodsDestroy()
    {
        $category = PostageIncludedCategory::find(request()->input('id'));
        $category->hasManyGoods()->detach(request()->input('goods_id'));
        return $this->successJson('操作成功');
    }

    public function searchGoods()
    {
        $keyword = request()->input('keyword');
        $goods = Goods::select('id', 'title', 'thumb')
            ->where('title', 'like', '%' . $keyword . '%')
            ->where('status', 1)
            ->whereInPluginIds([0, 92, 44]) // 先做自营, 后期可能需要添加别的插件商品
            ->paginate();

        if (!$goods->isEmpty()) {
            foreach ($goods->items() as $data) {
                $data->thumb = yz_tomedia($data->thumb);
            }
        }

        return $this->successJson('ok', $goods);
    }
}