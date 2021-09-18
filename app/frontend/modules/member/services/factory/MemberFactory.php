<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/23
 * Time: 下午2:27
 */

namespace app\frontend\modules\member\services\factory;

use app\frontend\modules\member\services\MemberAnchorAppService;
use app\frontend\modules\member\services\MemberAppYdbService;
use app\frontend\modules\member\services\MemberCpsAppService;
use app\frontend\modules\member\services\MemberDouyinService;
use app\frontend\modules\member\services\MemberMiniAppFaceService;
use app\frontend\modules\member\services\MemberMobileService;
use app\frontend\modules\member\services\MemberNativeAppService;
use app\frontend\modules\member\services\MemberPcOfficeAccountService;
use app\frontend\modules\member\services\MemberTFBService;
use app\frontend\modules\member\services\MemberTjpCpsService;
use app\frontend\modules\member\services\MemberWechatService;
use app\frontend\modules\member\services\MemberAppWechatService;
use app\frontend\modules\member\services\MemberMiniAppService;
use app\frontend\modules\member\services\MemberOfficeAccountService;
use app\frontend\modules\member\services\MemberQQService;
use app\frontend\modules\member\services\MemberAlipayService;
use app\frontend\modules\member\services\SmsCodeService;
use app\frontend\modules\member\services\MemberWechatQrcodeService;
use Yunshop\Freelogin\common\service\FreeloginService;
use Yunshop\WechatChatSidebar\frontend\service\MemberWorkService;


class MemberFactory
{
    const LOGIN_OFFICE_ACCOUNT = 1;
    const LOGIN_MINI_APP = 2;
    const LOGIN_APP_WECHAT = 3;
    const LOGIN_WECHAT = 4;
    const LOGIN_MOBILE = 5;
    const LOGIN_QQ = 6;
    const LOGIN_APP_YDB = 7;
    const LOGIN_ALIPAY = 8;
    const LOGIN_Native = 9;
    const LOGIN_MOBILE_CODE = 10;
    const LOGIN_DOUYIN = 11;
    const LOGIN_MINI_APP_FACE = 12;
    const LOGIN_APP_ANCHOR = 14;
    const LOGIN_APP_CPS = 15;
    const LOGIN_PC_OFFICE_ACCOUNT = 16;
    const LOGIN_WORK = 17;

    public static function create($type = null)
    {
        $className = null;
        $scope = request()->input('scope');
        $is_pc_qrcode = request()->input('is_pc_qrcode') ?: 0;//1-PC扫码登录 0-否

        switch ($scope)
        {
            case 'tfb':
                return new MemberTFBService();
                break;
            case 'freelogin':
                return new FreeloginService();
                break;
            case 'tjpcps':
                return new MemberTjpCpsService();
                break;
        }

        switch($type)
        {
            case self::LOGIN_OFFICE_ACCOUNT:
                $className = new MemberOfficeAccountService();
                break;
            case self::LOGIN_MINI_APP:
                $className = new MemberMiniAppService();
                break;
            case self::LOGIN_APP_WECHAT:
                $className = new MemberAppWechatService();
                break;
            case self::LOGIN_WECHAT:
                $className = new MemberWechatService();
                break;
            case self::LOGIN_MOBILE:
                if((int)$is_pc_qrcode == 1){
                    $className = new MemberWechatQrcodeService();
                    break;
                }else{
                    $className = new MemberMobileService();
                    break;
                }
            case self::LOGIN_QQ:
                $className = new MemberQQService();
                break;
            case self::LOGIN_APP_YDB:
                $className = new MemberAppYdbService();
                break;
            case self::LOGIN_ALIPAY:
                $className = new MemberAlipayService();
                break;
            case self::LOGIN_Native:
                $className = new MemberNativeAppService();
                break;
            case self::LOGIN_MOBILE_CODE:
                $className = new SmsCodeService();
                break;
            case self::LOGIN_DOUYIN:
                $className = new MemberDouyinService();
                break;
            case self::LOGIN_MINI_APP_FACE:
                $className = new MemberMiniAppFaceService();
                break;
            case self::LOGIN_APP_ANCHOR:
                $className = new MemberAnchorAppService();
                break;
            case self::LOGIN_APP_CPS:
                $className = new MemberCpsAppService();
                break;
            case self::LOGIN_PC_OFFICE_ACCOUNT:
                $className = new MemberPcOfficeAccountService();
                break;
            case self::LOGIN_WORK:
                $className = new MemberWorkService();
                break;
            default:
                $className = null;
        }
        return $className;
    }
}