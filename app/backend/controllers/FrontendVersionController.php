<?php


namespace app\backend\controllers;


use app\common\components\BaseController;

class FrontendVersionController extends BaseController
{
    public function index()
    {
        return view('frontend.version',[])->render();
    }

    public function getVersion()
    {
        $frontend_version = config('front-version');

        return $this->successJson('ok', ['version' => $frontend_version]);
    }

    public function change()
    {
        $version = request()->input('version');

        if (empty($version)) {
            return $this->errorJson('请填写版本号');
        }

        $str = file_get_contents(base_path('config/') . 'front-version.php');
        $str = preg_replace('/"[\d\.]+"/', '"' . $version . '"', $str);
        file_put_contents(base_path('config/') . 'front-version.php', $str);

        \Artisan::call('config:cache');

        return $this->successJson('ok');
    }
}