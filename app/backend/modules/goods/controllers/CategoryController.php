<?php

namespace app\backend\modules\goods\controllers;

use app\backend\modules\goods\models\Category;
use app\backend\modules\goods\models\Categorys;
use app\backend\modules\goods\models\Goods;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\models\GoodsCategory;
use app\common\models\Member;
use app\common\models\Order;
use app\common\helpers\Url;
use phpDocumentor\Reflection\DocBlock\Description;
use Setting;
use app\backend\modules\filtering\models\Filtering;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/22
 * Time: 下午1:51
 */

class CategoryController extends UploadVerificationBaseController
{
    /**
     * 商品分类列表172
     */
    public function index()
    {
        if(request()->ajax()){
            $keyword = request()->keyword;
            if($keyword){
                $category_data = Categorys::searchCategory($keyword);
                return $this->successJson('', $category_data);
            }
            $category_data = Categorys::getCategoryData();
            $thirdShow = \Setting::get('shop.category.cat_level') == 3 ? true : false;  //根据后台设置显示两级还是三级分类
            return $this->successJson('', array('data' => $category_data ,'thirdShow' => $thirdShow));
        }
        return view('goods.category.list');
    }

    /**
     * 获取所有商城商品分类（批量修改用）
     */
    public function getAllShopCategory(){

        $setlevel = \Setting::get('shop.category.cat_level') ? : 2;  //根据后台设置显示两级还是三级分类

        $category_data = Categorys::uniacid()
            ->select('id', 'name', 'parent_id')->with(['hasManyChildren' => function ($query) use ($setlevel){
                $query->select('id', 'name', 'parent_id')
                    ->with(['hasManyChildren' => function ($query) use ($setlevel) {
                        $query->select('id', 'name', 'parent_id')->where('level', $setlevel);
                    }]);
            }])->where('level', 1)->where('plugin_id',0)
            ->orderBy('display_order' ,'asc')->orderBy('id' ,'asc')->get();

        return $this->successJson('获取成功',['list'=>$category_data,'setlevel'=>$setlevel]);

    }

    /**
     * 批量修改商城商品分类
     */
    public function changeManyGoodsCategory(){

        $request = request();

        if (!$request->goods_id_arr || !is_array($request->goods_id_arr)){
            return $this->errorJson('请选择要修改分类的商品');
        }

        $setlevel = \Setting::get('shop.category.cat_level') ? : 2;
        $category_id = 0;
        $category_ids = '';
        for ($i=1;$i<=$setlevel;$i++){
            $key = 'level_'.$i;
            if (empty($request->$key)){
                return $this->errorJson('请选择'.$i.'级分类');
            }
            $category_id = $request->$key;
            $category_ids .= empty($category_ids) ? $request->$key : ','.$request->$key;
        }

        //校验分类关系链是否正确
        if (!$check_res = \app\common\models\Category::checkCategory(explode(',',$category_ids))){
            return $this->errorJson('分类异常');
        }

        if(!$goods_ids = \app\common\models\Goods::uniacid()->where('plugin_id',0)->whereIn('id',$request->goods_id_arr)->pluck('id')){
            return $this->errorJson('选择商品不存在或非商城商品');
        }
        $category_goods_ids = GoodsCategory::whereIn('goods_id',$goods_ids)->pluck('goods_id');

        $insert_ids = $goods_ids->diff($category_goods_ids);
        $update_ids = $goods_ids->intersect($category_goods_ids);

//        dd($goods_ids->toArray(),$update_ids->toArray(),$insert_ids,$update_ids);

        if ($insert_ids){
            $time = time();
            $insert_data = $insert_ids->map(function ($v) use ($category_id,$category_ids,$time){
                return [
                    'goods_id'=>$v,
                    'category_id'=>$category_id,
                    'category_ids'=>$category_ids,
                    'created_at'=>$time,
                    'updated_at'=>$time
                ];
            })->all();
            GoodsCategory::insert($insert_data);
        }

        if ($update_ids){
            GoodsCategory::whereIn('goods_id',$update_ids)->update([
                'category_id'=>$category_id,
                'category_ids'=>$category_ids
            ]);
        }

        return $this->successJson('修改成功');
    }

    public function categoryInfo()
    {
        return view('goods.category.info', [
            'id'=>request()->id,
            'level' => request()->level,
            'parent_id'=> request()->parent_id,
        ])->render();
    }

    /**
     * 添加商品分类
     */
    public function addCategory()
    {

        // sleep(5);
        //判断分类等级
        $level = request()->level ? request()->level : '1';
        //判断是否有父类id没有默认0
        $parent_id = request()->parent_id ? request()->parent_id : '0';

        $categoryModel = new Category();
        //分类等级
        $categoryModel->level = $level;
        //父类id
        $categoryModel->parent_id = $parent_id;
        $parent = [];
        //url地址
        $url = Url::absoluteWeb('goods.category.index');
        if ($parent_id > 0) {
            //查出父分类
            $parent = Category::getCategory($parent_id);
            //地址栏显示父分类
            $url = Url::absoluteWeb('goods.category.index', ['parent_id' => $parent_id]);
        }
        //获取分类发送过来的值
        $requestCategory = request()->category;
        if ($requestCategory) {
            if (isset($requestCategory['filter_ids']) && is_array($requestCategory['filter_ids'])) {
                $requestCategory['filter_ids'] = implode(',', $requestCategory['filter_ids']);
            }
            //将数据赋值到model
            $categoryModel->fill($requestCategory);
            //其他字段赋值
            $categoryModel->uniacid = \YunShop::app()->uniacid;
            //字段检测
            $validator = $categoryModel->validator();
            if ($validator->fails()) {
                //检测失败
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($categoryModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('分类创建成功');
                } else {
                    $this->errorJson('分类创建失败');
                }
            }
        }
        return $this->successJson('ok',[
            'item' => $categoryModel,
            'parent' => $parent,
            'level' => $level,
            'label_group' => [],
        ]);
//        return view('goods.category.info', [
//            'item' => $categoryModel,
//            'parent' => $parent,
//            'level' => $level,
//            'label_group' => [],
//        ])->render();
    }

