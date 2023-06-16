<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/22
 * Time: 19:41
 */

namespace app\backend\modules\goods\services;


use app\common\models\GoodsCategory;

class GoodsService
{
    //商品分类保存
    public static function store($goods_id, $categorys, $cat_level,$category_to_option = [])
    {
        (new GoodsCategory())->delCategory($goods_id);
        $category_to_option_open = \Setting::get('shop.category.category_to_option') ? : 0;
        if (!empty($categorys)) {
            foreach ($categorys as $key => $val) {

                $small = $val[(count($val) - 1)];
                $category_id = $small['id'];
                $category_ids = implode(',', array_column($val, 'id'));
                $goodsCategory = [
                    'goods_id' =>$goods_id,
                    'category_id' => $category_id,
                    'category_ids' => $category_ids,
                    'goods_option_id' => $category_to_option_open ? ($category_to_option[$key]['goods_option_id'] ? : 0) : 0,
                ];

                $goodsCategoryModel = new GoodsCategory();
                if (!$goodsCategoryModel->fill($goodsCategory)->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function saveGoodsCategory($goodsModel, $categorys, $shopset)
    {
        $category_id = $shopset['cat_level'] == 3 ? $categorys['thirdid'] : $categorys['childid'];
        $goodsCategory = [
            'goods_id' => $goodsModel->id,
            'category_id' => $category_id,
            'category_ids' => implode(',', $categorys),
        ];
        $goodsCategoryModel = new GoodsCategory($goodsCategory);
        return $goodsModel->hasManyGoodsCategory()->save($goodsCategoryModel);
    }

    public static function saveGoodsMultiCategory($goodsModel, $categorys, $shopset)
    {
        $categoryModel = new GoodsCategory();

        $categoryModel->delCategory($goodsModel->id);

        if (!empty($categorys)) {
            foreach ($categorys['parentid'] as $key => $val) {
                switch ($shopset['cat_level']) {
                    case 2:

                        if (0 == $val || 0 == $categorys['childid'][$key]) {
                            continue;
                        }

                        $category_id = $categorys['childid'][$key];
                        $category_ids = $val . ',' . $categorys['childid'][$key];
                        break;
                    case 3:
                        if (0 == $val || 0 == $categorys['childid'][$key] || 0 == $categorys['thirdid'][$key]) {
                            //continue;
                        }

                        $category_id = $categorys['thirdid'][$key];
                        $category_ids = $val . ',' . $categorys['childid'][$key] . ',' . $categorys['thirdid'][$key];
                        break;
                    default:
                        $category_id = $categorys['childid'][$key];
                        $category_ids = $val . ',' . $categorys['childid'][$key];
                }

                $goodsCategory = [
                    'goods_id' => $goodsModel->id,
                    'category_id' => $category_id,
                    'category_ids' => $category_ids,
                ];
                $goodsCategoryModel = new GoodsCategory($goodsCategory);

                if (!$goodsModel->hasManyGoodsCategory()->save($goodsCategoryModel)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function saveGoodsMultiNewCategory($goodsModel, $categorys, $shopset)
    {
        $categoryModel = new GoodsCategory();

        $categoryModel->delCategory($goodsModel->id);

        if (!empty($categorys)) {
                switch ($shopset['cat_level']) {
                    case 2:
                        if (0 == $categorys['childid']) {
                            continue;
                        }

                        $category_id = $categorys['childid'];
                        $category_ids =  $categorys['parentid'] . ',' .$categorys['childid'];
                        break;
                    case 3:
                        if (0 == $categorys['childid'] || 0 == $categorys['thirdid']) {
                            //continue;
                        }

                        $category_id = $categorys['thirdid'];
                        $category_ids = $categorys['parentid'] . ',' .$categorys['childid'] . ',' . $categorys['thirdid'];
                        break;
                    default:
                        $category_id = $categorys['childid'];
                        $category_ids = $categorys['childid'];
                }

                $goodsCategory = [
                    'goods_id' => $goodsModel->id,
                    'category_id' => $category_id,
                    'category_ids' => $category_ids,
                ];
                $goodsCategoryModel = new GoodsCategory($goodsCategory);

                if (!$goodsModel->hasManyGoodsCategory()->save($goodsCategoryModel)) {
                    return false;
                }

        }
        return true;
    }


}