<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/1
 * Time: 下午4:39
 */

namespace app\frontend\modules\member\controllers;

use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\backend\modules\member\models\MemberParent;
use app\backend\modules\member\models\MemberRelation;
use app\backend\modules\order\models\Order;
use app\common\components\ApiController;
use app\common\events\member\MemberBindMobile;
use app\common\events\member\MemberNewOfflineEvent;
use app\common\events\member\MergeMemberEvent;
use app\common\events\order\OrderMiniNoticeListEvent;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\facades\EasyWeChat;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\helpers\Client;
use app\common\helpers\ImageHelper;
use app\common\helpers\MiniCodeHelper;
use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\Address;
use app\common\models\Area;
use app\common\models\Goods;
use app\common\models\Income;
use app\common\models\McMappingFans;
use app\common\models\member\MemberCancelSet;
use app\common\models\member\MemberInvitationCodeLog;
use app\common\models\member\MemberInviteGoodsLog;
use app\common\models\member\MemberMerge;
use app\common\models\member\MemberPosition;
use app\common\models\MemberAddress;
use app\common\models\MemberAlipay;
use app\common\models\MemberGroup;
use app\common\models\MemberShopInfo;
use app\common\models\MiniTemplateCorresponding;
use app\common\models\notice\MinAppTemplateMessage;
use app\common\models\YzMemberAddress;
use app\common\modules\member\MemberCenter;
use app\common\services\alipay\OnekeyLogin;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\PointService;
use app\common\services\member\MemberCenterService;
use app\common\services\member\MemberMergeService;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\popularize\PortType;
use app\common\services\Session;
use app\common\services\Utils;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\frontend\modules\member\models\MemberDouyinModel;
use app\frontend\modules\member\models\MemberFavorite;
use app\frontend\modules\member\models\MemberMiniAppModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\models\SubMemberModel;
use app\frontend\modules\member\services\MemberCenterDataService;
use app\frontend\modules\member\services\MemberMiniAppService;
use app\frontend\modules\member\services\MemberReferralService;
use app\frontend\modules\member\services\MemberService;
use EasyWeChat\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Yunshop\AlipayOnekeyLogin\services\SynchronousUserInfo;
use Yunshop\Commission\models\Agents;
use Yunshop\Decorate\models\DecorateDefaultTabModel;
use Yunshop\Decorate\models\DecorateDefaultTemplateModel;
use Yunshop\Designer\models\ViewSet;
use Yunshop\DrainageCode\common\services\SceneService;
use Yunshop\GroupWork\frontend\modules\order\models\OrderModel;
use Yunshop\Kingtimes\common\models\Distributor;
use Yunshop\Kingtimes\common\models\Provider;
use Yunshop\LevelCompel\common\services\LevelCompelService;
use Yunshop\Love\Common\Models\MemberLove;
use Yunshop\Love\Common\Services\LoveChangeService;
use Yunshop\Love\Common\Services\SetService;
use Yunshop\Poster\models\Poster;
use Yunshop\Poster\services\CreatePosterService;
use Yunshop\RealNameAuth\common\models\RealNameAuth;
use Yunshop\RealNameAuth\common\models\RealNameAuthSet;
use Yunshop\RegistrationArea\Common\models\MemberLocation;
use Yunshop\ShopEsign\common\service\YunSignService;
use Yunshop\StoreCashier\common\models\Store;
use Yunshop\Designer\models\Designer;
use app\frontend\models\MembershipInformationLog;
use Yunshop\Designer\Backend\Modules\Page\Controllers\RecordsController;
use app\common\models\SynchronizedBinder;
use Illuminate\Support\Facades\Cookie;
use Yunshop\YunSign\common\models\Contract;
use Yunshop\YunSign\common\models\ContractNum;
use Yunshop\YunSign\common\models\PersonAccount;


class MemberController extends ApiController
{
    protected $publicAction = [
        'guideFollow',
        'wxJsSdkConfig',
        'memberFromHXQModule',
        'dsAlipayUserModule',
        'isValidatePage',
        'designer',
        'getAdvertisement',
        'miniMemberMobile',
        'getProvincesList',
        'getCitysByProvince',
        'getAreasByCity'
    ];
    protected $ignoreAction = [
        'guideFollow',
        'wxJsSdkConfig',
        'memberFromHXQModule',
        'dsAlipayUserModule',
        'isValidatePage',
        'designer',
        'getAdvertisement',
        'miniMemberMobile',
        'getProvincesList',
        'getCitysByProvince',
        'getAreasByCity'
    ];
    protected $type;
    protected $sign;
    protected $set;
    protected $relation_base_set;

    public $apiErrMsg = [];

    public $apiData = [];

