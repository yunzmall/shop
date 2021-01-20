<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/3
 * Time: 下午4:30
 */

namespace app\backend\modules\goods\controllers;


use app\backend\modules\goods\services\DispatchService;
use app\common\components\BaseController;
use app\backend\modules\goods\models\Dispatch;
use app\backend\modules\goods\models\Area;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use Setting;
use app\common\models\goods\GoodsDispatch;

class DispatchController extends BaseController
{
    /**
     * 配送模板列表
     * @return array $item
     */
    public function index()
    {
//        $shopset = Setting::get('shop');
//        $pageSize = 10;
//        $list = Dispatch::uniacid()->where(['is_plugin' => 0 , 'plugin_id' => 0])->orderBy('display_order', 'desc')->orderBy('id', 'desc')->paginate($pageSize)->toArray();
//        $pager = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);
//        return view('goods.dispatch.list', [
//            'list' => $list,
//            'pager' => $pager,
//            'shopset' => $shopset
//        ])->render();
        return view('goods.dispatch.list')->render();
    }

    public function dispatchData()
    {
//        $shopset = Setting::get('shop');
        $pageSize = 10;
        $list = Dispatch::uniacid()->where(['is_plugin' => 0 , 'plugin_id' => 0])
            ->orderBy('display_order', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($pageSize)->toArray();
        foreach($list['data'] as &$item){
            $item['piece_data'] = unserialize($item['piece_data']);
            $item['weight_data'] = unserialize($item['weight_data']);
        }
        return $this->successJson('ok',$list);
    }

    public function editSave()
    {
        return view('goods.dispatch.info', [
            'id'        => request()->id,
        ])->render();
    }

    /**
     * 配送模板添加
     * @return array $item
     */
    public function add()
    {
        $dispatchModel = new Dispatch();
        $areas = Area::getProvinces(0);

        $requestDispatch = request()->dispatch;
        $random = 1;

        if ($requestDispatch) {
//            dd($requestDispatch);
            $requestDispatch = DispatchService::getDispatch($requestDispatch);
            //将数据赋值到model
            $dispatchModel->setRawAttributes($requestDispatch);
            //其他字段赋值
            $dispatchModel->uniacid = \YunShop::app()->uniacid;
            //字段检测
            $validator = $dispatchModel->validator($dispatchModel->getAttributes());
            if ($validator->fails()) {//检测失败
                $this->errorJson($validator->messages());
            } else {
                //取消其他默认模板
                if($dispatchModel->is_default){
                    $defaultModel = Dispatch::getOneByDefault();
                    if ($defaultModel) {
                        $defaultModel->is_default = 0;
                        $defaultModel->save();
                    }
                }

                //dd($dispatchModel);
                //数据保存
                if ($dispatchModel->save()) {
                    //显示信息并跳转
//                    return $this->message('配送模板创建成功', Url::absoluteWeb('goods.dispatch.edit',['id' => $dispatchModel->id]));
                    return $this->successJson('配送模板创建成功');
                } else {
                    $this->errorJson('配送模板创建失败');
                }
            }
        }
        $data = [
            'dispatch' => $dispatchModel,
            'parents' => $areas->toArray(),
            'random' => $random,
        ];
        return $this->successJson('ok',$data);
//        return view('goods.dispatch.info', [
//            'dispatch' => $dispatchModel,
//            'parents' => $areas->toArray(),
//            'random' => $random,
//        ])->render();
    }

    /**
     * 配送模板编辑
     * @return array $item
     */
    public function edit()
    {
        $dispatchModel = Dispatch::find(request()->id);
        if (!$dispatchModel) {
            return $this->errorJson('无此记录或已被删除');
        }
        $dispatchModel->weight_data = unserialize($dispatchModel->weight_data);
        $dispatchModel->piece_data = unserialize($dispatchModel->piece_data);
        if(!$dispatchModel->calculate_type){
            $random = $dispatchModel->weight_data ? count($dispatchModel->weight_data) + 1 : 1;
        }else{
            $random = $dispatchModel->piece_data ? count($dispatchModel->piece_data) + 1 : 1;
        }

        $areas = Area::getProvinces(0);

        $requestDispatch = request()->dispatch;
        if ($requestDispatch) {

            $requestDispatch = DispatchService::getDispatch($requestDispatch);
            //将数据赋值到model
            $dispatchModel->setRawAttributes($requestDispatch);
            //其他字段赋值
            $dispatchModel->uniacid = \YunShop::app()->uniacid;

            //字段检测
            $validator = $dispatchModel->validator($dispatchModel->getAttributes());
            if ($validator->fails()) {//检测失败
                $this->errorJson($validator->messages());
            } else {
                //取消其他默认模板
                if($dispatchModel->is_default){
                    $defaultModel = Dispatch::getOneByDefault();

                    if ($defaultModel && ($defaultModel->id != request()->id) ) {
                        $defaultModel->is_default = 0;
                        $defaultModel->save();
                    }
                }

                //数据保存
                if ($dispatchModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('配送模板更新成功');
                } else {
                    $this->errorJson('配送模板更新失败');
                }
            }
        }

        $data = [
            'dispatch' => $dispatchModel,
            'parents' => $areas->toArray(),
            'random' => $random,
        ];
        return $this->successJson('ok',$data);
        //dd($dispatchModel);
//        return view('goods.dispatch.info', [
//            'dispatch' => $dispatchModel,
//            'parents' => $areas->toArray(),
//            'random' => $random,
//        ])->render();
    }

    /**
     * 配送模板删除
     * @return array $item
     */
    public function delete()
    {
        $dispatch = Dispatch::getOne(request()->id);
        if (!$dispatch) {
            return $this->errorJson('无此配送模板或已经删除');
        }
        $model = Dispatch::find(request()->id);
        if($model->is_default == 1){
            return $this->errorJson('默认模板不支持删除,如果需要删除请先设置其他的模板为默认模板');
        }
        $models= Dispatch::uniacid()->where('is_default',1)->first();

        if(!$models){
            return $this->errorJson('删除失败,请先添加默认的模板,当删除这个模板后,所有这个配送模板商品会自动选择到默认的配送模板');
        }
        $res = GoodsDispatch::where('dispatch_id',$model->id)->update(['dispatch_id' => $models->id]);

        if ($model->delete()) {
            return $this->successJson('删除模板成功');
        } else {
            return $this->errorJson('删除模板失败');
        }
    }

    /**
     * 配送模板排序
     * @return array $item
     */
    public function sort()
    {
        $displayOrders = request()->display_order;
        foreach($displayOrders as $id => $displayOrder){
            $dispatch = Dispatch::find($id);
            $dispatch->display_order = $displayOrder;
            $dispatch->save();
        }
        return $this->successJson('排序成功');
    }

    /**
     * 配送模板排序
     * @return array $item
     */
    public function sortV2()
    {
        $id = request()->id;
        $sort = request()->sort;

        $dispatch = Dispatch::find($id);
        $dispatch->display_order = $sort;
        $dispatch->save();

        return $this->successJson('排序成功');
    }

    public function selectCity()
    {
        $citys = Area::getAreasByCity(request()->parent_id);

        return $this->successJson('ok',$citys->toArray());
//        return view('area.selectcitys', [
//            'citys' => $citys->toArray()
//        ])->render();
    }

    public function quickEdit()
    {
        $id = request()->id;
        $type = request()->type;
        $status = request()->status;

        $result = Dispatch::quickUpdatedDispatch($id,$type,$status);

        if ($result){
            return $this->successJson('修改成功');
        }else{
            return $this->errorJson('修改失败');
        }
    }
}