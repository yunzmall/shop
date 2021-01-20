<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/9/19
 * Time: 下午4:10
 */

namespace app\backend\modules\setting\controllers;


use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\helpers\Url;

class FormController extends UploadVerificationBaseController
{
    public function index()
    {
        if(request()->ajax()){
            $pinyin = app('pinyin');
            $data = [];
            $set = \Setting::get('shop.form');
            $set = json_decode($set, true);

            $form = array_values(array_sort($set['form'], function ($value) {
                return $value['sort'];
            }));

            $set['form'] = $form;
            $form = request()->form?:[];
            //dd($form);
            $base = request()->base;
            if ($form && $base) {
                if (!empty($form) && !empty($form['name'])) {
                    foreach ($form['name'] as $key => $name) {
                        if (empty($name)) {
                            return $this->successJson('自定义表单数据错误');
                        }

                        $sort = $form['sort'][$key]?:99;
                        $pinyin = implode('', pinyin($name));
                        $data[] =['name'=>$name, 'sort'=>$sort, 'del'=>0, 'pinyin'=>$pinyin, 'value'=>''];

                    }
                }

                if (\Setting::set('shop.form', json_encode(['base'=>$base, 'form'=>$data]))) {
                    return $this->successJson('自定义表单数据保存成功');
                } else {
                    return $this->errorJson('自定义表单数据保存错误');
                }
            }
            return $this->successJson('请求接口成功', [
                'set' => $set
            ]);
        }
        return view('setting.form.index');
    }

}