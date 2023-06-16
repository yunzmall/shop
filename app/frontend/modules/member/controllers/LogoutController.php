<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/3/2
 * Time: 上午7:37
 */

namespace app\frontend\modules\member\controllers;

use app\common\components\BaseController;

use app\common\helpers\Client;
use app\common\services\Session;
use app\frontend\modules\member\models\SubMemberModel;
use Illuminate\Support\Facades\Cookie;

class LogoutController extends BaseController
{
    public function index()
    {
        if (Client::is_nativeApp()) {
            $token = \YunShop::request()->yz_token;

            $member = SubMemberModel::getMemberByNativeToken($token);

            $member->access_token_2 = '';

            $member->save();
        } else {
            setcookie('Yz-Token', '', time() - 3600);
            setcookie('Yz-appToken', '', time() - 3600);
            setcookie('Yz-Token', '', time() - 3600,'/');
            setcookie('Yz-appToken', '', time() - 3600,'/');
            setcookie(session_name(), '',time() - 3600, '/');
            setcookie(session_name(), '',time() - 3600, '/addons/yun_shop');

            session_destroy();
        }

        return $this->successJson('退出成功');
    }
}
