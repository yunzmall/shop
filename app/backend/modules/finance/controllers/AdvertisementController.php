<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 13:46
 */

namespace app\backend\modules\finance\controllers;

use app\backend\modules\finance\models\Advertisement;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;

class AdvertisementController extends UploadVerificationBaseController
{
    public function index()
    {
        if (request()->ajax()) {
            $search = request()->input('search');

            $list = Advertisement::getList($search)->paginate(15);

            return $this->successJson('ok', [
                'list' => $list,
            ]);
        }


        return view('finance.advertisement.adv_list')->render();
    }

    public function add()
    {
        $adv = request()->input('adv');

        if (request()->isMethod('post')) {

            $adv_model = new Advertisement();

            if($adv['area_open'] == 1){
                if(!$adv['lng'] || !$adv['lat']) throw new ShopException('若需开启投放区域，请选择详细地址');
            }
            $adv['longitude'] = trim($adv['lng']);
            $adv['latitude'] = trim($adv['lat']);
            unset($adv['lng']);
            unset($adv['lat']);

            $adv_model->setRawAttributes($adv);

            $validator = $adv_model->validator($adv_model->getAttributes());

            if ($validator->fails()) {
                $this->errorJson($validator->messages()->first());
            } else {
                //其他字段赋值
                $adv_model->uniacid = \YunShop::app()->uniacid;
                if ($adv_model->save()) {
                    //显示信息并跳转
                    return $this->successJson('添加成功', ['url' => yzWebUrl('finance.advertisement.index')]);
                } else {
                    $this->errorJson('添加失败');
                }
            }
        }

        return view('finance.advertisement.adv_form', [
            'adv' => $adv,
        ])->render();
    }

    public function edit()
    {
        if(request()->ajax()) {
            $id = intval(\Yunshop::request()->id);
            if (!$id) {
                return view('finance.advertisement.adv_form')->render();
            }
            $adv_model = Advertisement::find($id);
            if (!$adv_model) {
                return $this->errorJson('无记录或已被删除');
            }

            $requestData = \Yunshop::request()->adv;
            if ($requestData) {
                if($requestData['area_open'] == 1){
                    if(!$requestData['lng'] || !$requestData['lat']) throw new ShopException('若需开启投放区域，请选择详细地址');
                }
                $requestData['longitude'] = trim($requestData['lng']);
                $requestData['latitude'] = trim($requestData['lat']);
                unset($requestData['lng']);
                unset($requestData['lat']);
                //数据保存
                $adv_model->setRawAttributes($requestData);
                $validator = $adv_model->validator($adv_model->getAttributes());

                if ($validator->fails()) {
                    $this->errorJson($validator->messages());
                } else {
                    if ($adv_model->save()) {
                        //显示信息并跳转
                        return $this->successJson('修改成功');
                    } else {
                        $this->successJson('修改失败');
                    }
                }
            }
            $adv_model->thumb = yz_tomedia($adv_model->thumb);
            return $this->successJson('ok', [
                'adv' => $adv_model,
                'id' => $id,
            ]);
        }

        return view('finance.advertisement.adv_form')->render();
    }

    public function del()
    {
        $id = intval(\Yunshop::request()->id);
        $adv_model = Advertisement::find($id);
        if (!$adv_model) {
            return $this->errorJson('无记录或已被删除', '', 'error');
        }


        if ($adv_model->delete()) {
            return $this->successJson('删除成功');
        }
        return $this->errorJson('删除失败', '', 'error');
    }

    public function setStatus()
    {
        $id = \YunShop::request()->id;
        $adv_model = Advertisement::find($id);

        $adv_model->status = \YunShop::request()->status ?: 0;

        $adv_model->save();
        return $this->successJson('修改成功');
    }
}