    /**
     * 获取用户信息
     * @param $request
     * @param null $integrated
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getUserInfo(Request $request, $integrated = null)
    {
        $member_id = \YunShop::app()->getMemberId();

        if (empty($member_id)) {
            if (is_null($integrated)) {
                return $this->errorJson('缺少访问参数');
            } else {
                return show_json(0, '缺少访问参数');
            }
        }

        $this->type = intval(\YunShop::request()->type);
        $this->sign = intval(\YunShop::request()->ingress);

        $memberModel = $member_info = MemberModel::getUserInfos_v2($member_id)->first();
        if (empty($member_info)) {
            $this->jump = true;
            $mid = \app\common\models\Member::getMid();
            $this->jumpUrl(\YunShop::request()->type, $mid);
        }

        $member_info = $member_info->toArray();
        $data = MemberModel::userData_v2($member_info, $member_info['yz_member']);

        $switch = PortType::popularizeShow(\YunShop::request()->type);

        $un_withdraw = Income::getIncomes()
            ->where('member_id', \YunShop::app()->getMemberId())
            ->where('status', 0)
            ->sum('amount');

        $data['un_withdraw'] = number_format($un_withdraw, 2) ?? 0;

        //会员收入
        if ($switch) {
            $data['income'] = MemberModel::getIncomeCount();
        }

        //自定义表单
        $data['myform'] = (new MemberService())->memberInfoAttrStatus($member_info['yz_member']);

        //邀请码
        $v = request('v');
        if (!is_null($v)) {
            $data['inviteCode']['status'] = \Setting::get('shop.member.is_invite') ?: 0;
            if (is_null($member_info['yz_member']['invite_code']) || empty($member_info['yz_member']['invite_code'])) {
                $data['inviteCode']['code'] = MemberModel::getInviteCode($member_id);
            } else {
                $data['inviteCode']['code'] = $member_info['yz_member']['invite_code'];
            }
        } else {
            $data['inviteCode'] = 0;
        }

        // 邀请页面总店强制修改
        $member_set = \Setting::get('shop.member');
        $data['is_bind_invite'] = $member_set['is_bind_invite'] ?: 0;  // 邀请页面总店强制修改
        $data['copyrightImg'] = yz_tomedia(\Setting::get('shop.shop.copyrightImg')) ?: '';
        $data['copyright'] = \Setting::get('shop.shop.copyright') ?: '';
        $data['cat_adv_url'] = \Setting::get('shop.shop.cat_adv_url') ?: '';
        $data['small_cat_adv_url'] = \Setting::get('shop.shop.small_cat_adv_url') ?: '';

        if (MemberShopInfo::getParentId($member_id) > 0) { // 不是总店
            $data['is_bind_invite'] = 0;
        }
        $member_cancel_set = MemberCancelSet::uniacid()->select('status')->first();
        if (isset($member_cancel_set['status'])) {
            $data['member_cancel_status'] = $member_cancel_set['status'];
        } else {
            $data['member_cancel_status'] = 1;
        }
        //这个参数是要在会员设置里使用的，别再把这个参数移走了
        //易宝标准版
        $data['yop'] = app('plugins')->isEnabled('yop-pay') ? 1 : 0;

        //易宝专业版
        $data['yop_pro'] = app('plugins')->isEnabled('yop-pro') ? 1 : 0;

        //0.1拼团
        $data['group_work'] = app('plugins')->isEnabled('group-work') ? 1 : 0;

        // 汇聚支付是否开启
        $data['is_open_converge_pay'] = app('plugins')->isEnabled('converge_pay') ? 1 : 0;

        //CMC充值
        $data['cmc_pay'] = app('plugins')->isEnabled('cmc-pay') ? 1 : 0;

        //慈善基金-总捐赠金额
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('charity_fund_charity_total_money'))) {
            $charity_fund_config = \Yunshop\CharityFund\services\SetConfigService::getSetConfig();
            if ($charity_fund_config->is_open && $charity_fund_config->is_show_money) {
                $class = array_get(
                    \app\common\modules\shop\ShopConfig::current()->get('charity_fund_charity_total_money'),
                    'class'
                );
                $function = array_get(
                    \app\common\modules\shop\ShopConfig::current()->get('charity_fund_charity_total_money'),
                    'function'
                );
                $ret = $class::$function();

                $data['charity_total_money'] = $ret ?: 0;
            }
        }
        $set = json_decode(\Setting::get('shop.form'), true);

        $data['name_must'] = $set['base']['name_must'];

        $data['change_info'] = $set['base']['change_info'] == 1 ? true : false;

        $data['has_avatar'] = $memberModel->has_avatar;//用来判断会员是否有设置头像，avatar没设置也默认返回头像了

        //会员团队
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('team_name')) && \YunShop::app()->getMemberId(
            )) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('team_name'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('team_name'), 'function');
            $member_team = $class::$function(\YunShop::app()->getMemberId());
            if ($member_team['res']) {
                $data['member_team'] = $member_team;
            }
        }

        //会员等级天数
        $data['validity_day'] = $member_info['yz_member']['validity'];

        if (is_null($integrated)) {
            return $this->successJson('', $data);
        } else {
            return show_json(1, $data);
        }
    }

    /**
     * 检查会员推广资格
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberRelationInfo()
    {
        $info = MemberRelation::getSetInfo()->first();

        $member_info = SubMemberModel::getMemberShopInfo(\YunShop::app()->getMemberId());

        if (empty($info)) {
            return $this->errorJson('缺少参数');
        } else {
            $info = $info->toArray();
        }

        if (empty($member_info)) {
            return $this->errorJson('会员不存在');
        } else {
            $data = $member_info->toArray();
        }

        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
        switch ($info['become']) {
            case 0:
            case 1:
                $apply_qualification = 1;
                $mid = \app\common\models\Member::getMid();

                $m_member = MemberShopInfo::where('member_id', \YunShop::app()->getMemberId())->first();

                if ($m_member->parent_id != $mid && $m_member->parent_id != 0) {
                    $mid = $m_member->parent_id ?: 0;
                }

                $parent_name = '';

                if (empty($mid)) {
                    $parent_name = '总店';
                } else {
                    $parent_model = MemberModel::getMemberById($mid);

                    if (!empty($parent_model)) {
                        $parent_member = $parent_model->toArray();
                        $status = '';
                        if ($parent_model['inviter']) {
                            $status = '(暂定)';
                        }
                        $parent_name = $parent_member['realname'] ?: $status . $parent_member['nickname'];
                    }
                }

                $member_model = MemberModel::getMemberById(\YunShop::app()->getMemberId());

                if (!empty($member_model)) {
                    $member = $member_model->toArray();
                }
                break;
            case 2:
                $apply_qualification = 2;
                $cost_num = Order::getCostTotalNum(\YunShop::app()->getMemberId());

                if ($info['become_check'] && $cost_num >= $info['become_ordercount']) {
                    $apply_qualification = 5;
                }
                break;
            case 3:
                $apply_qualification = 3;
                $cost_price = Order::getCostTotalPrice(\YunShop::app()->getMemberId());

                if ($info['become_check'] && $cost_price >= $info['become_moneycount']) {
                    $apply_qualification = 6;
                }
                break;
            case 4:
                $apply_qualification = 4;
                $goods = Goods::getGoodsById($info['become_goods_id']);
                $goods_name = '';

                if (!empty($goods)) {
                    $goods = $goods->toArray();

                    $goods_name = $goods['title'];
                }

                if ($info['become_check'] && MemberRelation::checkOrderGoods(
                        $info['become_goods_id'],
                        $member_info->member_id
                    )) {
                    $apply_qualification = 7;
                }
                break;
            default:
                $apply_qualification = 0;
        }

        $relation = [
            'switched' => $info['status'],
            'become'   => $apply_qualification,
            'become1'  => [
                'shop_name'   => $account['name'],
                'parent_name' => $parent_name,
                'realname'    => $member['realname'],
                'mobile'      => $member['mobile']
            ],
            'become2'  => ['shop_name' => $account['name'], 'total' => $info['become_ordercount'], 'cost' => $cost_num],
            'become3'  => [
                'shop_name' => $account['name'],
                'total'     => $info['become_moneycount'],
                'cost'      => $cost_price
            ],
            'become4'  => [
                'shop_name'  => $account['name'],
                'goods_name' => $goods_name,
                'goods_id'   => $info['become_goods_id']
            ],
            'is_agent' => $data['is_agent'],
            'status'   => $data['status'],
            'account'  => $account['name']
        ];

        return $this->successJson('', $relation);
    }

    /**
     * 会员是否有推广权限
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function isAgent()
    {
        if (MemberModel::isAgent()) {
            $has_permission = 1;
        } else {
            $has_permission = 0;
        }

        return $this->successJson('', ['is_agent' => $has_permission]);
    }

    /**
     * 会员推广二维码
     *
     * @param $url
     * @param string $extra
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgentQR($extra = '')
    {
        if (empty(\YunShop::app()->getMemberId())) {
            return $this->errorJson('请重新登录');
        }

        $qr_url = MemberModel::getAgentQR($extra = '');

        return $this->successJson('', ['qr' => $qr_url]);
    }

    /**
     * 用户推广申请
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAgentApply()
    {
        if (!\YunShop::app()->getMemberId()) {
            return $this->errorJson('请重新登录');
        }
        $sub_member_model = SubMemberModel::getMemberShopInfo(\YunShop::app()->getMemberId());

        $sub_member_model->status = 1;
        $sub_member_model->apply_time = time();

        if (!$sub_member_model->save()) {
            return $this->errorJson('会员信息保存失败');
        }

        $realname = \YunShop::request()->realname;
        $moible = \YunShop::request()->mobile;

        $member_mode = MemberModel::getMemberById(\YunShop::app()->getMemberId());

        $member_mode->realname = $realname;
        $member_mode->mobile = $moible;

        if (!$member_mode->save()) {
            return $this->errorJson('会员信息保存失败');
        }

        return $this->successJson('ok');
    }

    /**
     * 获取我的下线
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyAgentCount()
    {
        return $this->successJson('', ['count' => MemberModel::getAgentCount_v2(\YunShop::app()->getMemberId())]);
    }

    /**
     * 我的推荐人
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyReferral()
    {
        $data = MemberModel::getMyReferral();

        if (!empty($data)) {
            return $this->successJson('', $data);
        } else {
            return $this->errorJson('会员不存在');
        }
    }

    /**
     * 我的推荐人v2
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyReferral_v2(Request $request, $integrated = null)
    {
        $data = MemberModel::getMyReferral_v2();

        //IOS时，把微信头像url改为https前缀
        $data['avatar'] = ImageHelper::iosWechatAvatar($data['avatar']);

        if (!empty($data)) {
            if (is_null($integrated)) {
                return $this->successJson('', $data);
            } else {
                return show_json(1, $data);
            }
        } else {
            if (is_null($integrated)) {
                return $this->errorJson('会员不存在');
            } else {
                return show_json(0, '会员不存在');
            }
        }
    }

    /**
     * 会员推荐人上级
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getMyReferralParents()
    {
        $member_id = \YunShop::app()->getMemberId();
        $yz_member = MemberShopInfo::getMemberShopInfo($member_id);
        if ($yz_member['inviter'] == 1 && !empty(MemberShopInfo::getMemberShopInfo($yz_member['parent_id']))) {
            $data = MemberParent::getAgentParentByMemberId($yz_member['parent_id']);
            return show_json(1, $data);
        } else {
            return show_json(1, ['is_show' => 0]); //没有推荐人上级
        }
    }

    /**
     * 我推荐的人
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyAgent()
    {
        $data = MemberModel::getMyAgent();

        if (!empty($data)) {
            return $this->successJson('', $data);
        } else {
            return $this->errorJson('会员不存在');
        }
    }

    /**
     * 我推荐的人 v2 基本信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyAgent_v2(Request $request, $integrated = null)
    {
        $data = MemberModel::getMyAgent_v2();

        if (is_null($integrated)) {
            return $this->successJson('', $data);
        } else {
            return show_json(1, $data);
        }
    }

    /**
     * 我推荐的人 v2 数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyAgentData_v2(Request $request, $integrated = null)
    {
        app('db')->cacheSelect = true;

        $data = MemberModel::getMyAgentData_v2();

        if (is_null($integrated)) {
            return $this->successJson('', $data);
        } else {
            return show_json(1, $data);
        }
    }

    /**
     * 会员中心我的关系
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyRelation()
    {
        $my_referral = MemberModel::getMyReferral();

        $my_agent = MemberModel::getMyAgent();

        $data = [
            'my_referral' => $my_referral,
            'my_agent'    => $my_agent
        ];

        return $this->successJson('', $data);
    }

    /**
     * 通过省份id获取对应的市信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCitysByProvince()
    {
        $id = \YunShop::request()->parent_id;

        $data = Area::getCitysByProvince($id);

        if (!empty($data)) {
            return $this->successJson('', $data->toArray());
        } else {
            return $this->errorJson('查无数据');
        }
    }

    /**
     * 通过市id获取对应的区信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreasByCity()
    {
        $id = \YunShop::request()->parent_id;

        $data = Area::getAreasByCity($id);

        if (!empty($data)) {
            return $this->successJson('', $data->toArray());
        } else {
            return $this->errorJson('查无数据');
        }
    }

    /**
     * 获取所有省份数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvincesList()
    {
        $data = Area::getProvincesList();

        if (!empty($data)) {
            return $this->successJson('', $data->toArray());
        } else {
            return $this->errorJson('查无数据');
        }
    }

    /**
     * 更新会员资料
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserInfo()
    {
        $birthday = [];
        $data = \YunShop::request()->data;
        $get = json_decode(\Setting::get('shop.form'), true);
        if (empty($data['realname']) && $get['base']['name_must'] == 1) {
            return $this->errorJson('请填写姓名');
        }
        //商家App获取的数据是json字符串
        if (\Yunshop::request()->type == 9) {
            $data = json_decode($data, true);
        }

        if (isset($data['birthday'])) {
            $birthday = explode('-', $data['birthday']);
        }

        $member_data = [
            'realname'   => $data['realname'] ?: '',
            'avatar'     => $data['avatar'],
            'gender'     => isset($data['gender']) ? intval($data['gender']) : 0,
            'birthyear'  => isset($birthday[0]) ? intval($birthday[0]) : 0,
            'birthmonth' => isset($birthday[1]) ? intval($birthday[1]) : 0,
            'birthday'   => isset($birthday[2]) ? intval($birthday[2]) : 0
        ];

        if ($data['nickname']) {
            $member_data['nickname'] = $data['nickname'];
        }

//        if (!empty($data['mobile'])) {
//            $member_data['mobile'] = $data['mobile'];
//        }
        $member_data['mobile'] = $data['mobile'];

        if (!empty($data['telephone'])) {
            $member_data['telephone'] = $data['telephone'];
        }

        $member_shop_info_data = [
            'alipay'        => $data['alipay'],
            'alipayname'    => $data['alipay_name'],
            'province_name' => isset($data['province_name']) ? $data['province_name'] : '',
            'city_name'     => isset($data['city_name']) ? $data['city_name'] : '',
            'area_name'     => isset($data['area_name']) ? $data['area_name'] : '',
            'province'      => isset($data['province']) ? intval($data['province']) : 0,
            'city'          => isset($data['city']) ? intval($data['city']) : 0,
            'area'          => isset($data['area']) ? intval($data['area']) : 0,
            'address'       => isset($data['address']) ? $data['address'] : '',
            'wechat'        => isset($data['wx']) ? $data['wx'] : '',
        ];


        if (\YunShop::app()->getMemberId()) {
//            $memberService = app(MemberService::class);
//            $memberService->chkAccount(\YunShop::app()->getMemberId());

            $member_model = MemberModel::getMemberById(\YunShop::app()->getMemberId());
            $member_shop_info_model = MemberShopInfo::getMemberShopInfo(\YunShop::app()->getMemberId());

            $old_data = [
                'alipay'     => $member_shop_info_model->alipay,
                'alipayname' => $member_shop_info_model->alipayname,
                'wechat'     => $member_shop_info_model->wechat,
                'mobile'     => $member_model->mobile,
                'name'       => $member_model->realname,
                'type'       => \YunShop::request()->type
            ];

            $new_data = [
                'alipay'     => $data['alipay'],
                'alipayname' => $data['alipay_name'],
                'wechat'     => isset($data['wx']) ? $data['wx'] : '',
                'mobile'     => $data['mobile'],
                'name'       => $data['realname'],
                'type'       => \YunShop::request()->type
            ];

            $membership_infomation = [
                'uniacid'    => \YunShop::app()->uniacid,
                'uid'        => \YunShop::app()->getMemberId(),
                'old_data'   => serialize($old_data),
                'new_data'   => serialize($new_data),
                'session_id' => session_id()
            ];


            MembershipInformationLog::create($membership_infomation);


            $member_model->setRawAttributes($member_data);
            $member_shop_info_model->setRawAttributes($member_shop_info_data);

            $member_validator = $member_model->validator($member_model->getAttributes());
            $member_shop_info_validator = $member_shop_info_model->validator($member_shop_info_model->getAttributes());

            if ($member_validator->fails()) {
                $warnings = $member_validator->messages();
                $show_warning = $warnings->first();

                return $this->errorJson($show_warning);
            }

            if ($member_shop_info_validator->fails()) {
                $warnings = $member_shop_info_validator->messages();
                $show_warning = $warnings->first();
                return $this->errorJson($show_warning);
            }

            //自定义表单
            $member_form = (new MemberService())->updateMemberForm($data);

            if (!empty($member_form)) {
                $member_shop_info_model->member_form = json_encode($member_form);
            }

            if ($member_model->save() && $member_shop_info_model->save()) {
                if (Cache::has($member_model->uid . '_member_info')) {
                    Cache::forget($member_model->uid . '_member_info');
                }

                $phoneModel = PhoneAttribution::getMemberByID(\YunShop::app()->getMemberId());
                if (!is_null($phoneModel)) {
                    $phoneModel->delete();
                }

                //手机归属地查询插入
                $phoneData = file_get_contents((new PhoneAttributionService())->getPhoneApi($member_model->mobile));
                $phoneArray = json_decode($phoneData);
                $phone['uid'] = \YunShop::app()->getMemberId();
                $phone['uniacid'] = \YunShop::app()->uniacid;
                $phone['province'] = $phoneArray->data->province;
                $phone['city'] = $phoneArray->data->city;
                $phone['sp'] = $phoneArray->data->sp;

                $phoneModel = new PhoneAttribution();
                $phoneModel->updateOrCreate(['uid' => \YunShop::app()->getMemberId()], $phone);


                return $this->successJson('用户资料修改成功');
            } else {
                return $this->errorJson('更新用户资料失败');
            }
        } else {
            return $this->errorJson('用户不存在');
        }
    }

    public function updateWxOrAli()
    {
        try {
            $yz_member = MemberShopInfo::getMemberShopInfo(\YunShop::app()->getMemberId());
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
        }
        if (request()->wx) {
            $yz_member->wechat = request()->wx;
        }
        if (request()->alipay) {
            $yz_member->alipay = request()->alipay;
        }
        if (request()->alipay_name) {
            $yz_member->alipayname = request()->alipay_name;
        }
        if (!$yz_member->save()) {
            return $this->errorJson('保存失败');
        }
        return $this->successJson('保存成功');
    }

    /**
     * 手机预绑定
     * @return \Illuminate\Http\JsonResponse
     */
    public function prepBind()
    {
        $mobile = \YunShop::request()->mobile;
        $invite_code = \YunShop::request()->invite_code;
        $uid = \YunShop::app()->getMemberId();
        $is_show = false;
        $save_uid = 0;
        $del_uid = 0;
        if (empty($mobile)) {
            return $this->errorJson('输入手机号码为空');
        }

        $member_merge_set = Setting::get('relation_base');
        $member_model = MemberModel::getMemberById($uid); //当前登录会员
        $memberinfo_model = MemberModel::getMemberinfo(\YunShop::app()->uniacid, $mobile); //老手机会员
        if (empty($member_merge_set['is_merge_save_level'])) {
            //注册时间
            if (!empty($memberinfo_model) && ($memberinfo_model->createtime < $member_model->createtime)) {
                $save_uid = $memberinfo_model->uid;
                $del_uid = $uid;
            } elseif (!empty($memberinfo_model) && ($memberinfo_model->createtime > $member_model->createtime)) {
                $save_uid = $uid;
                $del_uid = $memberinfo_model->uid;
            }
        } elseif ($member_merge_set['is_merge_save_level'] == 1) {
            //手机号
            if (!empty($memberinfo_model)) {
                $save_uid = $memberinfo_model->uid;
                $del_uid = $uid;
            }
        } elseif ($member_merge_set['is_merge_save_level'] == 2) {
            //公众号
            $fans = McMappingFans::getFansById($uid);
            if ($fans) {
                if ($memberinfo_model) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            } else {
                if (!empty($memberinfo_model) && ($memberinfo_model->createtime < $member_model->createtime)) {
                    $save_uid = $memberinfo_model->uid;
                    $del_uid = $uid;
                } elseif (!empty($memberinfo_model) && ($memberinfo_model->createtime > $member_model->createtime)) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            }
        } elseif ($member_merge_set['is_merge_save_level'] == 3) {
            //小程序
            $mini_fans = MemberMiniAppModel::getFansById($uid);
            if ($mini_fans) {
                if ($memberinfo_model) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            } else {
                if (!empty($memberinfo_model) && ($memberinfo_model->createtime < $member_model->createtime)) {
                    $save_uid = $memberinfo_model->uid;
                    $del_uid = $uid;
                } elseif (!empty($memberinfo_model) && ($memberinfo_model->createtime > $member_model->createtime)) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            }
        } elseif ($member_merge_set['is_merge_save_level'] == 4) {
            //app
            $app_fans = MemberWechatModel::getFansById($uid);
            if ($app_fans) {
                if ($memberinfo_model) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            } else {
                if (!empty($memberinfo_model) && ($memberinfo_model->createtime < $member_model->createtime)) {
                    $save_uid = $memberinfo_model->uid;
                    $del_uid = $uid;
                } elseif (!empty($memberinfo_model) && ($memberinfo_model->createtime > $member_model->createtime)) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            }
        } else {
            //alipay
            $ali_fans = MemberAlipay::getFansById($uid);
            if ($ali_fans) {
                if ($memberinfo_model) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            } else {
                if (!empty($memberinfo_model) && ($memberinfo_model->createtime < $member_model->createtime)) {
                    $save_uid = $memberinfo_model->uid;
                    $del_uid = $uid;
                } elseif (!empty($memberinfo_model) && ($memberinfo_model->createtime > $member_model->createtime)) {
                    $save_uid = $uid;
                    $del_uid = $memberinfo_model->uid;
                }
            }
        }
        if (!empty($save_uid) && !empty($del_uid) && $del_uid != $save_uid) {
            $is_show = true;
        }
        return $this->successJson('ok', [
            'is_show'    => $is_show,
            'change_uid' => $save_uid,
            'uid'        => $del_uid,
        ]);
    }

