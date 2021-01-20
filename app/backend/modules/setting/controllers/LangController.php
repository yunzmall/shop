<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/9
 * Time: 下午5:26
 */

namespace app\backend\modules\setting\controllers;

use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\facades\Setting;

class LangController extends UploadVerificationBaseController
{
    private $locale = 'zh_cn';

    public function index()
    {
        if (request()->setdata) {
            return $this->store();
        }
        if (request()->ajax()) {
            return $this->successJson('ok', $this->langData());
        }
        return view('setting.shop.lang', $this->langData());
    }

    private function store()
    {
        $data['lang'] = $this->locale;

        $data[$this->locale] = request()->setdata;

        if (Setting::set('shop.lang', $data)) {
            return $this->successJson('语言设置成功');
        }
        return $this->errorJson('语言设置失败');
    }

    /**
     * @return array
     */
    private function langData()
    {
        $lang = $this->langSet();

        return ['set' => $lang[$lang['lang']]];
    }

    /**
     * @return array
     */
    private function langSet()
    {
        return Setting::get('shop.lang', ['lang' => 'zh_cn']);
    }
}
