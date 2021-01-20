<?php


namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\Url;

class CacheController extends BaseController
{

    public function index()
    {
        $set = Setting::get('cache');

        $requestModel = request()->cache;
        if ($requestModel) {
            if (Setting::set('cache', $requestModel)) {
                return $this->successJson('缓存设置成功', Url::absoluteWeb('setting.cache.index'));
            } else {
                $this->errorJson('缓存设置失败');
            }
        }
        return view('setting.cache.index', [
            'set' => $set,
        ])->render();
    }
}