    /**
     * 单纯绑定手机号
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function justBindMobile()
    {
        $mobile = \YunShop::request()->mobile;
        $uid = \YunShop::app()->getMemberId();
        $type = \YunShop::request()->type;
        if (empty($mobile)) {
            return $this->errorJson('输入手机号码为空');
        }
        $member_merge_set = Setting::get('relation_base');
        $member_model = MemberModel::getMemberById($uid);
        if (!empty($member_model->mobile) && $member_model->mobile == $mobile) {
            return $this->successJson('手机号码绑定成功');
        }
        $yz_member = MemberShopInfo::getMemberShopInfo($uid);
        if ($yz_member->is_old) {
            throw new AppException('会员数据有冲突，请联系客服');
        }
        if ($uid > 0) {
            $check_code = MemberService::checkCode();
            if ($check_code['status'] != 1) {
                return $this->errorJson($check_code['json']);
            }
            //查询绑定手机号会员
            $member_info_model = MemberModel::getMemberinfo(\YunShop::app()->uniacid, $mobile);
            \Log::debug('------会员设置--保留方式---just_bind---', $member_merge_set);
            \Log::debug('----手机号码绑定--提交的手机号码--当前登录会员信息--手机原始会员信息----', [$mobile, $member_model, $member_info_model]);
            if ($member_info_model && (($type == 5 && request()->scope) || $type != 5)) {
                $merge_choice = $member_merge_set['is_merge_save_level'];
                switch ($merge_choice) {
                    case 1 :
                        //手机号
                        $member_model = $this->phoneMemberSave($uid, $member_info_model, $member_model, $mobile);
                        break;
                    case 2 :
                        //公众号
                        $fans = McMappingFans::getFansById($uid);
                        if ($fans) {
                            $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                        } else {
                            if ($member_info_model->createtime < $member_model->createtime) {
                                $member_model = $this->phoneMemberSave(
                                    $uid,
                                    $member_info_model,
                                    $member_model,
                                    $mobile
                                );
                            } elseif ($member_info_model->createtime > $member_model->createtime) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            }
                        }
                        break;
                    case 3 :
                        //小程序
                        $mini_fans = MemberMiniAppModel::getFansById($uid);
                        if ($mini_fans) {
                            $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                        } else {
                            if ($member_info_model->createtime < $member_model->createtime) {
                                $member_model = $this->phoneMemberSave(
                                    $uid,
                                    $member_info_model,
                                    $member_model,
                                    $mobile
                                );
                            } elseif ($member_info_model->createtime > $member_model->createtime) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            }
                        }
                        break;
                    case 4 :
                        //app
                        $app_fans = MemberWechatModel::getFansById($uid);
                        if ($app_fans) {
                            $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                        } else {
                            if ($member_info_model->createtime < $member_model->createtime) {
                                $member_model = $this->phoneMemberSave(
                                    $uid,
                                    $member_info_model,
                                    $member_model,
                                    $mobile
                                );
                            } elseif ($member_info_model->createtime > $member_model->createtime) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            }
                        }
                        break;
                    case 5 :
                        //alipay
                        $app_fans = MemberAlipay::getFansById($uid);
                        if ($app_fans) {
                            $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                        } else {
                            if ($member_info_model->createtime < $member_model->createtime) {
                                $member_model = $this->phoneMemberSave(
                                    $uid,
                                    $member_info_model,
                                    $member_model,
                                    $mobile
                                );
                            } elseif ($member_info_model->createtime > $member_model->createtime) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            }
                        }
                        break;
                    default :
                        //注册时间
                        if ($member_info_model->createtime < $member_model->createtime) {
                            $member_model = $this->phoneMemberSave($uid, $member_info_model, $member_model, $mobile);
                        } elseif ($member_info_model->createtime > $member_model->createtime) {
                            $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                        }
                        break;
                }
            }
            if (!$member_model) {
                return $this->errorJson('手机号码绑定失败！');
            }
            $member_model->mobile = $mobile;
            $member_shop_info = MemberShopInfo::uniacid()->where('member_id', $member_model->uid)->first();
            if (!$member_model) {
                return $this->errorJson('手机号码绑定失败！');
            }
            if ($member_model->save() && $member_shop_info->save()) {
                if ($member_model->save() && $member_shop_info->save()) {
                    if (Cache::has($member_model->uid . '_member_info')) {
                        Cache::forget($member_model->uid . '_member_info');
                    }
                    if (\YunShop::request()->positioning_success == 1) {
                        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('set_location'))) {
                            $class = array_get(
                                \app\common\modules\shop\ShopConfig::current()->get('set_location'),
                                'class'
                            );
                            $function = array_get(
                                \app\common\modules\shop\ShopConfig::current()->get('set_location'),
                                'function'
                            );
                            $class::$function(
                                $member_model->uid,
                                MemberLocation::TYPE_BIND_MOBILE,
                                \YunShop::request()->register_province,
                                \YunShop::request()->register_city
                            );
                        }
                    }
                }
                event(new MemberBindMobile($member_model));
                return $this->successJson('手机号码绑定成功');
            } else {
                return $this->errorJson('手机号码绑定失败');
            }
        } else {
            return $this->errorJson('手机号或密码格式错误');
        }
    }

    /**
     * 小程序首次绑定手机
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function miniFirstTimeBindMobile()
    {
        $mobile = \YunShop::request()->mobile;
        $uid = \YunShop::app()->getMemberId();
        $type = \YunShop::request()->type;

        $is_first_time_bind = (bool)Setting::get('plugin.min_app.is_first_time_bind');
        if (!$is_first_time_bind || $type != 2 || $uid == 0 || empty($mobile)) {
            return $this->errorJson('不符合自动绑定手机号要求');
        }

        $member_model = MemberModel::getMemberById($uid);
        if (!empty($member_model->mobile) && $member_model->mobile == $mobile) {
            return $this->successJson('已绑定手机号');
        }

        $yz_member = MemberShopInfo::getMemberShopInfo($uid);
        if ($yz_member->is_old) {
            throw new AppException('会员数据有冲突，请联系客服');
        }

        $member_model->mobile = $mobile;
        if (!$member_model->save()) {
            return $this->errorJson('手机号码绑定失败！');
        }

        event(new MemberBindMobile($member_model));
        return $this->successJson('手机号码绑定成功');
    }


    /**
     * 绑定手机号
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function bindMobile()
    {
        $mobile = \YunShop::request()->mobile;
        $password = \YunShop::request()->password;
        $uid = \YunShop::app()->getMemberId();
        $type = \YunShop::request()->type;
        $scope = \YunShop::request()->scope;
        $close_invitecode = \YunShop::request()->close;
        $customDatas = \YunShop::request()->customDatas;
        $address = \YunShop::request()->address;
        $birthday = \YunShop::request()->birthday;
        $gender = \YunShop::request()->gender;
        $custom_value = \YunShop::request()->custom_value;
        $mini_first_time_bind = \YunShop::request()->mini_first_time_bind;//是否小程序首次绑定
        if ($birthday) {
            $birthday = explode('-', $birthday);
        }
        if (empty($mobile)) {
            return $this->errorJson('输入手机号码为空');
        }
        $this->relation_base_set = Setting::get('relation_base');
        $member_model = MemberModel::getMemberById($uid);
        if (!empty($member_model->mobile) && $member_model->mobile == $mobile) {
            return $this->successJson('手机号码绑定成功');
        }
        $yz_member = MemberShopInfo::getMemberShopInfo($uid);
        if ($yz_member->is_old) {
            throw new AppException('会员数据有冲突，请联系客服');
        }
        if ($uid > 0) {
            $hasCheckCode = 1;//是否进行校验验证码
            //开启小程序首次登录绑定 && 前端传值目前是授权请求绑定
            if ($type == 2 && (bool)Setting::get('plugin.min_app.is_first_time_bind') == 1 && $mini_first_time_bind == 1) {
                $hasCheckCode = 0;//不校验
            }
            if ($hasCheckCode) {
                $check_code = MemberService::checkCode();
                if ($check_code['status'] != 1) {
                    return $this->errorJson($check_code['json']);
                }
            }
            if ($mini_first_time_bind != 1 && !$close_invitecode) {
                $invite_code = MemberService::inviteCode();
                if ($invite_code['status'] != 1) {
                    return $this->errorJson($invite_code['json']);
                }
                //邀请码
                $parent_id = \app\common\models\Member::getMemberIdForInviteCode();
                if (!is_null($parent_id)) {
                    MemberShopInfo::change_relation($uid, $parent_id);
                    //锁定上线时上级没有奖励积分
                    if ($parent_id != $yz_member['parent_id']) {
                        MemberRelation::rewardPoint($parent_id, $uid);
                    }
                    //增加邀请码使用记录
                    $code_model = new MemberInvitationCodeLog();
                    $code_model->uniacid = \YunShop::app()->uniacid;
                    $code_model->invitation_code = trim(\YunShop::request()->invite_code);
                    $code_model->member_id = $uid; //使用者id
                    $code_model->mid = $parent_id; //邀请人id
                    $code_model->save();
                }
            }
            $register = Setting::get('shop.register');
            if (\YunShop::request()->pc == 1 || (isset($register['is_password']) && $register['is_password'] == 0)) {
                $password = '';
            } else {
                if ($hasCheckCode) {
                    $msg = MemberService::validate($mobile, $password);
                    if ($msg['status'] != 1) {
                        return $this->errorJson($msg['json']);
                    }
                }
            }
            //查询绑定手机号会员
            $member_info_model = MemberModel::getMemberinfo(\YunShop::app()->uniacid, $mobile);
            \Log::debug('------会员设置--保留方式------', $this->relation_base_set);
            \Log::debug(
                '----手机号码绑定-提交的手机号码-当前登录会员信息-手机原始会员信息-type--scope--',
                [$mobile, $member_model, $member_info_model, $type, $scope]
            );
            try {
                if ($member_info_model && (($type == 5 && request()->scope) || $type != 5)) {
                    $merge_choice = $this->relation_base_set['is_merge_save_level'];
                    switch ($merge_choice) {
                        case 1 :
                            //手机号
                            $member_model = $this->phoneMemberSave($uid, $member_info_model, $member_model, $mobile);
                            break;
                        case 2 :
                            //公众号
                            $fans = McMappingFans::getFansById($uid);
                            if ($fans) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            } else {
                                if ($member_info_model->createtime < $member_model->createtime) {
                                    $member_model = $this->phoneMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                } elseif ($member_info_model->createtime > $member_model->createtime) {
                                    $member_model = $this->fansMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                }
                            }
                            break;
                        case 3 :
                            //小程序
                            $mini_fans = MemberMiniAppModel::getFansById($uid);
                            if ($mini_fans) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            } else {
                                if ($member_info_model->createtime < $member_model->createtime) {
                                    $member_model = $this->phoneMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                } elseif ($member_info_model->createtime > $member_model->createtime) {
                                    $member_model = $this->fansMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                }
                            }
                            break;
                        case 4 :
                            //app
                            $app_fans = MemberWechatModel::getFansById($uid);
                            if ($app_fans) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            } else {
                                if ($member_info_model->createtime < $member_model->createtime) {
                                    $member_model = $this->phoneMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                } elseif ($member_info_model->createtime > $member_model->createtime) {
                                    $member_model = $this->fansMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                }
                            }
                            break;
                        case 5 :
                            //alipay
                            $app_fans = MemberAlipay::getFansById($uid);
                            if ($app_fans) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            } else {
                                if ($member_info_model->createtime < $member_model->createtime) {
                                    $member_model = $this->phoneMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                } elseif ($member_info_model->createtime > $member_model->createtime) {
                                    $member_model = $this->fansMemberSave(
                                        $uid,
                                        $member_info_model,
                                        $member_model,
                                        $mobile
                                    );
                                }
                            }
                            break;
                        default :
                            //注册时间
                            if ($member_info_model->createtime < $member_model->createtime) {
                                $member_model = $this->phoneMemberSave(
                                    $uid,
                                    $member_info_model,
                                    $member_model,
                                    $mobile
                                );
                            } elseif ($member_info_model->createtime > $member_model->createtime) {
                                $member_model = $this->fansMemberSave($uid, $member_info_model, $member_model, $mobile);
                            }
                            break;
                    }
                }
            } catch (ShopException $exception) {
                return $this->errorJson($exception->getMessage());
            }
            if (!$member_model) {
                return $this->errorJson('手机号码绑定失败！');
            }
            $salt = Str::random(8);
            $member_model->salt = $salt;
            $member_model->mobile = $mobile;
            $member_model->password = md5($password . $salt);
            $member_model->gender = $gender ?: 0;
            if (request()->input('realname')) {
                $member_model->realname = request()->input('realname');
            }
            $member_model->birthyear = $birthday[0] ?: 0;
            $member_model->birthmonth = $birthday[1] ?: 0;
            $member_model->birthday = $birthday[2] ?: 0;
            $member_shop_info = MemberShopInfo::uniacid()->where('member_id', $member_model->uid)->first();
            $customDatas['customDatas'] = $customDatas;
            //自定义表单
            $member_form = (new MemberService())->updateMemberForm($customDatas);
            if (!empty($member_form)) {
                $member_shop_info->member_form = json_encode($member_form);
            }
            if (!$member_model) {
                return $this->errorJson('手机号码绑定失败！');
            }
            //赋值地址
            if (!empty($address)) {
                $member_shop_info->province = $address['province'] ?: '';
                $member_shop_info->city = $address['city'] ?: '';
                $member_shop_info->area = $address['area'] ?: '';
                $member_shop_info->province_name = $address['province_name'] ?: '';
                $member_shop_info->city_name = $address['city_name'] ?: '';
                $member_shop_info->area_name = $address['area_name'] ?: '';
                $member_shop_info->address = $address['address'] ?: '';
            }
            $member_shop_info->custom_value = $custom_value;
            if ($member_model->save() && $member_shop_info->save()) {
                $member_shop_info->custom_value = $custom_value;
                if ($member_model->save() && $member_shop_info->save()) {
                    if (Cache::has($member_model->uid . '_member_info')) {
                        Cache::forget($member_model->uid . '_member_info');
                    }
                    if (\YunShop::request()->positioning_success == 1) {
                        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('set_location'))) {
                            $class = array_get(
                                \app\common\modules\shop\ShopConfig::current()->get('set_location'),
                                'class'
                            );
                            $function = array_get(
                                \app\common\modules\shop\ShopConfig::current()->get('set_location'),
                                'function'
                            );
                            $class::$function(
                                $member_model->uid,
                                MemberLocation::TYPE_BIND_MOBILE,
                                \YunShop::request()->register_province,
                                \YunShop::request()->register_city
                            );
                        }
                    }
                }
                event(new MemberBindMobile($member_model));
                return $this->successJson('手机号码绑定成功');
            } else {
                return $this->errorJson('手机号码绑定失败');
            }
        } else {
            return $this->errorJson('手机号或密码格式错误');
        }
    }

    private function bindMobileVerify($request)
    {
        $formSet = json_decode(Setting::get('shop.form'), true);
        if ($formSet['base']['basic_register']) {
            //基础信息-注册填写
            if ($formSet['base']['name'] && $formSet['base']['name_must'] && !$request['name']) {
                throw new AppException('请填写姓名');
            }
            if ($formSet['base']['sex'] && $formSet['base']['sex_must'] && !$request['sex']) {
                throw new AppException('请填写性别');
            }
            if ($formSet['base']['address'] && $formSet['base']['address_must'] && !$request['address']) {
                throw new AppException('请填写详细地址');
            }
            if ($formSet['base']['birthday'] && $formSet['base']['birthday_must'] && !$request['birthday']) {
                throw new AppException('请填写生日');
            }
        }
    }

    /**
     * 保留手机号会员数据
     * @param $uid 当前登录会员id
     * @param $member_info_model 当前输入手机号会员model
     * @param $member_model 当前登录会员model
     * @param $mobile 当前输入手机号
     * @return bool|mixed
     */
    public function phoneMemberSave($uid, $member_info_model, $member_model, $mobile)
    {
        $hold_uid = $member_info_model['uid'];
        $give_up_uid = $uid;
        $this->validateYunSignData($give_up_uid);
        $this->validateMerge($mobile);
        //保存修改的信息
        $merge_data = [
            'uniacid'       => \YunShop::app()->uniacid,
            'before_uid'    => $give_up_uid,
            'after_uid'     => $hold_uid,
            'before_mobile' => $member_model->mobile,
            'after_mobile'  => $mobile,
            'before_point'  => $member_model->credit1 ?: 0.00,
            'after_point'   => bcadd($member_info_model->credit1, $member_model->credit1, 2) ?: 0.00,
            'before_amount' => $member_model->credit2 ?: 0.00,
            'after_amount'  => bcadd($member_info_model->credit2, $member_model->credit2, 2) ?: 0.00,
            'set_content'   => json_encode($this->relation_base_set),
            'merge_type'    => 1,
        ];
        //保存新积分、余额、token
        $member_info_model->nickname = $member_model->nickname;
        $member_info_model->avatar = $member_model->avatar;
        //删除会员
        $yz_member = MemberShopInfo::getMemberShopInfo($give_up_uid);
        $exception = DB::transaction(
            function () use ($give_up_uid, $hold_uid, $member_info_model, $merge_data, $yz_member) {
                \app\backend\modules\member\models\MemberShopInfo::deleteMemberInfo($give_up_uid);
                \app\common\models\Member::where('uid', $give_up_uid)->delete();
                //公众号
                McMappingFans::where('uid', $give_up_uid)->update(['uid' => $hold_uid]);
                //小程序
                MemberMiniAppModel::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
                //app
                MemberWechatModel::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
                //聚合cps
                if (Schema::hasTable('yz_member_aggregation_app')) {
                    DB::table('yz_member_aggregation_app')->where('member_id', $give_up_uid)->update(
                        ['member_id' => $hold_uid]
                    );
                }
                //企业微信
                if (Schema::hasTable('yz_member_customer')) {
                    DB::table('yz_member_customer')->where('uid', $give_up_uid)->update(['uid' => $hold_uid]);
                }
                //支付宝
                \app\common\models\MemberAlipay::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
                //统一
                MemberUniqueModel::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
                //合并处理服务
                (new MemberMergeService($hold_uid, $give_up_uid, $merge_data))->handel();
                $member_info_model->save();
                \app\backend\modules\member\models\MemberShopInfo::where('member_id', $hold_uid)->update([
                    'access_token_1'       => $yz_member->access_token_1,
                    'access_expires_in_1'  => $yz_member->access_expires_in_1,
                    'refresh_token_1'      => $yz_member->refresh_token_1,
                    'refresh_expires_in_1' => $yz_member->refresh_expires_in_1,
                    'access_token_2'       => $yz_member->access_token_2,
                    'access_expires_in_2'  => $yz_member->access_expires_in_2,
                    'refresh_token_2'      => $yz_member->refresh_token_2,
                    'refresh_expires_in_2' => $yz_member->refresh_expires_in_2,
                ]);
            }
        );
        if (!is_null($exception)) {
            return false;
        }
        event(new MergeMemberEvent($hold_uid, $give_up_uid));
        //查出要保留的会员信息
        $member_model = MemberModel::getMemberById($hold_uid);
        Session::set('member_id', $hold_uid);
        return $member_model;
    }

