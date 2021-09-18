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

class MemberTjpCpsService extends MemberService
{
    private $appId;
    private $appSecret;
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

        if ($hfSign->verify($data)) {

            $yzMember = MemberShopInfo::getMemberShopInfo($data['mob_user']);

            if (is_null($yzMember)) {
                return false;
            }
            $uid = $yzMember->member_id;
//            $MemberModel = MemberModel::getId($data['i'], $data['mob_user']);
//            if (is_null($MemberModel)) {
//                $uid = $this->addMcMember(request()->input());
//
//                if ($uid) {
//                    $this->addYzMember($uid, request()->input('i'));
//                }
//            } else {
//                $yzMember = MemberShopInfo::getMemberShopInfo($MemberModel->uid);
//
//                if (is_null($yzMember)) {
//                    $this->addYzMember($MemberModel->uid, request()->input('i'));
//                }
//
//                $uid = $MemberModel->uid;
//            }

            Session::set('member_id', $uid);

            return true;
        }

        return false;
    }

    private function getAppData()
    {
        $appData = \Setting::get('plugin.aggregation-cps');

        if (is_null($appData)) {
            $this->error = true;
            throw new ShopException('应用未启用');
        }

        if (empty($appData['app_key']) || empty($appData['app_secret'])) {
            $this->error = true;
            throw new ShopException('应用未启用');
        }

        if ($appData['app_key'] != request()->input('appid')) {
            $this->error = true;
            throw new ShopException('访问身份异常');
        }

        if (!\YunShop::app()->getMemberId()) {
//            if (time() - request()->input('timestamp') > $this->expires) {
//                $this->error = true;
//                throw new ShopException('访问超时');
//            }
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