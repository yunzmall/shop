<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/7/9
 * Time: 上午11:03
 */

namespace app\frontend\modules\member\services;


use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\MemberGroup;
use app\common\models\Store;
use app\common\models\SynchronizedBinder;
use app\common\services\api\WechatApi;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\models\SubMemberModel;
use Illuminate\Support\Str;
use app\common\services\Session;
use Yunshop\AggregationCps\services\FreeLoginSign;
use Yunshop\AggregationCps\services\SettingManageService;

class MemberTjpCpsService extends MemberService
{
    private $appId;
    private $appSecret;
    private $uniqueCode;
    private $expires = 120;
    private $error = false;
    /**
     * @throws ShopException
     */
    public function login()
    {
        $this->verify(request()->input());
    }

    /**
     * 验证登录状态
     * @return bool
     * @throws ShopException
     */
    public function checkLogged()
    {
        return $this->verify(request()->input());
    }
    /**
     * @param $data
     * @return bool
     * @throws ShopException
     */
    public function verify($data)
    {
        $this->getAppData();
        if ($this->error) {
            return false;
        }
        $hfSign = new FreeLoginSign();
        $hfSign->setKey($this->appSecret);
        $hfSign->setUniqueCode($this->uniqueCode ? : '');
        $yzMember = MemberShopInfo::getMemberShopInfo($data['mob_user']);
        if (!$yzMember) {
            $bind_member = SynchronizedBinder::uniacid()->where('old_uid', $data['mob_user'])->first();
            if (!$bind_member) {
                return false;
            }
            $yzMember = MemberShopInfo::getMemberShopInfo($bind_member->new_uid);
            if (!$yzMember) {
                return false;
            }
        }
        if (!$data['app_token'] || $data['app_token'] != $yzMember->access_token_2) {
            return false;
        }
        $uid = $yzMember->member_id;
        Session::set('member_id', $uid);
        return true;
    }

    private function getAppData()
    {
        $appData = \Setting::get('plugin.aggregation-cps');
        if (is_null($appData) || !app('plugins')->isEnabled('aggregation-cps')) {
            $this->error = true;
            throw new ShopException('应用未启用');
        }
        if (!empty($appData['plat_unique_code']) && $appData['unique_mode']) {
            $appData['app_key'] = SettingManageService::getDefaultKey();
            $appData['app_secret'] = SettingManageService::getDefaultSecret();
            $this->uniqueCode = trim($appData['plat_unique_code']) ?: '';
        } elseif (empty($appData['app_key']) || empty($appData['app_secret'])) {
            $this->error = true;
            throw new ShopException('应用未启用');
        }
        if ($appData['app_key'] != request()->input('appid')) {
            $this->error = true;
            throw new ShopException('访问身份异常');
        }
        $this->appId     = $appData['app_key'];
        $this->appSecret = $appData['app_secret'];
    }

    public function addMcMember($data)
    {
        $member_model = new MemberModel();

        $member_model->uniacid    = $data['i'];
        $member_model->email      = '';
        $member_model->mobile     = $data['mob_user'];
        $member_model->groupid    = 0;
        $member_model->createtime = time();
        $member_model->nickname   = stripslashes($data['nickname']);
        $member_model->avatar     = isset($data['headimgurl']) ? $data['headimgurl'] : Url::shopUrl('static/images/photo-mr.jpg');;
        $member_model->gender         = isset($data['sex']) ? $data['sex'] : -1;
        $member_model->nationality    = isset($data['country']) ? $data['country'] : '';
        $member_model->resideprovince = isset($data['province']) ? $data['province'] : '' . '省';
        $member_model->residecity     = isset($data['city']) ? $data['city'] : '' . '市';
        $member_model->credit1        = isset($data['credit1']) ? $data['credit1'] : 0;
        $member_model->credit2        = isset($data['credit2']) ? $data['credit2'] : 0;
        $member_model->salt           = Str::random(8);
        $member_model->password       = md5(mt_rand());

        if ($member_model->save()) {
            return $member_model->uid;
        } else {
            return 0;
        }
    }

    public function addYzMember($member_id, $uniacid)
    {
        SubMemberModel::insertData(array(
            'member_id'    => $member_id,
            'uniacid'      => $uniacid,
            'group_id'     => 0,
            'level_id'     => 0,
            'pay_password' => '',
            'salt'         => '',
            'yz_openid'    => '',
        ));
    }
}