    /**
     * 保留粉丝会员数据
     * @param $uid 当前登录会员id
     * @param $member_info_model 输入手机号会员model
     * @param $member_model 当前登录会员model
     * @param $mobile 当前输入手机号
     * @return bool|mixed
     */
    public function fansMemberSave($uid, $member_info_model, $member_model, $mobile)
    {
        $hold_uid = $uid;
        $give_up_uid = $member_info_model['uid'];
        $this->validateYunSignData($give_up_uid);
        $this->validateMerge($mobile);
        $merge_data = [
            'uniacid'       => \YunShop::app()->uniacid,
            'before_uid'    => $give_up_uid,
            'after_uid'     => $hold_uid,
            'before_mobile' => $member_info_model->mobile,
            'after_mobile'  => $mobile,
            'before_point'  => $member_info_model->credit1 ?: 0.00,
            'after_point'   => bcadd($member_info_model->credit1, $member_model->credit1, 2) ?: 0.00,
            'before_amount' => $member_info_model->credit2 ?: 0.00,
            'after_amount'  => bcadd($member_info_model->credit2, $member_model->credit2, 2) ?: 0.00,
            'set_content'   => json_encode($this->relation_base_set),
            'merge_type'    => 1,
        ];
        //删除会员
        $exception = DB::transaction(function () use ($give_up_uid, $hold_uid, $member_model, $merge_data) {
            \app\backend\modules\member\models\MemberShopInfo::deleteMemberInfo($give_up_uid);
            \app\common\models\Member::where('uid', $give_up_uid)->delete();
            //公众号
            McMappingFans::where('uid', $give_up_uid)->update(['uid' => $hold_uid]);
            //小程序
            MemberMiniAppModel::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
            //app
            MemberWechatModel::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
            //聚合cps
            if (Schema::hasTable('yz_member_aggregation_app')) {
                DB::table('yz_member_aggregation_app')->where('member_id', $give_up_uid)->update(
                    ['member_id' => $hold_uid]
                );
            }
            //企业微信
            if (Schema::hasTable('yz_member_customer')) {
                DB::table('yz_member_customer')->where('uid', $give_up_uid)->update(['uid' => $hold_uid]);
            }
            //支付宝
            \app\common\models\MemberAlipay::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
            //统一
            MemberUniqueModel::where('member_id', $give_up_uid)->update(['member_id' => $hold_uid]);
            //合并处理服务
            (new MemberMergeService($hold_uid, $give_up_uid, $merge_data))->handel();
            $member_model->save();
        });
        if (!is_null($exception)) {
            return false;
        }
        event(new MergeMemberEvent($hold_uid, $give_up_uid));
        //查出要保留的会员信息
        $member_model = MemberModel::getMemberById($hold_uid);
        Session::set('member_id', $hold_uid);
        return $member_model;
    }

    private function validateMerge($mobile)
    {
        $type = request()->type;
        $member_info = MemberModel::getId(\YunShop::app()->uniacid, $mobile);
        $unique_info = MemberUniqueModel::getUnionidInfoByMemberId(
            \YunShop::app()->uniacid,
            $member_info['uid']
        )->first();
        $fans_info = McMappingFans::getFansById($member_info['uid']);
        $mini_info = MemberMiniAppModel::getFansById($member_info['uid']);
        $wechat_info = MemberWechatModel::getFansById($member_info['uid']);
        $ali_info = MemberAlipay::getFansById($member_info['uid']);
        if ($type != 8 && ($unique_info || $fans_info || $mini_info || $wechat_info)) {
            throw new ShopException('该手机号已被绑定，不能重复绑定');
        }
        if ($type == 8 && $ali_info) {
            throw new ShopException('该手机号已被绑定，不能重复绑定');
        }
        if ($type == 5 && !request()->scope && $member_info) { //request()->scope tjpcps
            throw new ShopException('该手机号已被绑定，不能重复绑定');
        }
    }

    /**
     * 验证是否有芸签数据(有则不能合并)
     * @param $give_up_uid 放弃的会员id
     * @return bool true 有 false 无
     */
    private function validateYunSignData($give_up_uid)
    {
        if (app('plugins')->isEnabled('yun-sign')) {
            $person = PersonAccount::uniacid()->where(['uid' => $give_up_uid, 'status' => 1])->first();
            if ($person) {
                throw new ShopException('合并会员有个人认证数据，不能合并');
            }
            $contract_num = ContractNum::uniacid()->where('uid', $give_up_uid)->where('rest_num', '>', 0)->first();
            if ($contract_num) {
                throw new ShopException('合并会员有合同数量，不能合并');
            }
            $not_sign_contract = Contract::uniacid()->where('status', '<', 2)->whereHas(
                'hasManyRole',
                function ($q) use ($give_up_uid) {
                    $q->where(['uid' => $give_up_uid, 'status' => 0]);
                }
            )->first();
            if ($not_sign_contract) {
                throw new ShopException('合并会员有未签署合同，不能合并');
            }
        }
        if (app('plugins')->isEnabled('shop-esign')) {
            $shop_person = \Yunshop\ShopEsign\common\models\PersonAccount::uniacid()->where(
                ['uid' => $give_up_uid, 'status' => 1]
            )->first();
            if ($shop_person) {
                throw new ShopException('合并会员有商城电子合同个人认证数据，不能合并');
            }
            $shop_not_sign_contract = \Yunshop\ShopEsign\common\models\Contract::uniacid()->where(
                'uid',
                $give_up_uid
            )->where('status', '<', 2)->first();
            if ($shop_not_sign_contract) {
                throw new ShopException('合并会员有商城电子合同未签署合同，不能合并');
            }
        }

        return false;
    }

