<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/11/8
 * Time: 下午4:20
 */

namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\notice\MessageTemp;
use app\common\modules\template\Template;

class DiyTempController extends BaseController
{
    private $temp_model;

    public function index()
    {
        if(request()->ajax()){
            $kwd = request()->keyword;
            $list = MessageTemp::fetchTempList($kwd)->orderBy('id', 'desc')->paginate(20);
            return $this->successJson('请求接口成功', ['list' => $list,]);
        }
        return view('setting.diytemp.list');
    }


    public function add()
    {
        if (request()->ajax() && request()->temp) {
            $temp_model = new MessageTemp();
            $ret = $temp_model::create($temp_model::handleArray(request()->temp));
            if (!$ret) {
                return $this->successJson('添加模板失败', Url::absoluteWeb('setting.diy-temp.index'));
            }
            return $this->errorJson('添加模板成功', Url::absoluteWeb('setting.diy-temp.index'));
        }
        if(request()->ajax()){
            $temp = array_values(Template::current()->getItems());
            return $this->successJson('请求接口成功',['temp' => $temp]);
        }

        return view('setting.diytemp.detail');
    }


    public function edit()
    {
        $this->verifyParam();

        if (request()->temp) {
            $this->temp_model->fill(MessageTemp::handleArray(request()->temp));
            $ret = $this->temp_model->save();
            if (!$ret) {
                return $this->successJson('修改模板失败', Url::absoluteWeb('setting.diy-temp.index'), 'error');
            }
            return $this->errorJson('修改模板成功', Url::absoluteWeb('setting.diy-temp.index'));
        }
        $temp = array_values(Template::current()->getItems());
        //  return $this->successJson('请求接口成功',['temp' => $temp]);
        if(request()->ajax()){
            return $this->successJson('请求接口', [
                'temp' => $this->temp_model->toArray(),
                'wechat_temp' =>$temp
            ]);

        }
        return view('setting.diytemp.tempEdit');
    }


    public function del()
    {
        $this->verifyParam();
        $this->temp_model->delete();
        return $this->successJson('删除成功', Url::absoluteWeb('setting.diy-temp.index'));
    }

    public function tpl()
    {
        return view('setting.diytemp.tpl.common', [
            'kw' => request()->kw,
            'tpkw' => request()->tpkw,
        ])->render();
    }

    private function verifyParam()
    {
        $temp_id = intval(request()->id);
        if (!$temp_id) {
            return $this->errorJson('参数错误', Url::absoluteWeb('setting.diy-temp.index'), 'error');
        }
        $temp_model = MessageTemp::getTempById($temp_id)->first();
        if (!$temp_model) {
            return $this->errorJson('未找到数据', Url::absoluteWeb('setting.diy-temp.index'), 'error');
        }
        $this->temp_model = $temp_model;
    }

    public function query()
    {
        $kwd = trim(request()->keyword);
        if ($kwd) {
            $temp_list = MessageTemp::fetchTempList($kwd)->get();
            return view('setting.diytemp.query', [
                'temp_list' => $temp_list
            ])->render();
        }
    }
}