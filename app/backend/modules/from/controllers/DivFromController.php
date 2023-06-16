<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/8/25 下午1:53
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 ****************************************************************/

namespace app\backend\modules\from\controllers;


use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\helpers\Url;
use app\common\traits\MessageTrait;

class DivFromController extends UploadVerificationBaseController
{
    use MessageTrait;

    public function index()
    {
        return view('from.index');
//        return view('from.index', [
//            'div_from' => array_pluck(\Setting::getAllByGroup('div_from')->toArray(), 'value', 'key')
//        ])->render();
    }

    public function getData()
    {
        return $this->successJson('ok',array_pluck(\Setting::getAllByGroup('div_from')->toArray(), 'value', 'key'));
    }

    public function store()
    {
        $requestData = request()->div_from;
        if ($requestData) {
            foreach ($requestData as $key => $item) {
                \Setting::set('div_from.' . $key, $item);
            }
            return $this->successJson('设置保存成功');
//            return $this->message("设置保存成功",Url::absoluteWeb('from.div-from.index'));
        }
        return $this->index();
    }


}