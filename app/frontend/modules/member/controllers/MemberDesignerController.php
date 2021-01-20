<?php
/**
 * Created by PhpStorm.
 * User: win 10
 * Date: 2019/7/16
 * Time: 14:53
 */

namespace app\frontend\modules\member\controllers;


use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\models\Member;
use app\frontend\controllers\HomePageController;
use app\frontend\modules\coupon\controllers\MemberCouponController;
use Yunshop\Diyform\api\DiyFormController;
use Yunshop\Designer\models\MemberDesigner;
use Yunshop\Designer\services\DesignerService;
use app\frontend\modules\member\models\MemberModel;
use Yunshop\NearbyStoreGoods\frontend\controllers\DesignerController;
use Yunshop\Commission\models\Agents;
use Yunshop\Commission\models\AgentLevel;
use Yunshop\TeamDividend\admin\models\TeamDividendAgencyModel;
use Yunshop\TeamDividend\models\TeamDividendLevelModel;
use app\frontend\modules\member\controllers\MemberController;

class MemberDesignerController extends ApiController
{
    public function index($request, $integrated = null)
    {//代码结构有机会一定要重新弄一下。。。
        $res = [];
        $res['status'] = false;
        $res['data'] = [];
        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;
        $set = \Setting::get('plugin.video-share');

        //如果安装了新装修插件并开启插件
        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1")
        {

            $page = new \Yunshop\Decorate\frotend\IndexController();
            $pageType = \Yunshop::request()->type;
            if ($pageType == '7') {
                $pageType = '3';
            }
            if ($pageType == '8') {
                $pageType = '4';
            }
            $page->page_type = $pageType;
            $page->page_scene  = '2';
            $page->page_sort = '1';
            if ($pageType == '2')
            {
                $page->page_sort = "2"; //2为会员装修页面
            }

            $decorate = $page->getPage();
            if ($decorate)
            {
                $decorate['datas']=json_decode($decorate['datas'],true);
                $decorate['page_info']=json_decode($decorate['page_info'],true);

                $memberData = $this->getMemberData();
                //收银台属于插件第二个按钮，特殊处理
                $is_cashier = 0;
                $has_cashier = 1;
                if ($memberData['merchants_arr']['cashier']) {
                    $is_cashier = 1;
                }

                $is_love_open = app('plugins')->isEnabled('love');
                foreach ($decorate['datas'] as $key => $value)
                {
                    //实用工具
                    if ($value['component_key'] == 'U_membertop')
                    {
                        $name = '';
                        if ($value['remote_data']['grade_type'] == 2) {
                            if (app('plugins')->isEnabled('team-dividend')) {
                                $name = $this->getLevelName(2);
                            } else {
                                $decorate['datas'][$key]['remote_data']['grade_type'] = 1;
                            }
                        }
                        if ($value['remote_data']['grade_type'] == 3) {
                            if (app('plugins')->isEnabled('commission')) {
                                $name = $this->getLevelName(3);
                            } else {
                                $decorate['datas'][$key]['remote_data']['grade_type'] = 1;
                            }
                        }
                        if (!$value['remote_data']['grade_type']) {
                            $decorate['datas'][$key]['remote_data']['grade_type'] = 1;
                        }

                        if (MemberModel::isAgent()) {
                            $decorate['datas'][$key]['remote_data']['isagent'] = true;
                        } else {
                            $decorate['datas'][$key]['remote_data']['isagent'] = false;
                        }

                        $decorate['datas'][$key]['remote_data']['levelname'] = $name;
                    }

                    //实用工具
                    if ($value['component_key'] == 'U_membertool')
                    {
                        foreach ($value['remote_data']['show_list'] as $pkey => $par)
                        {
                            if (!in_array($par['name'], $memberData['tools']) || $par['is_open'] == false)
                            {
                                unset($decorate['datas'][$key]['remote_data']['show_list'][$pkey]);
                            }
                        }
                        $decorate['datas'][$key]['remote_data']['show_list'] = array_values($decorate['datas'][$key]['remote_data']['show_list']);
                    }

                    // 商家管理
                    if ($value['component_key'] == 'U_membermerchant')
                    {
                        foreach ($value['remote_data']['show_list'] as $pkey => $par)
                        {

                            if (in_array($par['name'], ['store-cashier', 'hotel', 'supplier', 'micro','package_deliver']))
                            {
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['title'] = $memberData['merchants_arr'][$par['name']]['title'];
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['url'] = $memberData['merchants_arr'][$par['name']]['url'];
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['mini_url'] = $memberData['merchants_arr'][$par['name']]['mini_url'];
                            }

                            if ($par['name'] == 'cashier') {
                                $has_cashier = 0;
                            }

                            if ($par['name'] == 'store-cashier')
                            {
                                $storeCashier = true;
                            }

                            if (!in_array($par['name'], $memberData['merchants']) || $par['is_open'] == false)
                            {
                                unset($decorate['datas'][$key]['remote_data']['show_list'][$pkey]);
                            }
                        }

                        if ($is_cashier == 1 && $has_cashier == 1 && $storeCashier === true)
                        {
                            $decorate['datas'][$key]['remote_data']['show_list'][] = $memberData['merchants_arr']['cashier'];
                        }

                        $decorate['datas'][$key]['remote_data']['show_list'] = array_values($decorate['datas'][$key]['remote_data']['show_list']);
                    }

                    //营销互动
                    if ($value['component_key'] == 'U_membermarket')
                    {
                        foreach ($value['remote_data']['show_list'] as $pkey => $par)
                        {
                            if(app('plugins')->isEnabled('video-share') && $par['name'] == 'video-share' && $set['list_style'] == 2)
                            { //视频分享url单独处理
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['url'] = 'VideoDetail';
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['mini_url'] = '/packageC/video_goods/VideoDetail/VideoDetail';
                            }elseif(app('plugins')->isEnabled('video-share') && $par['name'] == 'video-share'){
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['url'] = 'VideoList';
                                $decorate['datas'][$key]['remote_data']['show_list'][$pkey]['mini_url'] = '/packageC/video_goods/VideoList/VideoList';
                            }

                            if (!in_array($par['name'], $memberData['markets']) || $par['is_open'] == false)
                            {
                                unset($decorate['datas'][$key]['remote_data']['show_list'][$pkey]);
                            }
                        }
                        $decorate['datas'][$key]['remote_data']['show_list'] = array_values($decorate['datas'][$key]['remote_data']['show_list']);
                    }

                    //资产权益 
                    if ($value['component_key'] == 'U_memberrights')
                    {
                        foreach ($value['remote_data']['show_list'] as $pkey => $par)
                        {
                            if (!in_array($par['name'], $memberData['assets']) || $par['is_open'] == false)
                            {
                                unset($decorate['datas'][$key]['remote_data']['show_list'][$pkey]);
                            }
                        }
                        $decorate['datas'][$key]['remote_data']['show_list'] = array_values($decorate['datas'][$key]['remote_data']['show_list']);
                    }
                }

                $memberController = new MemberController;
                $advertisement = $memberController->getFirstLogin('member');
                $decorate['pageinfo']['params']['advertisement'] = $advertisement;

                //给前端判断是否新装修页面
                $decorate['page_plugins']='decorate';
                $res['data'] = $decorate;
                $res['status'] = true;
            }

        } elseif (app('plugins')->isEnabled('designer')) {
            $designer = $this->getDesigner();
            if ($designer->datas) {
                $datas = (new DesignerService())->getMemberData($designer->datas);

                $memberData = $this->getMemberData();
                //收银台属于插件第二个按钮，特殊处理
                $is_cashier = 0;
                $has_cashier = 1;
                if ($memberData['merchants_arr']['cashier']) {
                    $is_cashier = 1;
                }

                $is_love_open = app('plugins')->isEnabled('love');
                $new_member = false;//前端需要
                $valueArr = array_column($datas,'temp');
                if(in_array('memberincome',$valueArr)){
                    $new_member = true;//前端需要
                }

                foreach ($datas as $dkey => $design) {
                    if ($design['temp'] == 'membercenter') {
                        if ($design['params']['memberredlove'] == true || $design['params']['memberwhitelove'] == true) {
                            if (!$is_love_open) {
                                $datas[$dkey]['params']['memberredlove'] = false;
                                $datas[$dkey]['params']['memberwhitelove'] = false;
                            }
                        }
                        $datas[$dkey]['params']['isnew'] = $new_member;
                        $datas[$dkey]['params']['memberintegral'] = false;
                        if ($design['params']['memberintegral'] == true && app('plugins')->isEnabled('integral')) {
                            $datas[$dkey]['params']['memberintegral'] = true;
                        }
                        $name = '';
                        if ($design['params']['memberleveltype'] == 2) {
                            if (app('plugins')->isEnabled('team-dividend')) {
                                $name = $this->getLevelName(2);
                            } else {
                                $datas[$dkey]['params']['memberleveltype'] = 1;
                            }
                        }
                        if ($design['params']['memberleveltype'] == 3) {
                            if (app('plugins')->isEnabled('commission')) {
                                $name = $this->getLevelName(3);
                            } else {
                                $datas[$dkey]['params']['memberleveltype'] = 1;
                            }
                        }
                        if (!$design['params']['memberleveltype']) {
                            $datas[$dkey]['params']['memberleveltype'] = 1;
                        }
                        if (MemberModel::isAgent()) {
                            $datas[$dkey]['params']['isagent'] = true;
                        } else {
                            $datas[$dkey]['params']['isagent'] = false;
                        }
                        $datas[$dkey]['params']['levelname'] = $name;
                    }
                    if ($design['temp'] == 'memberincome') {
                        if ($design['params']['memberredlove'] == true || $design['params']['memberwhitelove'] == true) {
                            if (!$is_love_open) {
                                $datas[$dkey]['params']['memberredlove'] = false;
                                $datas[$dkey]['params']['memberwhitelove'] = false;
                            }
                        }
                        $datas[$dkey]['params']['memberintegral'] = false;
                        if ($design['params']['memberintegral'] == true && app('plugins')->isEnabled('integral')) {
                            $datas[$dkey]['params']['memberintegral'] = true;
                        }
                    }
                    if ($design['temp'] == 'membertool') {
                        foreach ($design['data']['part'] as $pkey => $par) {
                            if (!in_array($par['name'], $memberData['tools']) || $par['is_open'] == false) {
                                unset($datas[$dkey]['data']['part'][$pkey]);
                            }
                        }
                        $datas[$dkey]['data']['part'] = array_values($datas[$dkey]['data']['part']);
                    }
                    if ($design['temp'] == 'membermerchant') {
                        foreach ($design['data']['part'] as $pkey => $par) {
                            if (in_array($par['name'], ['store-cashier', 'hotel', 'supplier', 'micro'])) {
                                $datas[$dkey]['data']['part'][$pkey]['title'] = $memberData['merchants_arr'][$par['name']]['title'];
                                $datas[$dkey]['data']['part'][$pkey]['url'] = $memberData['merchants_arr'][$par['name']]['url'];
                                $datas[$dkey]['data']['part'][$pkey]['mini_url'] = $memberData['merchants_arr'][$par['name']]['mini_url'];
                            }
                            if ($par['name'] == 'cashier') {
                                $has_cashier = 0;
                            }
                            if (!in_array($par['name'], $memberData['merchants']) || $par['is_open'] == false) {
                                unset($datas[$dkey]['data']['part'][$pkey]);
                            }
                        }
                        $datas[$dkey]['data']['part'] = array_values($datas[$dkey]['data']['part']);
                        if ($is_cashier == 1 && $has_cashier == 1) {
                            $datas[$dkey]['data']['part'][] = $memberData['merchants_arr']['cashier'];
                        }
                    }
                    if ($design['temp'] == 'membermarket') {
                        foreach ($design['data']['part'] as $pkey => $par) {
                            if (!in_array($par['name'], $memberData['markets']) || $par['is_open'] == false) {
                                unset($datas[$dkey]['data']['part'][$pkey]);
                            }else{
                                if(app('plugins')->isEnabled('video-share') && $par['name'] == 'video-share' && $set['list_style'] == 2){ //视频分享url单独处理
                                    $datas[$dkey]['data']['part'][$pkey]['url'] = 'VideoDetail';
                                    $datas[$dkey]['data']['part'][$pkey]['mini_url'] = '/packageC/video_goods/VideoDetail/VideoDetail';
                                }elseif(app('plugins')->isEnabled('video-share') && $par['name'] == 'video-share'){
                                    $datas[$dkey]['data']['part'][$pkey]['url'] = 'VideoList';
                                    $datas[$dkey]['data']['part'][$pkey]['mini_url'] = '/packageC/video_goods/VideoList/VideoList';
                                }
                            }
                        }

                        $datas[$dkey]['data']['part'] = array_values($datas[$dkey]['data']['part']);

                    }
                    if ($design['temp'] == 'memberasset') {
                        foreach ($design['data']['part'] as $pkey => $par) {
                            if (!in_array($par['name'], $memberData['assets']) || $par['is_open'] == false) {
                                unset($datas[$dkey]['data']['part'][$pkey]);
                            }
                        }
                        $datas[$dkey]['data']['part'] = array_values($datas[$dkey]['data']['part']);
                    }
                    if ($design['temp'] == 'membercarorder') {
                        if (!app('plugins')->isEnabled('net-car')) {
                            unset($datas[$dkey]);
                        }
                    }
                    if ($design['temp'] == 'memberhotelorder') {
                        if (!app('plugins')->isEnabled('hotel')) {
                            unset($datas[$dkey]);
                        }
                    }
                    if ($design['temp'] == 'memberleaseorder') {
                        if (!app('plugins')->isEnabled('lease-toy')) {
                            unset($datas[$dkey]);
                        }
                    }
                    if ($design['temp'] == 'membergoruporder') {
                        if (!app('plugins')->isEnabled('fight-groups')) {
                            unset($datas[$dkey]);
                        }
                    }
                    if ($design['temp'] == 'diyform') {
                        if (!app('plugins')->isEnabled('diyform')) {
                            unset($datas[$dkey]);
                        } else {
                            $getInfo = (new DiyFormController())->getDiyFormTypeMemberData('', true, $design['data']['form_id']);
                            $datas[$dkey]['get_info'] = $getInfo['status'] == 1 ? $getInfo['json'] : [];
                        }
                    }
                    if ($design['temp'] == 'coupon') {
                        $getInfo = (new MemberCouponController())->couponsForDesigner('', true);
                        $datas[$dkey]['get_info'] = $getInfo['status'] == 1 ? $getInfo['json'] : [];
                    }
                    if ($design['temp'] == 'nearbygoods') {
                        if (app('plugins')->isEnabled('nearby-store-goods')) {
                            $set = \Setting::get('nearby-store-goods.is_open');
                            if ($set == 1) {
                                $nearService = new DesignerController();
                                $datas[$dkey]['get_info'] = $nearService->getGoods($request, true, $design['params']['displaynum'])['json'];
                            }
                        }
                    }
                    //以下从店铺装修移植过来的，不一定全
                    if ($design['temp'] == 'sign') {
                        $shop = Setting::get('shop.shop')['credit1'] ?: '积分';
                        $datas[$dkey]['params']['award_content'] = str_replace('积分', $shop, $design['params']['award_content']);
                    }

                    if ($design['temp'] == 'goods' || $design['temp'] == 'assemble' || $design['temp'] == 'flashsale') {
                        if ($is_love_open) {
                            foreach ($design['data'] as $gkey => $goode_award) {
                                $HomePage = new HomePageController();
                                $datas[$dkey]['data'][$gkey]['award'] = $HomePage->getLoveGoods($goode_award['goodid']);
                                $datas[$dkey]['data'][$gkey]['stock'] = $HomePage->getMemberGoodsStock($goode_award['goodid']);
                            }
                        } else {
                            foreach ($design['data'] as $gkey => $goode_award) {
                                $datas[$dkey]['data'][$gkey]['award'] = 0;
                            }
                        }
                        foreach ($design['data'] as $key => $goods) {
                            $goods_data = $goods_model->find($goods['goodid']);
                            // $design['data'][$key]['vip_level_status']  = $goods_data->vip_level_status;
                            $datas[$dkey]['data'][$key]['vip_level_status'] = $goods_data->vip_level_status;
                        }
                    }
                }
                $datas = array_values($datas);
                $res['data'] = $datas;
                $res['status'] = true;
            }
        }
        if (is_null($integrated)) {
            return $this->successJson('成功', $res);
        } else {
            return show_json(1, $res);
        }
    }