    //会员信息同步
    public function synchro($new_member, $old_member)
    {
        $member_merge_set = Setting::get('relation_base');

        $type = \YunShop::request()->type;

        \Log::debug('会员同步type:' . $type);
        $type = empty($type) ? Client::getType() : $type;

        $className = SynchronousUserInfo::create($type);

        if ($className) {
            if (empty($member_merge_set['is_merge_save_level']) || $member_merge_set['is_merge_save_level'] === 1) {
                return $className->updateMember($old_member, $new_member);
            } else {
                return $className->updateMemberOther($old_member, $new_member);
            }
        } else {
            return false;
        }
    }

    /**
     * 绑定提现手机号
     *
     */
    public function bindWithdrawMobile()
    {
        $mobile = \YunShop::request()->mobile;

        $member_model = MemberShopInfo::getMemberShopInfo(\YunShop::app()->getMemberId());

        if (\YunShop::app()->getMemberId() && \YunShop::app()->getMemberId() > 0) {
            $check_code = MemberService::checkCode();

            if ($check_code['status'] != 1) {
                return $this->errorJson($check_code['json']);
            }

            $salt = Str::random(8);
            $member_model->withdraw_mobile = $mobile;

            if ($member_model->save()) {
                return $this->successJson('手机号码绑定成功');
            } else {
                return $this->errorJson('手机号码绑定失败');
            }
        } else {
            return $this->errorJson('手机号或密码格式错误');
        }
    }

