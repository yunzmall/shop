<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/11/22
 * Time: 11:37
 */

namespace app\frontend\modules\goods\controllers;

use app\common\components\ApiController;
use app\common\models\Goods;
use app\common\models\goods\PostageIncludedCategory;

class PostageIncludedCategoryController extends ApiController
{
    public function index()
    {
        request()->offsetSet('is_display', 1);
        $categories = PostageIncludedCategory::records()->get();
        
        return $this->successJson('ok', $categories);

    }

    public function goods()
    {
        $sort_array = ['id', 'price', 'discount'];
        $sort = request()->input('sort');

        if (!in_array($sort, $sort_array)) {
            $sort = 'id';
        }

        $catergoy = PostageIncludedCategory::find(request()->input('category_id') ?: 0);
        if ($catergoy) {
            $goods = $catergoy->goodsSort($sort);
            foreach ($goods->items() as $goodsModel) {
                $goodsModel->thumb = yz_tomedia($goodsModel->thumb);
            }
        }else{
            $goods = Goods::where('id',-1)->paginate();
        }

        return $this->successJson('ok', $goods);
    }
}