    /**
     * 获取可用模板
     */
    private function getDesigner()
    {
        if (\Yunshop::request()->ingress == 'weChatApplet') {
            $pageType = 9;
        } else {
            $pageType = \Yunshop::request()->type;
        }
        $designer = MemberDesigner::uniacid()
            ->whereRaw('FIND_IN_SET(?,page_type)', [$pageType])
            ->where(['shop_page_type' => MemberDesigner::PAGE_MEMBER_CENTER, 'is_default' => 1])
            ->first();
        return $designer;
    }

    /**
     * @return array
     * 获取可用插件按钮
     */
    private function getMemberData()
    {
        $memberId = \YunShop::app()->getMemberId();
        $arr = (new \app\common\services\member\MemberCenterService())->getMemberData($memberId);

        $tools = ['m-collection', 'm-footprint', 'm-address', 'm-info'];
        $merchants = [];
        //控制二维码显示，由member-data方法搬来
        $member_info = MemberModel::getUserInfos_v2(\YunShop::app()->getMemberId())->first();

        if (empty($member_info)) {
            $mid = Member::getMid();
            $this->jump = true;
            return $this->jumpUrl(\YunShop::request()->type, $mid);
        }

        $member_info = $member_info->toArray();
        $is_agent = $member_info['yz_member']['is_agent'] == 1 && $member_info['yz_member']['status'] == 2 ? true : false;
        if ($is_agent) {
            $markets = ['m-erweima'];
        } else {
            $markets = [];
        }
        $markets = array_merge($markets, ['m-pinglun', 'm-guanxi', 'm-coupon']);
        $assets = [];
        $merchants_arr = [];
        foreach ($arr['tool'] as $v) {
            $tools[] = $v['name'];
        }
        foreach ($arr['merchant'] as $v) {
            $merchants[] = $v['name'];
            $merchants_arr[$v['name']] = $v;
        }
        foreach ($arr['market'] as $v) {
            $markets[] = $v['name'];
        }
        foreach ($arr['asset_equity'] as $v) {
            $assets[] = $v['name'];
        }

        return [
            'tools'         => $tools,
            'merchants'     => $merchants,
            'markets'       => $markets,
            'assets'        => $assets,
            'merchants_arr' => $merchants_arr
        ];
    }

    private function getLevelName($type)
    {
        $name = '';
        if ($type == 2) {
            $agency_model = TeamDividendAgencyModel::getAgencyInfoByUid(\YunShop::app()->getMemberId());
            $name = $agency_model->hasOneLevel->level_name ?: '';
        }
        if ($type == 3) {
            $request = Agents::getLevelByMemberId()
                ->where('member_id', \YunShop::app()->getMemberId())
                ->first();
            if (!$request) {
                return '';
            }
            $request = $request->toArray();
            if ($request['agent_level']) {
                $name = $request['agent_level']['name'];
            } else {
                $name = AgentLevel::getDefaultLevelName();
            }
        }
        return $name;
    }
}