    /**
     * @name 微信JSSDKConfig
     * @param int $goods_id
     *
     * @return \Illuminate\Http\JsonResponse
     * @author
     *
     */
    public function wxJsSdkConfig()
    {
        $member = \Setting::get('shop.member');
        $info = [];
        $config = [];

        $is_true = false;
        if (!$member['wechat_login_mode']) {
            if (config('app.framework') == 'platform') {
                if (app('plugins')->isEnabled('wechat')) {
                    $is_true = true;
                }
            } else {
                $is_true = true;
            }
        }
        if ($is_true) {
            $url = \YunShop::request()->url ?: '';

            $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
            $app = EasyWeChat::officialAccount([
                'app_id' => $account->key,
                'secret' => $account->secret
            ]);

            $js = $app->jssdk;
            $js->setUrl($url);

            $config = $js->buildConfig(array(
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'showOptionMenu',
                'scanQRCode',
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'startRecord',
                'stopRecord',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'uploadVoice',
                'downloadVoice',
                'hideMenuItems',
                'chooseImage',
                'getLocalImgData',
                'translateVoice',
                'agentConfig'
            ));
            $config = json_decode($config, 1);
        }

        if (\YunShop::app()->getMemberId()) {
            $info = Member::select('uid', 'uniacid', 'mobile', 'nickname', 'avatar')->where(
                'uid',
                \YunShop::app()->getMemberId()
            )->first();
            if (!empty($info)) {
                $info = $info->toArray();
            }
        }

        $share = \Setting::get('shop.share');

        if ($share) {
            if ($share['icon']) {
                $share['icon'] = replace_yunshop(yz_tomedia($share['icon']));
            }
            $share = [
                'title' => $share['title'] ?: '',
                'icon'  => $share['icon'] ?: '',
                'desc'  => $share['desc'] ?: '',
                'url'   => $share['url'] ?: ''
            ];
        }

        $shop = \Setting::get('shop');
        $shop['icon'] = replace_yunshop(yz_tomedia($shop['logo']));
        $shop['share']['icon'] = yz_tomedia($shop['share']['icon']);
        //精简数据 优化
        foreach ($shop as $k => $v) {
            if (!in_array($k, ['shop', 'share', 'icon'])) {
                unset($shop[$k]);
            } elseif ($k == 'shop') {
                $shop[$k] = [
                    "name"      => $v['name'] ?: '',
                    "logo"      => $v['logo'] ?: '',
                    "logo_url"  => $v['logo_url'] ?: '',
                    "copyright" => $v['copyright'] ?: '',
                    "cservice"  => $v['cservice'] ?: '',
                ];
            }
        }
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('customer_service'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_service'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_service'), 'function');
            $ret = $class::$function(request()->goods_id, request()->type);
            if ($ret) {
                if (is_array($ret)) {
                    foreach ($ret as $rk => $rv) {
                        $shop[$rk] = $rv;
                    }
                } else {
                    $shop['cservice'] = $ret;
                }
            }
        }
        if (is_null($share) && is_null($shop)) {
            $share = [
                'title' => '商家分享',
                'icon'  => '#',
                'desc'  => '商家分享'
            ];
        }

        if (app('plugins')->isEnabled('designer')) {
            $index = (new RecordsController())->shareIndex();
            foreach ($index['data'] as $value) {
                foreach ($value['page_type_cast'] as $item) {
                    if ($item == 1) {
                        $designer = json_decode(htmlspecialchars_decode($value['page_info']))[0]->params;
                        if (!empty($designer->title) || !empty($designer->img) || !empty($designer->desc)) {
                            $share['title'] = $designer->title;
                            $share['icon'] = $designer->img;
                            $share['desc'] = $designer->desc;
                        }
                        break;
                    }
                }
            }
        }

        $data = [
            'config' => $config,
            'info'   => $info,   //商城设置
            'shop'   => $shop,
            'share'  => $share,   //分享设置
        ];

        return $this->successJson('', $data);
    }

    public function designer(Request $request, $integrated = null, $pageID = '')
    {
        $TemId = $pageID ?: \Yunshop::request()->id;
        if ($TemId) {
            $designerModel = Designer::getDesignerByPageID($TemId);
            if ($designerModel) {
//                $designerSet = json_decode(htmlspecialchars_decode($designerModel->page_info));
//                foreach ($designerSet->toArray as &$set){
//                    if (isset($set['temp']) && $set['temp'] == 'topbar'){
//                        if (!empty($set['params']['title'])){
//                            $shop = Setting::get('shop.shop');
//                            $set['params']['title'] = $shop['name'];
//                            $set['params']['img'] = $shop['logo'];
//                        }
//                    }
//                }
                $designerSet = json_decode(htmlspecialchars_decode($designerModel->page_info));
                if ($designerSet[0]->temp == 'topbar') {
                    $share = Setting::get('shop.share');
                    $designer['title'] = $designerSet[0]->params->title ?: $share['title'];
                    $designer['img'] = $designerSet[0]->params->img ?: $share['icon'];
                    $designer['desc'] = $designerSet[0]->params->desc ?: $share['desc'];
                }
                if (is_null($integrated)) {
                    return $this->successJson('获取数据成功!', $designer);
                } else {
                    return show_json(1, $designer);
                }
            }
        }
        if (is_null($integrated)) {
            return $this->successJson('参数有误!', []);
        } else {
            return show_json(1, '');
        }
    }

    /**
     * 申请协议
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyProtocol()
    {
        $protocol = Setting::get('apply_protocol');

        if ($protocol) {
            return $this->successJson('获取数据成功!', $protocol);
        }
        return $this->successJson('未检测到数据!', []);
    }

    /**
     * 推广基本设置
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function AgentBase()
    {
        $info = \Setting::get('relation_base');

        if ($info) {
            return $this->successJson('', [
                'banner' => replace_yunshop(yz_tomedia($info['banner']))
            ]);
        }

        return $this->errorJson('暂无数据', []);
    }

    public function guideFollow(Request $request, $integrated = null)
    {
        $member_id = \YunShop::app()->getMemberId();

        if (empty($member_id)) {
            if (is_null($integrated)) {
                return $this->errorJson('用户未登录', []);
            } else {
                return show_json(0, '用户未登录');
            }
        }
        if ($request->type == 1) {
            $set = \Setting::get('shop.share');
            $fans_model = McMappingFans::getFansById($member_id);
            $mid = \app\common\models\Member::getMid();


            if ($set['follow'] == 1 && $fans_model->follow === 0) {
                if ($mid != null && $mid != 'undefined' && $mid > 0) {
                    $member_model = Member::getMemberById($mid);

                    $logo = $member_model->avatar;
                    $text = $member_model->nickname;
                } else {
                    $setting = Setting::get('shop');
                    $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);

                    $logo = replace_yunshop(yz_tomedia($setting['shop']['logo']));
                    $text = $account->name;
                }
                if (is_null($integrated)) {
                    return $this->successJson('', [
                        'logo'       => $logo,
                        'text'       => $text,
                        'url'        => $set['follow_url'],
                        'follow_img' => replace_yunshop(yz_tomedia($set['follow_img'])),
                        'type'       => isset($set['type']) ? $set['type'] : 1,
                    ]);
                } else {
                    return show_json(1, [
                        'logo'       => $logo,
                        'text'       => $text,
                        'url'        => $set['follow_url'],
                        'follow_img' => replace_yunshop(yz_tomedia($set['follow_img'])),
                        'type'       => isset($set['type']) ? $set['type'] : 1, //优化加上链接，图片(0是图片)
                    ]);
                }
            }
        }
        if (is_null($integrated)) {
            return $this->errorJson('暂无数据', []);
        } else {
            return show_json(0, '暂无数据');
        }
    }

    //会员广告(涉及小程序第一次登陆)
    public function getAdvertisement(Request $request, $integrated = null)
    {
        $advertisement_data = \Setting::get('designer.first-screen');

        $type = $request ? $request->type : \YunShop::request()->type;
        //empty( Cookie::get('memberlogin_status')) && 去除登录缓存验证，前度做处理
        if (($advertisement_data['switch'] || $advertisement_data['Midswitch']) &&
            ($advertisement_data['rule'] == 0 || $advertisement_data['Midrule'] == 0) &&
            $type != 2) {
            if ($advertisement_data['type'] == 0) {
                unset($advertisement_data['link'], $advertisement_data['prolink']);
            }
            setcookie('memberlogin_status', '1');

            if (is_null($integrated)) {
                return $this->successJson('', [
                    'advertisement' => $advertisement_data,
                ]);
            } else {
                return show_json(1, [
                    'advertisement' => $advertisement_data,
                ]);
            }
        }

        if (($advertisement_data['switch'] || $advertisement_data['Midswitch']) && $type == 2) {
            if (!$this->firstLogin()) {
                if (is_null($integrated)) {
                    return $this->errorJson('暂无信息');
                } else {
                    return show_json(1, '暂无信息');
                }
            }

            //if ($advertisement_data['type'] == 1) {
            //unset($advertisement_data['time']);
            if ($advertisement_data['rule'] == 1) {
                unset($advertisement_data['link']);
            }
            //}
            if (is_null($integrated)) {
                return $this->successJson('ok', [
                    'advertisement' => $advertisement_data,
                ]);
            } else {
                return show_json(1, [
                    'advertisement' => $advertisement_data,
                ]);
            }
        }

        if (is_null($integrated)) {
            return $this->errorJson('暂无信息');
        } else {
            return show_json(1, '暂无信息');
        }
    }

    //

    /**
     * 小程序第一次登录
     * @param $name 避免有插件或其他类型获取加多一个类型
     */
    private function firstLogin($name = '')
    {
        //0点时间戳
        $start = strtotime(date("Y-m-d"), time());
        $end = $start + 60 * 60 * 24;
        $member_id = \YunShop::app()->getMemberId();
        $member_first_login = Cache::get($member_id . 'first_login' . $name);


        if ($member_first_login) {
            $data = explode('#', $member_first_login);
            $datatime = $data[1];
        }

        if (!$member_first_login || $datatime >= $end || $datatime < $start) {
            //小程序今天第一次登录
            Cache::put($member_id . 'first_login' . $name, $member_id . '#' . time(), 1440);
            return true;
        }
        return false;
    }

    /**
     * 装修2.0 小程序是否首次登录
     */
    public function getFirstLogin($name = '')
    {
        $member_id = \YunShop::app()->getMemberId();
        $type = $request ? $request->type : \YunShop::request()->type;
        if ($member_id && $type == 2) {
            return $this->firstLogin($name);
        }
        return false;
    }

    public function memberInfo()
    {
        $member_id = request()->input('uid');
        if (empty($member_id)) {
            return $this->errorJson('会员不存在');
        }
        $member_info = MemberModel::select(['nickname', 'avatar', 'realname'])->uniacid()->where(
            'uid',
            $member_id
        )->first();
        if (empty($member_info)) {
            return $this->errorJson('会员不存在');
        }
        return $this->successJson('', $member_info);
    }

    public function forget()
    {
        Session::clear('member_id');

        redirect(Url::absoluteApp('home'))->send();
    }

    public function memberFromHXQModule()
    {
        $uniacid = \YunShop::app()->uniacid;
        $member_id = \YunShop::request()->uid;

        if (!empty($member_id)) {
            $member_shop_info_model = MemberShopInfo::getMemberShopInfo($member_id);

            if (is_null($member_shop_info_model)) {
                (new MemberService)->addSubMemberInfo($uniacid, (int)$member_id);
            }

            $mid = \YunShop::request()->mid ?: 0;

            Member::createRealtion($member_id, $mid);

            \Log::debug('------HXQModule---------' . $member_id);
            \Log::debug('------HXQModule---------' . $mid);

            return $this->successJson('ok');
        }

        return $this->errorJson('uid为空');
    }

    /**
     * 同步模块支付宝用户
     * @return string
     */
    public function dsAlipayUserModule()
    {
        $uniacid = \YunShop::app()->uniacid;
        $member_id = \YunShop::request()->uid;
        $userInfo = \YunShop::request()->user_info;

        if (!is_array($userInfo)) {
            $userInfo = json_decode($userInfo, true);
        }

        if (!empty($member_id)) {
            if (app('plugins')->isEnabled('alipay-onekey-login') && $userInfo) {
                $bool = MemberAlipay::insertData($userInfo, ['member_id' => $member_id, 'uniacid' => $uniacid]);
                if (!$bool) {
                    return json_encode(['status' => 0, 'result' => '支付宝用户信息保存失败']);
                }
            } else {
                return json_encode(['status' => 0, 'result' => '未开启插件或未接受到支付宝用户信息']);
            }

            $member_shop_info_model = MemberShopInfo::getMemberShopInfo($member_id);

            if (is_null($member_shop_info_model)) {
                (new MemberService)->addSubMemberInfo($uniacid, (int)$member_id);
            }

            $mid = \YunShop::request()->mid ?: 0;

            Member::createRealtion($member_id, $mid);

            \Log::debug('------HXQModule---------' . $member_id);
            \Log::debug('------HXQModule---------' . $mid);

            return json_encode(['status' => 1, 'result' => 'ok']);
        }

        return json_encode(['status' => 0, 'result' => 'uid为空']);
    }


    public function getCustomField(Request $request, $integrated = null)
    {
        // member.member.get-custom-field
        $member = Setting::get('shop.member');
        $data = [
            'is_custom'    => $member['is_custom'],
            'custom_title' => $member['custom_title'],
            'is_validity'  => $member['level_type'] == 2 ? true : false,
            'term'         => $member['term'] ? $member['term'] : 0,
        ];

        if (is_null($integrated)) {
            return $this->successJson('获取自定义字段成功！', $data);
        } else {
            return show_json(1, $data);
        }
    }

    public function saveCustomField()
    {
        // member.member.sava-custom-field
        $member_id = \YunShop::app()->getMemberId();
        $custom_value = \YunShop::request()->get('custom_value');

        $data = [
            'custom_value' => $custom_value,
        ];
        $request = MemberShopInfo::where('member_id', $member_id)->update($data);
        if ($request) {
            return $this->successJson('保存成功！', []);
        }
        return $this->successJson('保存失败！', []);
    }

    public function withdrawByMobile()
    {
        $trade = \Setting::get('shop.trade');

        if ($trade['is_bind'] && \YunShop::app()->getMemberId() && \YunShop::app()->getMemberId() > 0) {
            $member_model = MemberShopInfo::getMemberShopInfo(\YunShop::app()->getMemberId());

            if ($member_model && $member_model->withdraw_mobile) {
                $is_bind_mobile = 0;
            } else {
                $is_bind_mobile = 1;
            }
        } else {
            $is_bind_mobile = 0;
        }

        return $this->successJson('', ['is_bind_mobile' => $is_bind_mobile]);
    }

    /**
     * 修复关系链
     *
     * 历史遗留问题
     */
    public function fixRelation()
    {
        set_time_limit(0);
        //获取修改数据
        $members = MemberShopInfo::uniacid()
            ->where('parent_id', '!=', 0)
            ->where('is_agent', 1)
            ->where('status', 2)
            ->where('relation', '')
            ->orWhereNull('relation')
            ->orWhere('relation', '0,')
            ->whereNull('deleted_at')
            ->get();

        if (!$members->isEmpty()) {
            foreach ($members as $member) {
                //yz_members
                if ($member->is_agent == 1 && $member->status == 2) {
                    Member::setMemberRelation($member->member_id, $member->parent_id);
                }
            }
        }

        echo 'yz_member修复完毕<BR>';

        //yz_agents
        //获取修改数据
        $agents = Agents::uniacid()
            ->where('parent_id', '!=', 0)
            ->whereNull('deleted_at')
            ->where('parent', '')
            ->orWhereNull('parent')
            ->orWhere('parent', '0,')
            ->get();

        foreach ($agents as $agent) {
            $rows = DB::table('yz_member')
                ->select()
                ->where('uniacid', $agent->uniacid)
                ->where('member_id', $agent->member_id)
                ->where('parent_id', $agent->parent_id)
                ->where('is_agent', 1)
                ->where('status', 2)
                ->whereNull('deleted_at')
                ->first();

            if (!empty($rows)) {
                $agent->parent = $rows['relation'];

                $agent->save();
            }
        }

        echo 'yz_agents修复完毕';
    }

    public function memberRelationFilter()
    {
        $data = MemberModel::filterMemberRoleAndLevel();

        return $this->successJson('', $data);
    }

    public function isOpenRelation(Request $request, $integrated = null)
    {
        //是否显示我的推广 和 withdraw_status是否显示提现
        $switch = PortType::popularizeShow(\YunShop::request()->type);

        $data = [
            'switch' => $switch
        ];

        if (is_null($integrated)) {
            return $this->successJson('', $data);
        } else {
            return show_json(1, $data);
        }
    }

    public function anotherShare()
    {
        $order_ids = \YunShop::request()->order_ids;
        $mid = \YunShop::app()->getMemberId();
        if (empty($order_ids)) {
            return $this->errorJson('参数错误', '');
        }
        if (empty($mid)) {
            return $this->errorJson('用户未登陆', '');
        }
        $title = Setting::get('shop.pay.another_share_title');
        $another_share_type = Setting::get('shop.pay.another_share_type') == 2 ? 2 : 1;
        $url = yzAppFullUrl(
            '/member/payanotherdetail',
            ['pid' => $mid, 'order_ids' => $order_ids, 'share_type' => $another_share_type]
        );
        $order_goods = Order::find($order_ids)->hasManyOrderGoods;
        if (is_null($order_goods)) {
            return $this->errorJson('订单商品不存在', '');
        }
        if (empty($title)) {
            $title = '土豪大大，跪求代付';
        }
        if (request()->type == 2) {
            $file_name = 'another_oid_' . $order_ids . '_mid_' . $mid;
            $page = 'packageD/buy/payanotherDetail/payanotherDetail';//todo 需要更换
            $scene = 'id=' . $order_ids . ',p=' . $mid . ',t=' . $another_share_type;
            $dir = 'storage/app/public/mini-qr/another-pay/' . \YunShop::app()->uniacid; //商城根目录下
            try {
                $mini_code_helper = new MiniCodeHelper($dir, $file_name, $page, $scene, 300);
                $code_url = $mini_code_helper->url();
            } catch (ShopException $exception) {
                $code_url = '';
            }
        } else {
            $h5_code = new \app\common\helpers\QrCodeHelper($url, 'app/public/qr/another-pay');
            $code_url = $h5_code->url();
        }
        $data = [
            'title'    => $title,
            'url'      => $url,
            'code_url' => $code_url,
            'content'  => $order_goods[0]->title,
            'img'      => replace_yunshop(yz_tomedia($order_goods[0]->thumb))
        ];
        return $this->successJson('', $data);
    }

    public function getEnablePlugins(Request $request, $integrated = null)
    {
        $memberId = \YunShop::app()->getMemberId();
        $arr = (new MemberCenterService())->getMemberData($memberId);//获取会员中心页面各入口

        if (is_null($integrated)) {
            return $this->successJson('ok', $arr);
        } else {
            return show_json(1, $arr);
        }
    }

    public function isOpenHuanxun()
    {
        $huanxun = \Setting::get('plugin.huanxun_set');

        if (app('plugins')->isEnabled('huanxun')) {
            if ($huanxun['withdrawals_switch']) {
                return $this->successJson('', $huanxun['withdrawals_switch']);
            }
        }
        return $this->errorJson('', 0);
    }

    /**
     *  推广申请页面数据
     */
    public function shareinfo()
    {
        $data = MemberRelation::uniacid()->where(['status' => 1])->get();

        $become_term = unserialize($data[0]['become_term']);

        $goodsid = explode(',', $data[0]['become_goods_id']);

        foreach ($goodsid as $key => $val) {
            $online_good = Goods::where('status', 1)
                ->select('id', 'title', 'thumb', 'price', 'market_price')
                ->find($val);

            if ($online_good) {
                $online_good['thumb'] = replace_yunshop(yz_tomedia($online_good['thumb']));
                $online_goods[] = $online_good;
                $online_goods_keys[] = $online_good->id;
            }
        }
        unset($online_good);

        $goodskeys = range(0, count($online_goods_keys) - 1);

        $data[0]['become_goods'] = array_combine($goodskeys, $online_goods);

        $termskeys = range(0, count($become_term) - 1);
        $become_term = array_combine($termskeys, $become_term);
        $member_uid = \YunShop::app()->getMemberId();

        $status = $data[0]['become_order'] == 1 ? 3 : 1;
        $terminfo = [];

        foreach ($become_term as $v) {
            if ($v == 2) {
                $terminfo['become_ordercount'] = $data[0]['become_ordercount'];
            }
            if ($v == 3) {
                $terminfo['become_moneycount'] = $data[0]['become_moneycount'];
            }
            if ($v == 4) {
                $terminfo['goodsinfo'] = $data[0]['become_goods'];
            }
            if ($v == 5) {
                $terminfo['become_selfmoney'] = $data[0]['become_selfmoney'];
            }
        }

        $data[0]['become_term'] = $terminfo;
        if ($data[0]['become'] == 2) {
            //或
            $data[0]['tip'] = '满足以下任意条件都可以成为推广员';
        } elseif ($data[0]['become'] == 3) {
            //与
            $data[0]['tip'] = '满足以下所有条件才可以成为推广员';
        }

        $data[0]['getCostTotalNum'] = Order::where('status', '>=', $status)->where('uid', $member_uid)->count('id');
        $data[0]['getCostTotalPrice'] = Order::where('status', '>=', $status)->where('uid', $member_uid)->sum('price');
        if (app('plugins')->isEnabled('sales-commission')) {
            $data[0]['getSelfMoney'] = \Yunshop\SalesCommission\models\SalesCommission::sumDividendAmountByUid(
                $member_uid
            );
        } else {
            $data[0]['getSelfMoney'] = 0;
            if (in_array(5, $become_term)) {
                foreach ($become_term as $k => $v) {
                    if ($v == 5) {
                        unset($become_term[$k]);
                    }
                }
                $become_term = array_values($become_term);
            }
        }

        $data[0]['become_term_id'] = $become_term;
        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
        $mid = \app\common\models\Member::getMid();

        $m_member = MemberShopInfo::where('member_id', \YunShop::app()->getMemberId())->first();

        if ($m_member->parent_id != $mid && $m_member->parent_id != 0) {
            $mid = $m_member->parent_id ?: 0;
        }

        $parent_name = '';

        if (empty($mid)) {
            $parent_name = '总店';
        } else {
            $parent_model = MemberModel::getMemberById($mid);

            if (!empty($parent_model)) {
                $parent_member = $parent_model->toArray();
                $status = '';
                if ($parent_model['inviter']) {
                    $status = '(暂定)';
                }
                $parent_name = $parent_member['realname'] ?: $status . $parent_member['nickname'];
            }
        }

        if (!empty($member_model = MemberModel::getMemberById(\YunShop::app()->getMemberId()))) {
            $member = $member_model->toArray();
        }
        $member_info = SubMemberModel::getMemberShopInfo(\YunShop::app()->getMemberId());
        $data[0]['base_info'] = [
            'shop_name'   => $account['name'],
            'parent_name' => $parent_name,
            'realname'    => $member['realname'],
            'mobile'      => $member['mobile'],
            'status'      => $member_info->status
        ];
        return $this->successJson('ok', $data[0]);
    }

    /**
     *  邀请页面验证
     */
    public function memberInviteValidate()
    {
        $invite_code = request()->invite_code;
        $parent = (new MemberShopInfo())->getInviteCodeMember($invite_code);
        if ($parent) {
            \Log::info('更新上级------' . \YunShop::app()->getMemberId());
            MemberShopInfo::change_relation(\YunShop::app()->getMemberId(), $parent->member_id);
            //增加邀请码使用记录
            $model = new MemberInvitationCodeLog();
            $model->uniacid = \YunShop::app()->uniacid;
            $model->mid = $parent->member_id; //邀请用户
            $model->member_id = \YunShop::app()->getMemberId(); //使用用户
            $model->invitation_code = $invite_code;
            $model->save();
            return $this->successJson('ok', $parent);
        } else {
            return $this->errorJson('邀请码有误!请重新填写');
        }
    }

    /**
     * 邀请页面确认上级
     */
    public function updateMemberInvite()
    {
        $parent_id = (integer)request()->parent_id ?: 0;
        $invitation_code = '';
        if ($parent_id) {
            $invitation_code = MemberShopInfo::where('member_id', $parent_id)->first()->invite_code ?: '';
        }
        $model = new MemberInvitationCodeLog();
        $model->uniacid = \YunShop::app()->uniacid;
        $model->member_id = \YunShop::app()->getMemberId(); //使用用户
        $model->mid = $parent_id; //邀请用户
        $model->invitation_code = $invitation_code;
        $model->save();
        return $this->successJson('成功');
    }

    public function isValidatePage(Request $request, $integrated = null)
    {
        $member_id = \YunShop::app()->getMemberId();
        $invite_page = 0;
        $addressClass = \Setting::get(
            'shop.trade.is_street'
        ) ? '\app\common\models\YzMemberAddress' : '\app\common\models\MemberAddress';
        $data = [
            'is_bind_mobile' => 0,
            'invite_page'    => 0,
            'is_invite'      => 0,
            'is_login'       => 0,
            'invite_mobile'  => MemberModel::getMobile($member_id) ? 1 : 0, // 是否已绑定手机号
            'bind_address'   => [
                'is_bind_address'   => $addressClass::uniacid()->where('uid', \YunShop::app()->getMemberId())->where(
                    'isdefault',
                    1
                )->first() ? 1 : 0,
                'bind_address_type' => 0,
                'bind_address_page' => [],
            ],
            'need_video' => 0
        ];
        if (app('plugins')->isEnabled('real-name-auth')) {
            $real_name_auth_set = RealNameAuthSet::uniacid()->first();
            if ($member_id) {
                $real_name_auth = RealNameAuth::getInfoByUid($member_id);
                $data['is_auth'] = 0;
                if ($real_name_auth->auth_status) {
                    $data['is_auth'] = 1;
                }
            }
            $is_open = 0;
            if (!$real_name_auth_set || $real_name_auth_set->status) {
                $is_open = 1;
            }
            $data['real_name_auth_scene'] = $real_name_auth_set->auth_scene ?: [];
            $data['real_name_auth_is_open'] = $is_open;
        }
        //爱心值加速池-钱包地址（钱包登录前端验证有用到）
        if (app('plugins')->isEnabled('love-speed-pool')) {
            $data['lsp_wallet_site'] = \Yunshop\LoveSpeedPool\model\WalletSite::getWalletSite(
                \YunShop::app()->getMemberId()
            ) ?: '';
        }

        //爆客码-爆客场景
        if(app('plugins')->isEnabled('drainage-code') && app('plugins')->isEnabled('wechat-customers')){
            $data['drainage_code']['drainage_scene'] = SceneService::getCacheScene();
            $data['drainage_code']['has_add_employee'] = SceneService::hasAddEmployee();
        }

        //强制绑定手机号
        if (Cache::has('shop_member')) {
            $member_set = Cache::get('shop_member');
        } else {
            $member_set = \Setting::get('shop.member');
        }

        if (!is_null($member_set)) {
            $data['is_bind_mobile'] = $this->isBindMobile($member_set, $member_id);
            $data['bind_mobile_page'] = $member_set['bind_mobile_page'] ?: [];
            $invite_page = $member_set['invite_page'] ? 1 : 0;
            $data['bind_address']['bind_address_type'] = intval($member_set['is_bind_address']) ?: 0;
            $data['bind_address']['bind_address_page'] = $member_set['bind_address_page'] ?: [];
        }


        if ($data['is_bind_mobile']) {
            if (is_null($integrated)) {
                return $this->successJson('强制绑定手机开启', $data);
            } else {
                return show_json(1, $data);
            }
        }

        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('get_video_access'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('get_video_access'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('get_video_access'), 'function');
            $judge_res = $class::$function($member_id);
            if (!$judge_res) {
                $data['need_video'] = 1;
            }
        }

        $type = \YunShop::request()->type;
        $invitation_log = [];
        if ($member_id) {
            $mobile = \app\common\models\Member::where('uid', $member_id)->first();
            if ($mobile->mobile) {
                $invitation_log = 1;
            } else {
                $member = MemberShopInfo::uniacid()->where('member_id', $member_id)->first();
                $invitation_log = MemberInvitationCodeLog::uniacid()->where('member_id', $member_id)->where(
                    'mid',
                    $member->parent_id
                )->first();
            }
        }

        $data['invite_page'] = $type == 5 ? 0 : $invite_page;

        $data['is_invite'] = $invitation_log ? 1 : 0;
        $data['is_login'] = $member_id ? 1 : 0;

        /**
         * 新客下单插件，是否跳转独立购买页面
         *
         * 会员登陆后，需要验证会员等级
         * 如果是默认等级 需要跳转制定选择商品购买页面
         */
        $data['is_level_compel'] = 0;
        if (app('plugins')->isEnabled('level-compel')) {
            $data['is_level_compel'] = (new LevelCompelService())->getSetting($member_id);
        }


        if (is_null($integrated)) {
            return $this->successJson('邀请页面开关', $data);
        } else {
            return show_json(1, $data);
        }
    }

    public function confirmGoods()
    {
        $member_id = \YunShop::app()->getMemberId();
        $member = MemberShopInfo::getMemberShopInfo($member_id);
        $parent = MemberShopInfo::getMemberShopInfo($member->parent_id);
        $invite_code = '';
        if ($parent) {
            $invite_code = $parent->invite_code ?: '';
        }
        $model = new MemberInvitationCodeLog();
        $model->uniacid = \YunShop::app()->uniacid;
        $model->member_id = $member_id;
        $model->mid = $member->parent_id;
        $model->invitation_code = $invite_code;
        if (!$model->save()) {
            return $this->errorJson('保存失败');
        }
        return $this->successJson('ok');
    }

    public function refuseGoods()
    {
        $invite_code = request()->invite_code;
        $member_id = \YunShop::app()->getMemberId();
        $parent = (new MemberShopInfo())->getInviteCodeMember($invite_code);
        if (!$parent) {
            return $this->errorJson('邀请码有误!请重新填写');
        }
        $yz_member = MemberShopInfo::getMemberShopInfo($member_id);
        MemberShopInfo::change_relation($member_id, $parent->member_id);
        $model = new MemberInvitationCodeLog();
        $model->uniacid = \YunShop::app()->uniacid;
        $model->member_id = $member_id;
        $model->mid = $parent->member_id;
        $model->invitation_code = $invite_code;
        if (!$model->save()) {
            return $this->errorJson('保存失败');
        }
        if (!$yz_member->inviter) {
            event(new MemberNewOfflineEvent($member_id, $parent->member_id, false));
        }
        return $this->successJson('ok');
    }

    public function isValidatePageGoods()
    {
        $member_id = \YunShop::app()->getMemberId();
        $log = MemberInvitationCodeLog::getLogByMemberId($member_id);
        return $this->successJson('ok', [
            'is_invite' => $log ? 1 : 0,
        ]);
    }

    public function getShopSet()
    {
        $data = [
            'shop_set_name'  => Setting::get('shop.shop.name') ?: '商城名称',
            'default_invite' => Setting::get('shop.member.default_invite') ?: '',//默认邀请码
        ];
        return $this->successJson('ok', $data);
    }

    public function getArticleQr()
    {
        if (app('plugins')->isEnabled('article')) {
            $article_qr_set = Setting::get('plugin.article.qr');
            $qr = MemberModel::getAgentQR();
            if ($article_qr_set == 1) {
                return $this->errorJson('二维码开关关闭!');
            }
            return $this->successJson('获取二维码成功!', $qr);
        }
        return $this->errorJson('文章插件未开启!');
    }

    public function isBindMobile($member_set, $member_id)
    {
        $is_bind_mobile = 0;

        if ((0 < $member_set['is_bind_mobile']) && $member_id && $member_id > 0) {
            $member_model = Member::getMemberById($member_id);

            if ($member_model && empty($member_model->mobile)) {
                $is_bind_mobile = intval($member_set['is_bind_mobile']);
            }
        }
        return $is_bind_mobile;
    }

    public function isOpen()
    {
        $settinglevel = \Setting::get('shop.member');

        $info['is_open'] = 0;

        //判断是否显示等级页
        if ($settinglevel['display_page']) {
            $info['is_open'] = 1;
        }

        $info['level_type'] = $settinglevel['level_type'] ?: '0';

        return show_json(1, $info);
    }

    public function pluginStore()
    {
        if (app('plugins')->isEnabled('store-cashier')) {
            $store = Store::getStoreByUid(\YunShop::app()->getMemberId())->first();
            if (!$store || $store->is_black == 1) {
                return show_json(0, ['status' => 0]);
            }

            return show_json(1, ['status' => 1]);
        }

        return show_json(1, ['status' => 0]);
    }

    public function getMemberSetting(Request $request, $integrated)
    {
        $set = \Setting::get('shop.member');
        //判断微信端是否开启了手机号登录
        $data['wechat_login_mode'] = $set['wechat_login_mode'] ? true : false;
        //判断是否显示等级页
        $data['level']['is_open'] = $set['display_page'] ? 1 : 0;
        $data['level']['level_type'] = $set['level_type'] ?: '0';

        //获取自定义字段
        $data['custom'] = [
            'is_custom'    => $set['is_custom'],
            'custom_title' => $set['custom_title'],
            'is_validity'  => $set['level_type'] == 2 ? true : false,
            'term'         => $set['term'] ?: 0,
        ];
        // 是否显示会员id
        $data['show_member_id'] = $set['show_member_id'] == 1 ? 1 : 0;

        $data['member_auth_pop_switch'] = Setting::get('plugin.min_app.member_auth_pop_switch') ? 1 : 0;

        if (is_null($integrated)) {
            return $this->successJson('获取自定义字段成功！', $data);
        } else {
            return show_json(1, $data);
        }
    }

    public function getMemberOrder(Request $request, $integrated)
    {
        //订单显示
        $order_info = \app\frontend\models\Order::getOrderCountGroupByStatus(
            [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND]
        );
        $order['order'] = $order_info;
        //酒店订单
        if (app('plugins')->isEnabled('hotel')) {
            $order['hotel_order'] = \Yunshop\Hotel\common\models\Order::getHotelOrderCountGroupByStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND]
            );
        }
        // 拼团订单
        if (app('plugins')->isEnabled('fight-groups')) {
            $order['fight_groups_order'] = \Yunshop\FightGroups\common\models\Order::getFightGroupsOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE, Order::REFUND]
            );
        }
        // 0.1元拼团订单
        if (app('plugins')->isEnabled('group-work')) {
            $order['group_work_order'] = OrderModel::getGroupWorkOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND]
            );
        }
        //抢团订单
        if (app('plugins')->isEnabled('snatch-regiment')) {
            $order['snatch_regiment_order'] = \Yunshop\SnatchRegiment\common\models\Order::getSnatchRegimentOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE, Order::REFUND]
            );
        }

        //上门安装订单
        if (app('plugins')->isEnabled('live-install')) {
            $order['live_install_order'] = \Yunshop\LiveInstall\models\InstallOrder::getInstallOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]
            );
        }
        //上门安装师傅订单
        if (app('plugins')->isEnabled('live-install') && \Yunshop\LiveInstall\services\SettingService::checkIsWorker(
            )) {
            $order['live_install_work_order'] = \Yunshop\LiveInstall\models\InstallOrder::getInstallOrderWorkCountStatus(
                [2, 3, 4, 6]
            );
        }

        //cps订单
        if (app('plugins')->isEnabled('aggregation-cps')) {
            $order['aggregation_cps_order'] = \Yunshop\AggregationCps\api\models\BingBirdOrderModel::countOrderByStatus(
            );
        }

        // 新拼团订单
        if (app('plugins')->isEnabled('ywm-fight-groups')) {
            $order['ywm_fight_groups_order'] = \Yunshop\YwmFightGroups\common\models\Order::getFightGroupsOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE, Order::REFUND]
            );
        }

        if (\app\common\services\plugin\leasetoy\LeaseToySet::whetherEnabled()) {
            $order['lease_order'] = \Yunshop\LeaseToy\models\Order::getLeaseOrderCountGroupByStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]
            );
        }

        //消费券联盟订单
        if (app('plugins')->isEnabled('coupon-store') && \Yunshop\CouponStore\services\SettingService::getSetting(
            )['open_state']) {
            $order['coupon_store_order'] = \Yunshop\CouponStore\models\StoreOrder::getOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::COMPLETE]
            );
        }

        //周边游订单
        if (app('plugins')->isEnabled('travel-around')) {
            $order['travel_around_order'] = \Yunshop\TravelAround\models\FrontendOrder::GetTravelAroundOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]
            );;
        }

        //蛋糕叔叔订单
        if (app('plugins')->isEnabled('yz-supply-cake')) {
            $order['yz_supply_cake_order'] = \Yunshop\YzSupplyCake\models\FrontendOrder::GetYzSupplyCakeOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]
            );;
        }
        //
        if (app('plugins')->isEnabled('union-cps')) {
            $order['yz_union_cps_order'] = \Yunshop\UnionCps\common\models\UnionCpsOrder::GetYzUnionOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::CLOSE]
            );;
        }
        if (app('plugins')->isEnabled('yz-supply-lease')) {
            $order['yz_supply_lease_order'] = \Yunshop\YzSupplyLease\common\models\FrontendOrder::GetYzSupplyLeaseOrderCountStatus(
                [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]
            );;
        }

        foreach ($order as $key => $item) {
            $order[$key] = array_values(collect($item)->sortBy('status')->all());
        }

