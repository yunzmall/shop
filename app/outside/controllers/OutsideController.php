<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/1/5
 * Time: 15:35
 */

namespace app\outside\controllers;


use app\common\exceptions\ApiException;
use app\common\exceptions\AppException;
use app\common\services\utils\EncryptUtil;
use app\outside\modes\OutsideAppSetting;
use app\outside\services\NotifyService;
use app\outside\services\OutsideAppService;
use Illuminate\Support\Facades\DB;
use app\common\models\AccountWechats;
use app\common\components\BaseController;


class OutsideController extends BaseController
{

    /**
     * @var OutsideAppSetting
     */
    public $outsideApp;


    protected $noAppMode = false; //无应用模式


    public $needVerifySign = true;

    /**
     * 前置action
     * @throws ApiException
     */
    public function preAction()
    {
        parent::preAction();

        $this->apiVerify();
    }

    /**
     * @throws ApiException
     */
    protected function apiVerify()
    {
        if ($this->noAppMode) {
            return;
        }

        $appID = trim(request()->input('app_id'));

        if (!is_numeric($appID)) {
            throw new ApiException('不符合标准的appID');
        }

        $appSet = OutsideAppSetting::where('app_id', $appID)->first();

        if (!$appSet) {
            throw new ApiException('应用不存在');
        }

        if (!$appSet->is_open) {
            throw new ApiException('应用已关闭');
        }

        $this->outsideApp = $appSet;

        //$this->setCurrentUniacid($appSet->uniacid);

        //开启签名验证，签名验证失败
        if(!$appSet->sign_required && !$this->appSignVerify()) {
            throw new ApiException('签名验证失败');
        }
    }

    protected function appSignVerify()
    {
        //过滤掉路由地址参数
        $requestData = request()->except([request()->getRequestUri(),'i']);

        return (new NotifyService($requestData))->verifySign();

    }

    //设置公众号
    protected function setCurrentUniacid($uniacid)
    {
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniacid;
        AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
    }
}