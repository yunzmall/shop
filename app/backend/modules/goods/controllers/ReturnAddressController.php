<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/4/25
 * Time: 9:31
 */

namespace app\backend\modules\goods\controllers;

use app\backend\modules\goods\services\DispatchService;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\backend\modules\goods\models\Dispatch;
use app\common\models\member\Address;
use app\backend\modules\goods\models\ReturnAddress;
use app\backend\modules\goods\models\Area;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\Street;
use Setting;

class ReturnAddressController extends UploadVerificationBaseController
{
    const PLUGINS_ID = 0;

    /**
     * 退货地址列表
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
//        $pageSize = 10;
//        $plugins_id = 0;//商城
//        $list = ReturnAddress::uniacid()->where('plugins_id', self::PLUGINS_ID)->orderBy('id', 'desc')->orderBy('id', 'desc')->paginate($pageSize)->toArray();
////        dd($list);
//        $pager = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);
//        return view('goods.return.list', [
//            'list' => $list,
//            'pager' => $pager,
//        ])->render();

        return view('goods.return.list')->render();
    }

    public function returnAddressList()
    {
        $pageSize = 10;
        $plugins_id = 0;//商城
        $list = ReturnAddress::uniacid()
            ->where('plugins_id', self::PLUGINS_ID)
            ->orderBy('id', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($pageSize)->toArray();

        return $this->successJson('ok',$list);
    }

    public function editView()
    {
        return view('goods.return.info', [
            'id' => request()->id,
        ])->render();
    }

    /**
     * 退货地址添加
     * @return mixed|string
     * @throws \Throwable
     */
    public function add()
    {
        $addressModel = new ReturnAddress();
        $requestAddress = request()->address;
        if ($requestAddress) {
            if (!$requestAddress['province_id']) {
                return $this->errorJson('请选择省份');
            }
            if (!$requestAddress['city_id']) {
                return $this->errorJson('请选择城市');
            }
            if (!$requestAddress['district_id']) {
                return $this->errorJson('请选择区域');
            }
            if (!$requestAddress['street_id']) {
                return $this->errorJson('请选择街道');
            }
            //将数据赋值到model
            $addressModel->setRawAttributes($requestAddress);
            //其他字段赋值
            $province = Address::find($requestAddress['province_id'])->areaname;
            $city = Address::find($requestAddress['city_id'])->areaname;
            $district = Address::find($requestAddress['district_id'])->areaname;
            $street = Street::find($requestAddress['street_id'])->areaname;
            $addressModel->province_name = $province;
            $addressModel->city_name = $city;
            $addressModel->district_name = $district;
            $addressModel->street_name = $street;
            $addressModel->plugins_id = self::PLUGINS_ID;//商城
            $addressModel->uniacid = \YunShop::app()->uniacid;
            //字段检测
            $validator = $addressModel->validator($addressModel->getAttributes());
            if ($validator->fails()) {//检测失败
                $this->errorJson($validator->messages());
            } else {
                //取消其他默认模板
                if($addressModel->is_default){
                    $defaultModel = ReturnAddress::getOneByPluginsId(self::PLUGINS_ID,0,0);
                    if ($defaultModel) {
                        $defaultModel->is_default = 0;
                        $defaultModel->save();
                    }
                }
                //数据保存
                if ($addressModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('退货地址创建成功');
                } else {
                    return $this->errorJson('退货地址创建失败');
                }
            }
        }
        return $this->successJson('ok',$addressModel);
//        return view('goods.return.info', [
//            'address' => $addressModel,
//        ])->render();
    }

    /**
     * 退货地址编辑
     * @return mixed|string
     * @throws \Throwable
     */
    public function edit()
    {
        $addressModel = ReturnAddress::find(request()->id);
        if (!$addressModel) {
            return $this->errorJson('无此记录或已被删除');
        }
        $requestAddress = request()->address;

        if ($requestAddress) {
            if (!$requestAddress['province_id']) {
                return $this->errorJson('请选择省份');
            }
            if (!$requestAddress['city_id']) {
                return $this->errorJson('请选择城市');
            }
            if (!$requestAddress['district_id']) {
                return $this->errorJson('请选择区域');
            }
            if (!$requestAddress['street_id']) {
                return $this->errorJson('请选择街道');
            }
            //将数据赋值到model
            $addressModel->setRawAttributes($requestAddress);
            //其他字段赋值
            $province = Address::find($requestAddress['province_id'])->areaname;
            $city = Address::find($requestAddress['city_id'])->areaname;
            $district = Address::find($requestAddress['district_id'])->areaname;
            $street = Street::find($requestAddress['street_id'])->areaname;
            $addressModel->province_name = $province;
            $addressModel->city_name = $city;
            $addressModel->district_name = $district;
            $addressModel->street_name = $street;
            $addressModel->uniacid = \YunShop::app()->uniacid;

            //字段检测
            $validator = $addressModel->validator($addressModel->getAttributes());
            if ($validator->fails()) {//检测失败
                $this->errorJson($validator->messages());
            } else {
                //取消其他默认模板
                if($addressModel->is_default){
                    $defaultModel = ReturnAddress::getOneByPluginsId(self::PLUGINS_ID,0,0);

                    if ($defaultModel && ($defaultModel->id != request()->id) ) {
                        $defaultModel->is_default = 0;
                        $defaultModel->save();
                    }
                }

                //数据保存
                if ($addressModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('退货地址修改成功');
                } else {
                    return $this->errorJson('退货地址修改失败');
                }


            }
        }

        return $this->successJson('ok',$addressModel);
//        return view('goods.return.info', [
//            'address' => $addressModel,
//        ])->render();
    }

    /**
     * 退货地址删除
     * @return array $item
     */
    public function delete()
    {
        $address = ReturnAddress::getOne(request()->id);
        if (!$address) {
            return $this->errorJson('无此配送模板或已经删除');
        }

        $model = ReturnAddress::find(request()->id);
        if ($model->delete()) {
            return $this->successJson('删除模板成功');
        } else {
            return $this->errorJson('删除模板失败');
        }
    }

    public function addressSave($addressModel) {

    }

    /**
     *
     * 快速修改
     */
    public function isDefault()
    {
        $id = request()->id;
        $status = request()->status;
        $returnAddress = ReturnAddress::uniacid()->where(['id' => $id])->first();
        if ($status){
            ReturnAddress::uniacid()->where('is_default' , 1)->update(['is_default' => 0]);
        }

        $returnAddress->is_default = $status;
        $result = $returnAddress->save();
        if ($result){
            return $this->successJson('修改成功');
        }else{
            return $this->errorJson('修改失败！！');
        }

    }

    public function ajaxAllAddress()
    {
        $list = ReturnAddress::select('id','address_name','is_default')
            ->uniacid()
            ->where('plugins_id', 0)
            ->orderBy('is_default', 'desc')
            ->get();
        return $this->successJson('ok',$list);
    }
}