//        //宠物医院插件会员中心模板化显示 todo;前端说没用了，我就注释掉了
//        $order['current']= MemberCenter::current()->all();

        if (is_null($integrated)) {
            return $this->successJson('获取会员订单成功！', $order);
        } else {
            return show_json(1, $order);
        }
    }

    public function getMemberOrderName(Request $request, $integrated)
    {
        //订单名字
        $order['order'] = '商城订单';
        //酒店订单
        if (app('plugins')->isEnabled('hotel')) {
            $order['hotel_order'] = '酒店订单';
        }
        // 拼团订单
        if (app('plugins')->isEnabled('fight-groups')) {
            $order['fight_groups_order'] = '拼团订单';
        }

        // 0.1元拼团订单
        if (app('plugins')->isEnabled('group-work')) {
            $setGroupWrok = \Setting::get('plugin.group_work');
            $order['group_work_order'] = $setGroupWrok['plugin_name'] ? $setGroupWrok['plugin_name'] : '0.1元拼订单';
        }

        //抢团订单
        if (app('plugins')->isEnabled('snatch-regiment')) {
            $order['snatch_regiment_order'] = '抢团订单';
        }

        //上门安装订单
        if (app('plugins')->isEnabled('live-install')) {
            $another_name = \Yunshop\LiveInstall\services\SettingService::getAnotherName();
            $order['live_install_order'] = $another_name['plugin_name'] . '订单';
            //上门安装师傅订单
            if (\Yunshop\LiveInstall\services\SettingService::checkIsWorker()) {
                $order['live_install_work_order'] = $another_name['worker_name'] . '订单';
            }
        }

        if (app('plugins')->isEnabled('aggregation-cps')) {
            $order['aggregation_cps_order'] = 'CPS订单';
        }

        if (app('plugins')->isEnabled('coupon-store')) {
            $order['coupon_store_order'] = (defined(
                    'COUPON_STORE_PLUGIN_NAME'
                ) ? COUPON_STORE_PLUGIN_NAME : '消费券联盟') . '订单';
        }

        if (\app\common\services\plugin\leasetoy\LeaseToySet::whetherEnabled()) {
            $order['lease_order'] = '租赁订单';
        }

        if (app('plugins')->isEnabled('ys-system')) {
            $order['ys_system'] = '线下订单';
        }

        // 新拼团订单
        if (app('plugins')->isEnabled('ywm-fight-groups')) {
            $order['ywm_fight_groups_order'] = '新拼团订单';
        }

        // 周边游订单
        if (app('plugins')->isEnabled('travel-around')) {
            $order['travel_around_order'] = '周边游订单';
        }
        // 蛋糕叔叔订单
        if (app('plugins')->isEnabled('yz-supply-cake')) {
            $order['yz_supply_cake_order'] = '蛋糕订单';
        }
        // 聚推联盟订单
        if (app('plugins')->isEnabled('union-cps')) {
            $order['yz_union_cps_order'] = '聚推联盟订单';
        }
        // 供应链租赁订单
        if (app('plugins')->isEnabled('yz-supply-lease')) {
            $order['yz_supply_lease_order'] = '供应链租赁订单';
        }

        if (is_null($integrated)) {
            return $this->successJson('获取会员订单成功！', $order);
        } else {
            return show_json(1, $order);
        }
    }

    public function memberData()
    {
        if (!miniVersionCompare('1.1.141') || !versionCompare('1.1.140')) {
            return $this->oldMemberData();
        }
        return $this->successJson('',app('MemberCenter')->getData());
    }


    public function oldMemberData()
    {
        $request = Request();
        //查看会员订单
        $this->dataIntegrated((new MemberDesignerController())->index($request, true), 'designer');
        if (
            (miniVersionCompare('1.1.109') && versionCompare('1.1.109')) &&
            ($this->apiData['designer'] && $this->apiData['designer']['status'] == false)) {
            //版本符合且没有自定义设置装修
            if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
                //会员中心模版
                $view_set = \Yunshop\Decorate\models\DecorateTempletModel::getList(
                    ['is_default' => 1, 'type' => 1],
                    '*',
                    false
                );
                if (empty($view_set) || $view_set->code == 'member01') {
                    return $this->newMemberData();
                }
            }
        }

        $this->dataIntegrated($this->getUserInfo($request, true), 'member');
        $this->dataIntegrated($this->getEnablePlugins($request, true), 'plugins');
        //是否显示我的推广
