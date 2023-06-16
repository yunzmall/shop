<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/12/28
 * Time: 18:12
 */

namespace app\common\services\member;


use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\models\Member as Members;
use app\common\models\MemberGroup as Member_Group;
use app\common\models\MemberShopInfo as MemberShop_Info;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\SubMemberModel as SubMember_Model;
use Illuminate\Support\Str;
use EasyWeChat\Foundation\Application;
use Yunshop\AlipayOnekeyLogin\services\SynchronousUserInfo;
use Yunshop\GroupWork\frontend\modules\order\models\OrderModel;
use Yunshop\Kingtimes\common\models\Distributor;
use Yunshop\Kingtimes\common\models\Provider;
use Yunshop\RealNameAuth\common\models\RealNameAuth;
use Yunshop\RealNameAuth\common\models\RealNameAuthSet;
use Yunshop\RegistrationArea\Common\models\MemberLocation;
use Yunshop\YunSign\common\models\Contract;
use Yunshop\YunSign\common\models\ContractNum;
use Yunshop\YunSign\common\models\PersonAccount;
use app\backend\modules\order\models\Order;

class MemberService
{
    public static function addMember($data,$is_encrypt = false)
    {
        $uniacid = \YunShop::app()->uniacid;
        $mobile = $data['mobile'];
        $password = $data['password'];
        //获取图片
        $member_set = \Setting::get('shop.member');
        \Log::info('member_set', $member_set);
        if (isset($member_set) && $member_set['headimg']) {
            $avatar = yz_tomedia($member_set['headimg']);
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        //判断是否已注册
        $member_info = MemberModel::getId($uniacid, $mobile);
        \Log::info('member_info', $member_info);

        if (!empty($member_info)) {
            throw new AppException('该手机号已被注册');
        }

        //添加mc_members表
        $default_groupid = Member_Group::getDefaultGroupId($uniacid)->first();
        \Log::info('default_groupid', $default_groupid);
        $data = array(
            'uniacid' => $uniacid,
            'mobile' => $mobile,
            'groupid' => $default_groupid->id ? $default_groupid->id : 0,
            'createtime' => time(),
            'nickname' => $data['nickname'] ?: $mobile,
            'avatar' => $avatar,
            'gender' => 0,
            'residecity' => '',
        );
        //随机数
        if (!$is_encrypt || !$data['salt']) {
            $data['salt'] = Str::random(8);
        }
        \Log::info('salt', $data['salt']);

        //加密
        if (!$is_encrypt || !$data['password']) {
            $data['password'] = md5($password . $data['salt']);
        }

        $memberModel = MemberModel::create($data);
        $member_id = $memberModel->uid;

        //手机归属地查询插入
        $phoneData = file_get_contents((new PhoneAttributionService())->getPhoneApi($mobile));
        $phoneArray = json_decode($phoneData);
        $phone['uid'] = $member_id;
        $phone['uniacid'] = $uniacid;
        $phone['province'] = $phoneArray->data->province;
        $phone['city'] = $phoneArray->data->city;
        $phone['sp'] = $phoneArray->data->sp;

        $phoneModel = new PhoneAttribution();
        $phoneModel->updateOrCreate(['uid' => $member_id], $phone);

        //默认分组表
        //添加yz_member表
        $default_sub_group_id = Member_Group::getDefaultGroupId()->first();

        if (!empty($default_sub_group_id)) {
            $default_subgroup_id = $default_sub_group_id->id;
        } else {
            $default_subgroup_id = 0;
        }

        $sub_data = array(
            'member_id' => $member_id,
            'uniacid' => $uniacid,
            'group_id' => $default_subgroup_id,
            'level_id' => 0,
            'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
        );

        //添加用户子表
        SubMember_Model::insertData($sub_data);
        //生成分销关系链
        Members::createRealtion($member_id);

//            $cookieid = "__cookie_yun_shop_userid_{$uniacid}";
//            Cookie::queue($cookieid, $member_id);
//            Session::set('member_id', $member_id);

        $password = $data['password'];
        $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
        $yz_member = MemberShop_Info::getMemberShopInfo($member_id)->toArray();
        $data = MemberModel::userData($member_info, $yz_member);
        return $data;
    }
	
	
	public function getMemberOrder($integrated)
	{
		//订单显示
		$order_info = \app\frontend\models\Order::getOrderCountGroupByStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND]);
		$order['order'] = $order_info;
		//酒店订单
		if (app('plugins')->isEnabled('hotel')) {
			$order['hotel_order'] = \Yunshop\Hotel\common\models\Order::getHotelOrderCountGroupByStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND]);
		}
		// 拼团订单
		if (app('plugins')->isEnabled('fight-groups')) {
			$order['fight_groups_order'] = \Yunshop\FightGroups\common\models\Order::getFightGroupsOrderCountStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE, Order::REFUND]);
		}
		// 0.1元拼团订单
		if (app('plugins')->isEnabled('group-work')) {
			$order['group_work_order'] = OrderModel::getGroupWorkOrderCountStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND]);
		}
		//抢团订单
		if (app('plugins')->isEnabled('snatch-regiment')) {
			$order['snatch_regiment_order'] = \Yunshop\SnatchRegiment\common\models\Order::getSnatchRegimentOrderCountStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE, Order::REFUND]);
		}
		
		//上门安装订单
		if (app('plugins')->isEnabled('live-install')) {
			$order['live_install_order'] = \Yunshop\LiveInstall\models\InstallOrder::getInstallOrderCountStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]);
		}
		//上门安装师傅订单
		if (app('plugins')->isEnabled('live-install') && \Yunshop\LiveInstall\services\SettingService::checkIsWorker()) {
			$order['live_install_work_order'] = \Yunshop\LiveInstall\models\InstallOrder::getInstallOrderWorkCountStatus([2, 3, 4, 6]);
		}
		
		//cps订单
		if (app('plugins')->isEnabled('aggregation-cps')) {
			$order['aggregation_cps_order'] = \Yunshop\AggregationCps\api\models\BingBirdOrderModel::countOrderByStatus();
		}
		
		if (\app\common\services\plugin\leasetoy\LeaseToySet::whetherEnabled()) {
			$order['lease_order'] = \Yunshop\LeaseToy\models\Order::getLeaseOrderCountGroupByStatus([Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::COMPLETE]);
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
	
	public function getMemberOrderName($integrated)
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
		
		if (\app\common\services\plugin\leasetoy\LeaseToySet::whetherEnabled()) {
			$order['lease_order'] = '租赁订单';
		}
		
		if (is_null($integrated)) {
			return $this->successJson('获取会员订单成功！', $order);
		} else {
			return show_json(1, $order);
		}
	}
}