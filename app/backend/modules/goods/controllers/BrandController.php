<?php
namespace app\backend\modules\goods\controllers;

use app\backend\modules\goods\models\Brand;
use app\backend\modules\goods\services\BrandService;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/27
 * Time: 上午9:17
 */
class BrandController extends UploadVerificationBaseController
{
    /**
     * 商品品牌列表
     */
    public function index()
    {
        return view('goods.brand.list')->render();
    }


    public function getBrandData(){
        $where = [];

        if (request()->search_brand_keyword){
            $where[] = ['name','like','%'.trim(request()->search_brand_keyword).'%'];
        }

        $data = \app\common\models\Brand::getBrands()->where($where)->select('id', 'name')->get();

        if ($data->isNotEmpty()){
            $data->each(function (&$v){
                if (mb_strlen($v->name,'utf-8') > 40){
                    $v->name = mb_substr($v->name,0,40,'utf-8').'...';
                }
            });
        }

        return $this->successJson('获取成功',$data);

    }

    public function brandData()
    {
        $search = request()->search;
        $pageSize = 10;
        $list = Brand::getBrands($search)->orderBy('id','desc')->paginate($pageSize);
        foreach ($list as &$item){
            $item['logo_url'] = yz_tomedia($item['logo']);
        }
        return $this->successJson('ok',$list);

    }


    /**
     * 添加品牌
     */
    public function add()
    {
        $brandModel = new Brand();

        $requestBrand = request()->brand;

        if($requestBrand) {
            //将数据赋值到model
            $brandModel->setRawAttributes($requestBrand);
            //其他字段赋值
            $brandModel->uniacid = \YunShop::app()->uniacid;

            //字段检测
            $validator = $brandModel->validator($brandModel->getAttributes());
            if ($validator->fails()) {//检测失败
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($brandModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('品牌创建成功');
                }else{
                    $this->errorJson('品牌创建失败');
                }
            }
        }

        $this->title = '创建品牌';
        $this->breadcrumbs = [
            '品牌管理'=>['url'=>$this->createWebUrl('goods.brand.index'),'icon'=>'icon-dian'],
            $this->title,
        ];

        return $this->successJson('ok',$brandModel);
    }

    public function editViwe()
    {
        return view('goods.brand.info', [
            'id' => request()->id
        ])->render();
    }

    /**
     * 编辑商品品牌
     */
    public function edit()
    {

        $brandModel = Brand::getBrand(request()->id);
        if(!$brandModel){
            return $this->errorJson('无此记录或已被删除');
        }
        $requestBrand = request()->brand;
        if($requestBrand) {
            //将数据赋值到model
            $brandModel->setRawAttributes($requestBrand);
            //字段检测
            $validator = $brandModel->validator($brandModel->getAttributes());
            if ($validator->fails()) {//检测失败
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($brandModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('品牌保存成功');
                }else{
                    $this->errorJson('品牌保存失败');
                }
            }
        }
        $brandModel->logo_url = yz_tomedia($brandModel->logo);
        $brandModel->desc =  html_entity_decode($brandModel->desc);
        return $this->successJson('ok',$brandModel);
    }

    /**
     * 删除商品品牌
     */
    public function deletedBrand()
    {
        $brand = Brand::getBrand(request()->id);
        if(!$brand) {
            return $this->errorJson('无此品牌或已经删除');
        }

        $result = Brand::deletedBrand(request()->id);
        if($result) {
           return $this->successJson('删除品牌成功');
        }else{
            return $this->errorJson('删除品牌失败');
        }
    }

    /**
     * 商品品牌
     */
    public function searchBrand()
    {
        $keyword = request()->keyword;

        if (!$keyword)
        {
            return $this->errorJson('请输入关键字!!');
        }
        $brand = Brand::keywordGetBrand($keyword)->limit(20)->get()->toArray();
        return $this->successJson('ok',$brand);
    }

    /**
     * 批量更新品牌推荐状态
     */
    public function batchRecommend()
    {
        if (is_array(request()->ids) && isset(request()->is_recommend)){
            Brand::uniacid()->whereIn('id', request()->ids)->update(['is_recommend'=> request()->is_recommend]);
            return $this->successJson("批量更新成功");
        }
        return $this->errorJson('修改失败, 请检查参数');
    }

}