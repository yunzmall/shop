<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/14
 * Time: 下午8:52
 */

namespace app\backend\modules\setting\controllers;


use app\backend\modules\setting\models\Slide;
use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use Illuminate\Support\Facades\DB;

class SlideController extends UploadVerificationBaseController
{
    public function index()
    {
        if(request()->ajax()){
            $slide = Slide::getSlides()->get();
            return $this->successJson('请求接口成功' ,$slide);
        }
        return view('setting.slide.slide-list');
    }



    public function create()
    {
        if(request()->ajax()){
            $requestSlide = request()->slide;
            unset($requestSlide['thumb_url']);
            $slideModel = new Slide();
            $slideModel->setRawAttributes($requestSlide);
            //其他字段赋值
            $slideModel->uniacid = \YunShop::app()->uniacid;

            //字段检测
            $validator = $slideModel->validator($slideModel->getAttributes());
            if ($validator->fails()) {//检测失败
                return $this->errorJson('数据不能为空');
            } else {
                //数据保存
                if ($slideModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('创建成功', Url::absoluteWeb('setting.slide'));
                }else{
                    return $this->errorJson('创建失败');
                }
            }
        }
        return view('setting.slide.slide-create');

    }
    public function edit()
    {
        $id = \YunShop::request()->id;
        $slideModel = Slide::getSlideByid($id);
        if(!$slideModel){
            return $this->errorJson('无此记录或已被删除');
        }

        $requestSlide = request()->slide;
        $slideModel['thumb_url'] = yz_tomedia($slideModel['thumb']);
        if($requestSlide) {
            unset($requestSlide['thumb_url']);
            //将数据赋值到model
            $slideModel->setRawAttributes($requestSlide);
            //字段检测
            $validator = $slideModel->validator($slideModel->getAttributes());
            if ($validator->fails()) {//检测失败
                return $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($slideModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('保存成功');
                }else{
                    return $this->errorJson('保存失败');
                }
            }
        }
        return view('setting.slide.slide-info',compact('slideModel'));
    }


    public function deleted()
    {
        if(request()->ajax()){
            $id = request()->id;
            $slide = Slide::getSlideByid($id);
            if(!$slide) {
                return $this->errorJson('无此记录或已经删除','','error');
            }

            $result = Slide::deletedSlide($id);
            if($result) {
                return $this->successJson('删除成功');
            }else{
                return $this->errorJson('删除失败');
            }
        }
    }


}