    /**
     * 修改分类
     */
    public function editCategory()
    {   //查询这个分类是否存在
        $categoryModel = Category::getCategory(request()->id);
        //判断是否有父类id没有默认0
        $parent_id = request()->parent_id ? request()->parent_id : '0';

        if (!$categoryModel) {
            return $this->errorJson('无此记录或已被删除');
        }
        //URL地址
        $url = Url::absoluteWeb('goods.category.index', ['parent_id' => $categoryModel->parent_id]);
        if (!empty($categoryModel->parent_id)) {
            //查出父分类
            $parent = Category::getCategory($categoryModel->parent_id);
        }
        if (isset($categoryModel->filter_ids)) {
            $filter_ids = explode(',', $categoryModel->filter_ids);
            $label_group = Filtering::categoryLabel($filter_ids)->get();
        }
        //获取分类发送过来的值
        $requestCategory = request()->category;
        if ($requestCategory) {
            if (isset($requestCategory['filter_ids']) && is_array($requestCategory['filter_ids'])) {
                $requestCategory['filter_ids'] = implode(',', $requestCategory['filter_ids']);
            }
            //将数据赋值到model
            $categoryModel->fill($requestCategory);
            //字段检测
            $validator = $categoryModel->validator();
            if ($validator->fails()) {
                //检测失败
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($categoryModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('分类保存成功', $url);
                } else {
                    $this->errorJson('分类保存失败');
                }
            }
        }
        $categoryModel['thumb_url'] = yz_tomedia($categoryModel['thumb']);
        $categoryModel['adv_img_url'] = yz_tomedia($categoryModel['adv_img']);
        $parent['thumb_url'] = yz_tomedia($parent['thumb']);
        $cat_level = \Setting::get('shop.category')['cat_level'] ?: 0;
        $link = yzAppFullUrl('/catelist/'.request()->id);
        return $this->successJson('ok',[
            'item' => $categoryModel,
            'level' => $categoryModel->level,
            'label_group' => $label_group,
            'parent' => $parent,
            'cat_level'     => $cat_level,
            'link'          => $link
        ]);
//        return view('goods.category.info', [
//            'item' => $categoryModel,
//            'level' => $categoryModel->level,
//            'label_group' => $label_group,
//            'parent' => $parent,
//        ])->render();
    }

    /**
     * 删除商品分类
     */
    public function deletedCategory()
    {
        $category = Category::find(request()->id);

        if (!$category) {
            return $this->errorJson('无此分类或已经删除');
        }
        //查询是否有商品分类关联表 find_in_set
        $good_ids = GoodsCategory::whereRaw('FIND_IN_SET(?,category_ids)', $category->id)
            ->pluck('goods_id')
            ->toArray();

        //查询是否有商品
        $goods = Goods::whereIn('id', $good_ids)->first();
        if (!empty($goods)) {
            return $this->errorJson('分类下存在商品,不允许删除');
        }

        $result = Category::deletedAllCategory($category->id);
        if ($result) {
            return $this->successJson('删除分类成功');
        } else {
            return $this->errorJson('删除分类失败');
        }
    }


    /**
     * 获取搜索分类
     * @return html
     */
    public function getSearchCategorys()
    {
        $keyword = \YunShop::request()->keyword;
        $categorys = Category::getCategorysByName($keyword);
        return view('goods.category.query', [
            'categorys' => $categorys
        ])->render();
    }

    /**
     * 获取搜索分类，返回数组
     * @return string
     * @throws \Throwable
     */
    public function getSearchCategorysJson()
    {
        $keyword = request()->keyword;
        $categorys = Category::getCategorysByName($keyword);
        foreach ($categorys as &$item){
            $item['thumb'] = yz_tomedia($item['thumb']);
        }
        return $this->successJson('ok',$categorys);
    }

    public function test()
    {
        //$order = Order::uniacid()->where("id",1265)->first();
        //$refund = RefundApply::uniacid()->where("order_id",1265)->first();
        //$createNotice = new OrderReceivedNotice($order);
        //$createNotice->sendMessage();
    }

    public function getCategoryData()
    {
        $data = Category::getCategoryData();
        return $this->successJson('', $data);
    }

    public function getCategorySmallData()
    {
        $data = Category::getCategorySmallData();
        return $this->successJson('', $data);
    }

    /**
     * 获取搜索分类，返回数组
     * @return string
     * @throws \Throwable
     */
    public function getCategorysJson()
    {
        $level = request()->level;
        $parent_id = request()->parent_id;
        $categorys = Category::parentIdGetCategorys($level,$parent_id,0)->get()->toArray();
        return $this->successJson('ok',$categorys);
    }

    /**
     * 批量更新商品分类显示
     */

    public function batchDisplay()
    {
        return $this->batchOption(request()->ids, 'enabled', request()->enabled);
    }

    /**
     * 批量更新商品分类推荐
     */
    public function batchRecommend()
    {
        return $this->batchOption(request()->ids, 'is_home', request()->is_home);
    }

    protected function batchOption($ids, $field, $value)
    {
        if (is_array($ids) && isset($value)){
            Category::uniacid()->whereIn('id', $ids)->update([$field=> $value]);
            return $this->successJson("批量更新成功");
        }
        return $this->errorJson('修改失败, 请检查参数');
    }
}