//        $this->dataIntegrated($this->isOpenRelation($request, true), 'relation');
        //查看自定义
//        $this->dataIntegrated($this->getCustomField($request, true), 'custom');
        //查看等级是否开启
//        $this->dataIntegrated($this->isOpen(), 'level');
        //查看自己是否是门店店主
//        $this->dataIntegrated($this->pluginStore(), 'isStore');
        //查看会员设置
        $this->dataIntegrated($this->getMemberSetting($request, true), 'setting');
        //查看会员订单
        $this->dataIntegrated($this->getMemberOrder($request, true), 'order');
        $this->dataIntegrated($this->getMemberOrderName($request, true), 'order_name');
        return $this->successJson('', $this->apiData);
    }

    /**
     * 新版会员中心数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function newMemberData()
    {
        $request = Request();
        $this->dataIntegrated($this->getUserInfo($request, true), 'member');
        //查看会员设置
        $this->dataIntegrated($this->getMemberSetting($request, true), 'setting');
        //查看会员订单
        $this->dataIntegrated($this->getMemberOrder($request, true), 'order');
        $this->dataIntegrated($this->getMemberOrderName($request, true), 'order_name');
        $memberCenterData = new MemberCenterDataService($this->apiData);
        $this->apiData['plugins'] = $memberCenterData->getEnablePlugins();
        $this->apiData['service'] = $memberCenterData->getService(true);
        $this->apiData['plugins_data'] = $memberCenterData->getPluginData('');  //默认第一个
        if (app('plugins')->isEnabled('decorate') && Setting::get('plugin.decorate.is_open') && Setting::get(
                'decorate.mc_one_default.open_state'
            )) {
            $this->apiData['plugins_data']['nav'] = DecorateDefaultTabModel::formNav(
                $this->apiData['plugins_data']['nav']
            );
        }
//        $this->dataIntegrated($memberCenterData->getPluginData(''), 'plugins_data');
        return $this->successJson('', $this->apiData);
    }

    /**
     * 会员中心模板01，列表数据获取
     * @return \Illuminate\Http\JsonResponse
     */
    public function pluginData(): \Illuminate\Http\JsonResponse
    {
        if (!miniVersionCompare('1.1.141') || !versionCompare('1.1.140')) {
            return $this->oldPluginData();
        }
        $code = request()->code;
        if (!$code) {
            return $this->errorJson('参数错误');
        }
        $data = app('MemberCenter')->getPluginDataDetail($code);
        return $this->successJson('ok',$data);
    }

    public function oldOluginData(): \Illuminate\Http\JsonResponse
    {
        $code = request()->code;
        if (!$code) {
            return $this->errorJson('参数错误');
        }
        $memberCenterData = new MemberCenterDataService();
        $data = $memberCenterData->getPluginData($code, false);
        return $this->successJson('ok', ['data' => $data['data']]);
    }

    /**
     * 更多工具、插件
     */
    public function morePlugins()
    {
        if (!miniVersionCompare('1.1.141') || !versionCompare('1.1.140')) {
            return $this->oldMorePlugins();
        }
        $data = app('MemberCenter')->getViewTypeAllData1();
        return $this->successJson('ok', $data);
    }

    public function oldMorePlugins()
    {
        $memberId = \YunShop::app()->getMemberId();
        $memberCenter = new MemberCenterService();
        $arr = $memberCenter->getMemberData($memberId);//获取会员中心页面各入口
        $newArr = $memberCenter->defaultPluginData($memberId);;
        foreach ($arr as $key => $item) {
            if (!in_array($key, ['is_open', 'hotel', 'plugins', 'ViewSet'])) {
                $newArr = array_merge($newArr, $item);
            }
        }
        unset($arr);
        $newArr = collect($newArr);
        $plugin = $memberCenter->morePluginData();
        foreach ($plugin as $key => $item) {
            $plugin[$key]['plugin'] = $newArr->whereIn('name', $item['plugin'])->all();
            $plugin[$key]['plugin'] = array_values($plugin[$key]['plugin']);
        }

        return $this->successJson('ok', ['plugin' => $plugin]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function getMemberList()
    {
        app('db')->cacheSelect = true;
        $member_referral = new MemberReferralService();
        $this->dataIntegrated($member_referral->getMyAgentData_v2(), 'agent_data');
        $this->dataIntegrated($member_referral->getMyAgent_v2(), 'my_agent');
        $this->dataIntegrated($member_referral->getMyReferral_v2(), 'my_referral');
        $this->dataIntegrated($member_referral->getMyReferralParents(), 'my_referral_parents');
//        $request = Request();
//        $this->dataIntegrated($this->getMyAgentData_v2($request, true), 'agent_data');
//        $this->dataIntegrated($this->getMyAgent_v2($request, true), 'my_agent');
//        $this->dataIntegrated($this->getMyReferral_v2($request, true), 'my_referral');
//        $this->dataIntegrated($this->getMyReferralParents($request), 'my_referral_parents');
        $this->pluginEnable();
        return $this->successJson('', $this->apiData);
    }

    public function getMiniTemplateCorrespond()
    {
        app('db')->cacheSelect = true;
        $status = \YunShop::request()->get("small_type");
        $status = empty($status) ? 0 : $status;

        $list = MiniTemplateCorresponding::uniacid()->where("small_type", $status)->get();

        $list = empty($list) ? [] : $list->toArray();

        $mini = MinAppTemplateMessage::where('is_open', 1)->pluck("title");

        $mini = empty($mini) ? [] : $mini->toArray();

        if ($list) {
            foreach ($list as $key => $value) {
                if (!in_array($value['template_name'], $mini)) {
                    unset($list[$key]);
                }
            }
        }
        $list = array_values($list);

        event($event = new OrderMiniNoticeListEvent($list, $status, intval(request()->order_id) ?: 0));
        $list = $event->getList();
        if (count($list) > 3) {
            array_splice($list, 3);
        }
        return $this->successJson("", $list);
    }

    public function pluginEnable()
    {
        if (app('plugins')->isEnabled('regional-reward')) {
            $regional_set = array_pluck(Setting::getAllByGroup('regional-reward')->toArray(), 'value', 'key');
            if ($regional_set['achievement_show'] == 1) {
                $this->apiData['my_achievement'] = true;
            }
        }

        if (app('plugins')->isEnabled('member-center-agent') && \Setting::get('plugin.member-center-agent.is_open')) {
            $this->apiData['is_link'] = true;
        }
    }

    /**
     * 会员定位记录
     */
    public function saveMemberLocation()
    {
        $data = [];
        $data['uniacid'] = \Yunshop::app()->uniacid;
        $data['member_id'] = \YunShop::app()->getMemberId();
        $data['province_name'] = request()->province_name;  //省
        $data['city_name'] = request()->city_name;          //市
        $data['district_name'] = request()->district_name;  //区
        $data['longitude'] = request()->longitude;          //经度
        $data['latitude'] = request()->latitude;            //纬度

        if (!$data['member_id']) {
            return $this->errorJson('会员不存在!');
        }

        foreach ($data as $key => $value) {
            if (!$value) {
                return $this->errorJson('定位数据错误!');
            }
        }

        $memberPosition = MemberPosition::getMemberLocation($data['member_id']);

        if (!$memberPosition) {
            $memberPosition = new MemberPosition();
        }
        $memberPosition->fill($data);

        if ($memberPosition->save()) {
            return $this->successJson('会员位置信息记录成功');
        }

        return $this->errorJson('会员位置信息记录失败');
    }

    /**
     * 获取小程序会员手机号
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function miniMemberMobile()
    {
        $code = \YunShop::request()->code;

        if (empty($code)) {
            return $this->errorJson('手机号获取凭证不能为空');
        }

        $data = app(MemberMiniAppService::class)->getPhoneNumber($code);

        return $this->successJson('ok', $data);
    }

    public function invitationCode()
    {
        $member_id = \YunShop::app()->getMemberId();

        $qrCodeUrl = '';
        if (request()->input('type') == 2) {
            $waxCode = new \app\common\services\wechat\WxaQrCodeService(
                'static/qrcode/invitation/' . \YunShop::app()->uniacid
            );

            $waxCode->setParameter('scene', "m={$member_id}&lk=1");
            $waxCode->setParameter('page', 'packageF/sign_in_subscribe/invite/invite');

            $qrCodeUrl = $waxCode->getQrCode();
        }


        return $this->successJson('小程序码', ['qr_code_url' => $qrCodeUrl]);
    }

}
