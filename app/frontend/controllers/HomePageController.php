<?php

namespace app\frontend\controllers;

use app\backend\modules\member\models\MemberRelation;
use app\common\components\ApiController;
use app\common\events\finance\PetEvent;
use app\common\exceptions\MemberNotLoginException;
use app\common\facades\EasyWeChat;
use app\common\facades\RichText;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\models\AccountWechats;
use app\common\models\member\MemberInvitationCodeLog;
use app\common\models\MemberShopInfo;
use app\common\models\Order;
use app\common\modules\goods\GoodsRepository;
use app\common\services\CollectHostService;
use app\common\services\popularize\PortType;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\frontend\models\PageShareRecord;
use app\frontend\modules\coupon\controllers\MemberCouponController;
use app\frontend\modules\home\services\ShopPublicDataService;
use app\frontend\modules\member\controllers\MemberController;
use app\frontend\modules\member\controllers\ServiceController;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\services\MemberLevelAuth;
use app\frontend\modules\shop\controllers\IndexController;
use Yunshop\Designer\Common\Services\IndexPageService;
use Yunshop\Designer\Common\Services\OtherPageService;
use Yunshop\Designer\Common\Services\PageTopMenuService;
use Yunshop\Designer\models\Designer;
use Yunshop\Designer\models\DesignerMenu;
use Yunshop\Designer\models\GoodsGroupGoods;
use Yunshop\Diyform\api\DiyFormController;
use Yunshop\Love\Common\Models\GoodsLove;
use Yunshop\Love\Common\Services\SetService;
use Yunshop\Designer\Backend\Modules\Page\Controllers\RecordsController;
use app\common\models\Goods;
use Yunshop\NearbyStoreGoods\common\services\DesignerService;
use Yunshop\NearbyStoreGoods\frontend\controllers\DesignerController;
use app\common\helpers\Client;
use app\frontend\modules\home\HomePage;
use Yunshop\NewMemberPrize\frontend\controllers\NewMemberPrizeController;
use Yunshop\SnatchRegiment\common\CommonService;
use Yunshop\SnatchRegiment\models\SnatchGoods;

class HomePageController extends ApiController
{
    protected $publicAction = [
        'index',
        'defaultDesign',
        'defaultMenu',
        'defaultMenuStyle',
        'bindMobile',
        'wxapp',
        'isCloseSite',
        'designerShare',
        'getParams',
        'getCaptcha'
    ];
    protected $ignoreAction = [
        'defaultDesign',
        'defaultMenu',
        'defaultMenuStyle',
        'bindMobile',
        'wxapp',
        'isCloseSite',
        'designerShare',
        'getCaptcha'
    ];
    private $pageSize = 16;

    /**
     * @return \Illuminate\Http\JsonResponse 当路由不包含page_id参数时,提供商城首页数据; 当路由包含page_id参数时,提供装修预览数据
     */
    public function oldIndex($request, $integrated = null)
    {
        app('db')->cacheSelect = true;
        $i = \YunShop::request()->i;
        $mid = \YunShop::request()->mid;
        $type = \YunShop::request()->type;
        $pageId = (int)\YunShop::request()->page_id ?: 0;
        $member_id = \YunShop::app()->getMemberId();
        $scope = \YunShop::request()->scope;
        $basic_info = \YunShop::request()->basic_info; //全局接口前端传参

        try {
            //商城设置, 原来接口在 setting.get
            $key = \YunShop::request()->setting_key ? \YunShop::request()->setting_key : 'shop';

            if (!Cache::has('shop_setting')) {
                $setting = Setting::get('shop.' . $key);

                if (!is_null($setting)) {
                    Cache::put('shop_setting', $setting, 3600);
                }
            } else {
                $setting = Cache::get('shop_setting');
            }

            if ($setting) {
                $setting['logo'] = replace_yunshop(yz_tomedia($setting['logo']));
                if (!Cache::has('member_relation')) {
                    $relation = MemberRelation::getSetInfo()->first();

                    if (!is_null($relation)) {
                        Cache::put('member_relation', $relation, 3600);
                    }
                } else {
                    $relation = Cache::get('member_relation');
                }

                $setting['signimg'] = replace_yunshop(yz_tomedia($setting['signimg']));
                if ($relation) {
                    $setting['agent'] = $relation->status ? true : false;
                } else {
                    $setting['agent'] = false;
                }

                $setting['diycode'] = html_entity_decode($setting['diycode']);
                foreach ((new ServiceController())->index() as $k => $v) {
                    $setting[$k] = $v;
                }
                $result['mailInfo'] = $setting;
            }

            //强制绑定手机号
            if (!Cache::has('shop_member')) {
                $member_set = Setting::get('shop.member');

                if (!is_null($member_set)) {
                    Cache::put('shop_member', $member_set, 4200);
                }
            } else {
                $member_set = Cache::get('shop_member');
            }

            $is_bind_mobile = 0;

            if (!is_null($member_set)) {
                if ((0 < $member_set['is_bind_mobile']) && $member_id && $member_id > 0) {
                    if (!Cache::has($member_id . '_member_info')) {
                        $member_model = Member::getMemberById($member_id);
                        if (!is_null($member_model)) {
                            Cache::put($member_id . '_member_info', $member_model, 4200);
                        }
                    } else {
                        $member_model = Cache::get($member_id . '_member_info');
                    }
                    if ($member_model && empty($member_model->mobile)) {
                        $is_bind_mobile = intval($member_set['is_bind_mobile']);
                    }
                }
            }
            $result['mailInfo']['is_bind_mobile'] = $is_bind_mobile;

            //用户信息, 原来接口在 member.member.getUserInfo
            if (empty($pageId)) {
                if (!empty($member_id)) //如果是请求首页的数据 
                {
                    if (!empty(Member::current()->uid)) {
                        // $member_info = $member_info->toArray();
                        // $data        = MemberModel::userData($member_info, $member_info['yz_member']);
                        // $data        = MemberModel::addPlugins($data);
                        $result['memberinfo']['uid'] = Member::current()->uid;
                    }
                }
            }

            //如果安装了新装修插件并开启插件
            if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
                //系统信息
                $system = \Setting::get('shop');
                $system['logo'] = replace_yunshop(yz_tomedia($system['logo']));
                $system['signimg'] = replace_yunshop(yz_tomedia($system['signimg']));

                $system = collect($system)->map(function ($item, $key) {
                    if ($key != 'key' && $key != 'pay' && $key != 'sms') {
                        return $item;
                    }
                });

                //用户爱心值
                $is_love = app('plugins')->isEnabled('love');
                if ($is_love && $member_id) {
                    $memberLove = \Yunshop\Love\Common\Models\MemberLove::select('usable')->where('member_id', $member_id)->first();
                    if ($memberLove) {
                        $memberLove->toArray();
                    }
                }

                $result['memberinfo']['usable'] = empty($memberLove['usable']) ? 0 : $memberLove['usable'];
                // 获取页面对象
                $page = new \Yunshop\Decorate\frotend\IndexController();
                $pageType = \Yunshop::request()->type;
                //处理页面类型数据
                switch ($pageType) {
                    case '7':
                        $pageType = '3';
                        break;
                    case '8':
                        $pageType = '4';
                        break;
                    case '2':
                        $page->page_sort = "2";
                        break;
                    default:
                        $page->page_sort = '1';
                        break;
                }
                $page->page_type = $pageType;
                $page->page_scene = '1';
                $page_id = $pageId;
                if ($page_id) {
                    $decorate = $page->getPage($page_id, 1, $integrated); //按照ID获取
                } else {
                    $decorate = $page->getPage(false, 1, $integrated); //自动获取开启的页面（对应类型）
                }
                //给前端判断插件是否开启
                if (empty($decorate)) { //如果是请求首页的数据, 提供默认值
                    $result['default'] = self::defaultDesign();
                    $result['item']['is_decorate'] = 1;
                    $result['item']['data'] = ''; //前端需要该字段
                    $result['item']['menus'] = self::defaultMenu($i, $mid, $type);//菜单
                    $result['item']['menustyle'] = self::defaultMenuStyle();//菜单样式
                    $result['item']['topmenu'] = [
                        'menus' => [],
                        'params' => [],
                        'isshow' => false
                    ];
                } else {
                    $decorate['datas'] = json_decode($decorate['datas'], true);
                    $decorate['page_info'] = json_decode($decorate['page_info'], true);
                    //给前端判断是否新装修页面
                    $decorate['page_plugins'] = 'decorate';
                    $decorate['is_decorate'] = 1;
                    $result['item'] = $decorate;
                    $result['item']['menus'] = self::defaultMenu($i, $mid, $type);//菜单
                    $result['item']['pageinfo']['params']['checkitem'] = explode(",", $decorate['member_level']);
                    if ($decorate['member_level'] == '') {
                        $result['item']['pageinfo']['params']['checkitem'] = [];
                    }

                    //小程序是否每日首次登录
                    if (!$basic_info) {
                        $memberController = new MemberController;
                        $advertisement = $memberController->getFirstLogin('home');
                        $result['item']['pageinfo']['params']['advertisement'] = $advertisement;
                    }
                }

                $decorateTempletController = new \Yunshop\Decorate\admin\DecorateTempletController;
                $templet = $decorateTempletController->defaultTemplet();
                $result['item']['ViewSet'] = $templet;

            } elseif (app('plugins')->isEnabled('designer')) {

                $is_love = app('plugins')->isEnabled('love');
                if ($is_love) {
                    $love_basics_set = SetService::getLoveSet();//获取爱心值基础设置
                    $result['designer']['love_name'] = $love_basics_set['name'];
                }

                //系统信息
                // TODO
                if (!Cache::has('designer_system')) {
                    $result['system'] = (new \Yunshop\Designer\services\DesignerService())->getSystemInfo();
                    Cache::put('designer_system', $result['system'], 4200);
                } else {
                    $result['system'] = Cache::get('designer_system');
                }

                $page_id = $pageId;
                if ($page_id) {
                    $page = (new OtherPageService())->getOtherPage($page_id);
                } else {
                    $page = (new IndexPageService())->getIndexPage();
                }

                if ($page) {
                    //写入缓存组
                    $designer = Cache::tags(["designer_default_{$page->id}"])->get("{$member_id}");
                    if ($designer === null) {
                        $designer = (new \Yunshop\Designer\services\DesignerService())->getPageForHomePage($page->toArray());
                        Cache::tags(["designer_default_{$page->id}"])->put("{$member_id}", $designer, 3);
                    }

                    $designer = $this->addDynamicData($designer);


                    $result['item'] = $designer;
                    $result['item']['is_decorate'] = 0;
                    //给前端判断是否新装修页面
                    // $result['item']['page_plugins']='designer';

                    //顶部菜单 todo 加快进度开发，暂时未优化模型，装修数据、顶部菜单、底部导航等应该在一次模型中从数据库获取、编译 Y181031
                    if ($designer['pageinfo']['params']['top_menu'] && $designer['pageinfo']['params']['top_menu_id']) {
                        $result['item']['topmenu'] = (new PageTopMenuService())->getTopMenu($designer['pageinfo']['params']['top_menu_id']);
                    } else {
                        $result['item']['topmenu'] = [
                            'menus' => [],
                            'params' => [],
                            'isshow' => false
                        ];
                    }

                    $footerMenuType = $designer['footertype']; //底部菜单: 0 - 不显示, 1 - 显示系统默认, 2 - 显示选中的自定义菜单
                    $footerMenuId = $designer['footermenu'];
                } elseif (empty($pageId)) { //如果是请求首页的数据, 提供默认值
                    $result['default'] = self::defaultDesign();
                    $result['item']['data'] = ''; //前端需要该字段
                    $footerMenuType = 1;
                    $result['item']['topmenu'] = [
                        'menus' => [],
                        'params' => [],
                        'isshow' => false
                    ];
                } else { //如果是请求预览装修的数据
                    $result['item']['data'] = ''; //前端需要该字段
                    $footerMenuType = 0;
                    $result['item']['topmenu'] = [
                        'menus' => [],
                        'params' => [],
                        'isshow' => false
                    ];
                }
                //自定义菜单, 原来接口在  plugin.designer.home.index.menu
                switch ($footerMenuType) {
                    case 1:
                        $result['item']['menus'] = self::defaultMenu($i, $mid, $type);//菜单
                        $result['item']['menustyle'] = self::defaultMenuStyle();//菜单样式
                        break;
                    case 2:
                        if (!empty($footerMenuId)) {
                            if (!Cache::has("designer_menu_{$footerMenuId}")) {
                                $menustyle = DesignerMenu::getMenuById($footerMenuId);
                                Cache::put("designer_menu_{$footerMenuId}", $menustyle, 4200);
                            } else {
                                $menustyle = Cache::get("designer_menu_{$footerMenuId}");
                            }

                            if (!empty($menustyle->menus) && !empty($menustyle->params)) {
                                $result['item']['menus'] = json_decode($menustyle->toArray()['menus'], true);
                                $result['item']['menustyle'] = json_decode($menustyle->toArray()['params'], true);
                                //判断是否是商城外部链接
                                foreach ($result['item']['menus'] as $key => $value) {
                                    if (!strexists($value['url'], 'addons/yun_shop/')) {
                                        $result['item']['menus'][$key]['is_shop'] = 1;
                                    } else {
                                        $result['item']['menus'][$key]['is_shop'] = 0;
                                    }
                                }
                            } else {
                                $result['item']['menus'] = self::defaultMenu($i, $mid, $type);
                                $result['item']['menustyle'] = self::defaultMenuStyle();
                            }
                        } else {
                            $result['item']['menus'] = self::defaultMenu($i, $mid, $type);
                            $result['item']['menustyle'] = self::defaultMenuStyle();
                        }
                        break;
                    default:
                        $result['item']['menus'] = false;
                        $result['item']['menustyle'] = false;
                }

                // 首页优化 获取首页列表数据（避免多次访问接口造成资源消耗）
                $home_page = new HomePage();
                $result['item']['data'] = $home_page->getList($result['item']['data']);

                if (!empty($result['item']['data'])) {
                    foreach ($result['item']['data'] as $kk => $vv) {
                        if ($vv['temp'] == "goods") {
                            if (!empty($vv['data'])) {
                                foreach ($vv['data'] as $k => $v) {
                                    if ($v['plugin_id'] == 69 && app('plugins')->isEnabled('snatch-regiment')) {

                                        $snatch_goods = SnatchGoods::getSnatchGoods(["goods_id" => $v['goodid']])->where("yz_goods.status", 1)->orderBy("id", "desc")->get();
                                        $snatch_goods = empty($snatch_goods) ? [] : $snatch_goods->toArray();
                                        if (!empty($snatch_goods[0])) {
                                            $price = CommonService::returnGoodsPrice($snatch_goods[0]);
                                            if ($price > 0) {
                                                $result['item']['data'][$kk]['data'][$k]['pricenow'] = $price;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($page_id) {
                    $result['memberDesigner'] = (new MemberController())->designer($request, true, $page_id)['json'];
                }
            } elseif (empty($pageId)) {  //如果是请求首页的数据, 但是没有安装"装修插件"或者"装修插件"没有开启, 则提供默认值
                $result['default'] = self::defaultDesign();
                $result['item']['menus'] = self::defaultMenu($i, $mid, $type);
                $result['item']['menustyle'] = self::defaultMenuStyle();
                $result['item']['data'] = ''; //前端需要该字段
                $result['item']['topmenu'] = [
                    'menus' => [],
                    'params' => [],
                    'isshow' => false
                ];
            }


            //增加验证码功能
            $status = Setting::get('shop.sms.status');
            if (extension_loaded('fileinfo')) {
                if ($status == 1) {
                    $captcha = self::captchaTest();
                    $result['captcha'] = $captcha;
                    $result['captcha']['status'] = $status;
                }
            }
            $result['system']['mobile_login_code'] = $member_set['mobile_login_code'] ? 1 : 0;
            //小程序验证推广按钮是否开启
            $result['system']['btn_romotion'] = PortType::popularizeShow($type);

            $allow_auth = $result['item']['pageinfo']['params']['checkitem'];//允许登录的用户等级
            $member_service = new MemberLevelAuth();
            $auth = $member_service->doAuth($member_id, $allow_auth);
            if (!$auth) {
                $result['item']['pageinfo']['params']['allowauth'] = 0;
            } else {
                $result['item']['pageinfo']['params']['allowauth'] = 1;
            }

            $result['plugin']['new_member_prize'] = [];
            if (app('plugins')->isEnabled('new-member-prize') && $basic_info == 1) {
                $result['plugin']['new_member_prize'] = (new NewMemberPrizeController())->index($type);
            }

            //商品展示组件
            $result['plugin']['goods_show'] = [];
            if (app('plugins')->isEnabled('goods-show')) {
                $result['plugin']['goods_show']['goods_group'] = \Setting::get('plugin.goods_show.goods_group');
                $result['plugin']['goods_show']['around_goods'] = \Setting::get('plugin.goods_show.around_goods');
            }

            //会员注册设置
            $register_set = Setting::get('shop.register');
            $shop = Setting::get('shop.shop');

            if ($shop['is_agreement']) {
                $register_set['new_agreement'] = RichText::get('shop.agreement');
                $register_set['agreement_name'] = $shop['agreement_name'];
            }
            $result['register_setting'] = $register_set;

            (new CollectHostService(request()->getHttpHost()))->handle();

            if (is_null($integrated)) {
                if ($basic_info == '1') {
                    $result['page_type'] = 'home';
                }
                return $this->successJson('ok', $result);
            } else {
                return show_json(1, $result);
            }
        } catch (MemberNotLoginException $e) {
            if ($scope == 'pass') {
                $service = [];
                foreach ((new ServiceController())->index() as $k => $v) {
                    $service[$k] = $v;
                }
                return show_json(1, ['mailInfo' => array_merge(['credit1' => 0], $service), 'item' => ['data' => []]]);
            }

            throw (new MemberNotLoginException($e->getMessage(), $e->getData()));

        }
    }

    public function index(Request $request, $integrated = null)
    {
        if (!miniVersionCompare('1.1.88') || !versionCompare('1.1.084')) {
            return $this->oldIndex($request, $integrated);
        }
        app('db')->cacheSelect = true;
        $scope = request()->scope;
        try {
            // 获取对应的装修服务
            $data_service = ShopPublicDataService::getInstance();
            // 获取首页数据
            $result = $data_service->getIndexData();
            return $this->successJson('ok', $result);
        } catch (MemberNotLoginException $e) {
            if ($scope == 'pass') {
                return show_json(1, ['mailInfo' => ['credit1' => 0], 'item' => ['data' => []]]);
            }
            throw (new MemberNotLoginException($e->getMessage(), $e->getData()));
        }
    }


    private function designerGoodsData($designer)
    {
        foreach ($designer['data'] as $data) {
            if ($data['temp'] == 'goods') {
                foreach ($data['data'] as &$goode_award) {
                    $goodsIds[] = $goode_award['goodid'];
                }
            }
        }
        if (!$goodsIds) {
            return $designer;
        }

        $goodsCollection = Goods::select(['id', 'stock'])->whereIn('id', $goodsIds)->getQuery()->get();
        $goodsLoveCollection = GoodsLove::select(['goods_id', 'award'])->whereIn('goods_id', $goodsIds)->getQuery()->get();
        foreach ($designer['data'] as &$data) {
            if ($data['temp'] == 'goods') {
                foreach ($data['data'] as &$goode_award) {
                    $goode_award['stock'] = array_get($goodsCollection->where('id', $goode_award['goodid'])->first(), 'stock', 0);
                    $goode_award['award'] = array_get($goodsLoveCollection->where('goods_id', $goode_award['goodid'])->first(), 'award', 0);

                }
            }
        }

        return $designer;
    }

    private function addDynamicData($designer)
    {
        $shop = Setting::get('shop.shop')['credit1'] ?: '积分';
        if (app('plugins')->isEnabled('love')) {
            $designer = $this->designerGoodsData($designer);
        }
        foreach ($designer['data'] as $k => &$data) {
            if (app('plugins')->isEnabled('love')) {
                //替换积分字样
                if ($data['temp'] == 'sign') {
                    $data['params']['award_content'] = str_replace('积分', $shop, $data['params']['award_content']);
                }
                if ($data['temp'] == 'goods') {
                    $love_basics_set = SetService::getLoveSet();//获取爱心值基础设置

                    $goods_ids = array_column($data['data'], 'goodid');
                    $goodsIdList = GoodsLove::select('award_proportion', 'goods_id')
                        ->where('uniacid', \Yunshop::app()->uniacid)
                        ->whereIn('goods_id', $goods_ids)
                        ->get()->toArray();
                    $goodsIdList = array_column($goodsIdList, null, 'goods_id');

                    foreach ($data['data'] as $key => &$goods) {
                        if (!empty($goodsIdList[$goods['goodid']]) && $goodsIdList[$goods['goodid']]['award_proportion'] <> 0) {
                            $goods['award_proportion'] = $goodsIdList[$goods['goodid']]['award_proportion'];
                        } else {
                            $goods['award_proportion'] = $love_basics_set['award_proportion'];//爱心值比例
                        }
                    }


                    //todo 优化嵌套循环语句
//                    $goods_ids = [];
//                    foreach ($data['data'] as $key => &$goods) {
//                        $goods_ids[] = $goods['goodid'];
//                        $goodsIdList = $this->getNewLoveGoods($goods['goodid']);//获取单个商品爱心值
//                        if ($goodsIdList['award_proportion'] == 0) {
//                            $goods['award_proportion'] = $love_basics_set['award_proportion'];//爱心值比例
//                        } else {
//                            $goods['award_proportion'] = $goodsIdList['award_proportion'];
//                        }
//                    }

                    if (count($goods_ids) >= 1) {
                        $goodsInfo = $this->getGoodsModelByIds($goods_ids);
                        foreach ($data['data'] as $keyTwo => &$valueTwo) {
                            foreach ($goodsInfo as $key => $goodsModel) {
                                if ($valueTwo['goodid'] == $goodsModel['id']) {
                                    $valueTwo['vip_price'] = $goodsModel['vip_price'];
                                    $valueTwo['priceold'] = $goodsModel['market_price'];
                                    $valueTwo['pricenow'] = $goodsModel['price'];
                                    $valueTwo['name'] = $goodsModel['title'];
                                    $valueTwo['sales'] = $goodsModel['virtual_sales'] + $goodsModel['show_sales'];//实时获取销量
                                }
                            }
                        }

                    }
                }
            } else {
                //替换积分字样
                if ($data['temp'] == 'sign') {
                    foreach ($data['params'] as &$award_content) {
                        if (strpos($award_content, '积分') !== false) {
                            $award_content = str_replace('积分', $shop, $data['params']['award_content']);
                        }
                        //$data['params']['award_content'] = str_replace('积分', $shop, $data['params']['award_content']);
                    }
                }
                if ($data['temp'] == 'goods') {
                    $goods_ids = [];
                    foreach ($data['data'] as $key => $goods) {
                        $goods_ids[] = $goods['goodid'];
                    }
                    if (count($goods_ids) >= 1) {
                        $goodsInfo = $this->getGoodsModelByIds($goods_ids);
                        foreach ($data['data'] as $keyTwo => &$valueTwo) {
                            foreach ($goodsInfo as $key => $goodsModel) {
                                if ($valueTwo['goodid'] == $goodsModel['id']) {
                                    $valueTwo['unit'] = $goodsModel['sku'];
                                    $valueTwo['vip_price'] = $goodsModel['vip_price'];
                                    $valueTwo['priceold'] = $goodsModel['market_price'];
                                    $valueTwo['pricenow'] = $goodsModel['price'];
                                    $valueTwo['name'] = $goodsModel['title'];
                                    $valueTwo['award'] = 0;
                                    $valueTwo['sales'] = $goodsModel['virtual_sales'] + $goodsModel['show_sales'];//实时获取销量
                                }
                            }
                        }
                    }
                }
            }
            if ($data['temp'] == 'nearbygoods') {
                if (app('plugins')->isEnabled('nearby-store-goods')) {
                    $set = \Setting::get('nearby-store-goods.is_open');
                    if ($set == 1) {
                        $nearService = new DesignerController();
                        $data['get_info'] = $nearService->getGoods(request(), true, $data['params']['displaynum'])['json'];
                    }
                }
            }
            if ($data['temp'] == 'diyform') {
                if (!app('plugins')->isEnabled('diyform')) {
                    unset($designer['data'][$k]);
                } else {
                    $getInfo = (new DiyFormController())->getDiyFormTypeMemberData('', true, $data['data']['form_id']);
                    $data['get_info'] = $getInfo['status'] == 1 ? $getInfo['json'] : [];
                }
            }
            if ($data['temp'] == 'coupon') {
                $getInfo = (new MemberCouponController())->couponsForDesigner('', true);
                $data['get_info'] = $getInfo['status'] == 1 ? $getInfo['json'] : [];
            }
            if ($data['temp'] == 'store') {
                if (!app('plugins')->isEnabled('store-cashier')) {
                    unset($designer['data'][$k]);
                }
            }

            if ($data['temp'] == 'picturesque') {
                $designer['data'][$k]['params']['imageOne'] = yz_tomedia($designer['data'][$k]['params']['imageOne']);
                $designer['data'][$k]['params']['imageTwo'] = yz_tomedia($designer['data'][$k]['params']['imageTwo']);
                $designer['data'][$k]['params']['imageThree'] = yz_tomedia($designer['data'][$k]['params']['imageThree']);
                $designer['data'][$k]['params']['imageFour'] = yz_tomedia($designer['data'][$k]['params']['imageFour']);
            }
        }
        $designer['data'] = array_values($designer['data']);
        return $designer;
    }

    public function designerShare()
    {
        $i = \YunShop::request()->i;
        $mid = \YunShop::request()->mid;
        $type = \YunShop::request()->type;
        $pageId = (int)\YunShop::request()->page_id ?: 0;
        $member_id = \YunShop::app()->getMemberId();

        if (app('plugins')->isEnabled('designer')) {
            //系统信息
            // TODO
            if (!Cache::has('designer_system')) {
                $result['system'] = (new \Yunshop\Designer\services\DesignerService())->getSystemInfo();

                Cache::put('designer_system', $result['system'], 4200);
            } else {
                $result['system'] = Cache::get('designer_system');
            }

            $page_id = $pageId;
            if ($page_id) {
                $page = (new OtherPageService())->getOtherPage($page_id);
            } else {
                $page = (new IndexPageService())->getIndexPage();
            }

            if ($page) {
                if (empty($pageId) && Cache::has($member_id . '_designer_default_0')) {
                    $designer = Cache::get($member_id . '_designer_default_0');
                } else {
                    $designer = (new \Yunshop\Designer\services\DesignerService())->getPageForHomePage($page->toArray());
                }

                if (empty($pageId) && !Cache::has($member_id . '_designer_default_0')) {
                    Cache::put($member_id . '_designer_default_0', $designer, 180);
                }

                $result['item'] = $designer;

                //顶部菜单 todo 加快进度开发，暂时未优化模型，装修数据、顶部菜单、底部导航等应该在一次模型中从数据库获取、编译 Y181031
                if ($designer['pageinfo']['params']['top_menu'] && $designer['pageinfo']['params']['top_menu_id']) {
                    $result['item']['topmenu'] = (new PageTopMenuService())->getTopMenu($designer['pageinfo']['params']['top_menu_id']);
                } else {
                    $result['item']['topmenu'] = [
                        'menus' => [],
                        'params' => [],
                        'isshow' => false
                    ];
                }


                $footerMenuType = $designer['footertype']; //底部菜单: 0 - 不显示, 1 - 显示系统默认, 2 - 显示选中的自定义菜单
                $footerMenuId = $designer['footermenu'];
            } elseif (empty($pageId)) { //如果是请求首页的数据, 提供默认值
                $result['default'] = self::defaultDesign();
                $result['item']['data'] = ''; //前端需要该字段
                $footerMenuType = 1;
                $result['item']['topmenu'] = [
                    'menus' => [],
                    'params' => [],
                    'isshow' => false
                ];
            } else { //如果是请求预览装修的数据
                $result['item']['data'] = ''; //前端需要该字段
                $footerMenuType = 0;
                $result['item']['topmenu'] = [
                    'menus' => [],
                    'params' => [],
                    'isshow' => false
                ];
            }
            //自定义菜单, 原来接口在  plugin.designer.home.index.menu
            switch ($footerMenuType) {
                case 1:
                    $result['item']['menus'] = self::defaultMenu($i, $mid, $type);//菜单
                    $result['item']['menustyle'] = self::defaultMenuStyle();//菜单样式
                    break;
                case 2:
                    if (!empty($footerMenuId)) {
                        if (!Cache::has("designer_menu_{$footerMenuId}")) {
                            $menustyle = DesignerMenu::getMenuById($footerMenuId);
                            Cache::put("designer_menu_{$footerMenuId}", $menustyle, 4200);
                        } else {
                            $menustyle = Cache::get("designer_menu_{$footerMenuId}");
                        }

                        if (!empty($menustyle->menus) && !empty($menustyle->params)) {
                            $result['item']['menus'] = json_decode($menustyle->toArray()['menus'], true);
                            $result['item']['menustyle'] = json_decode($menustyle->toArray()['params'], true);
                            //判断是否是商城外部链接
                            foreach ($result['item']['menus'] as $key => $value) {
                                if (!strexists($value['url'], 'addons/yun_shop/')) {
                                    $result['item']['menus'][$key]['is_shop'] = 1;
                                } else {
                                    $result['item']['menus'][$key]['is_shop'] = 0;
                                }
                            }
                        } else {
                            $result['item']['menus'] = self::defaultMenu($i, $mid, $type);
                            $result['item']['menustyle'] = self::defaultMenuStyle();
                        }
                    } else {
                        $result['item']['menus'] = self::defaultMenu($i, $mid, $type);
                        $result['item']['menustyle'] = self::defaultMenuStyle();
                    }
                    break;
                default:
                    $result['item']['menus'] = false;
                    $result['item']['menustyle'] = false;
            }
        } elseif (empty($pageId)) { //如果是请求首页的数据, 但是没有安装"装修插件"或者"装修插件"没有开启, 则提供默认值
            $result['default'] = self::defaultDesign();
            $result['item']['menus'] = self::defaultMenu($i, $mid, $type);
            $result['item']['menustyle'] = self::defaultMenuStyle();
            $result['item']['data'] = ''; //前端需要该字段
            $result['item']['topmenu'] = [
                'menus' => [],
                'params' => [],
                'isshow' => false
            ];
        }
        return $this->successJson('ok', $result);
    }

    public function getLoveGoods($goods_id)
    {
        $goodsModel = GoodsLove::select('award')->where('uniacid', \Yunshop::app()->uniacid)->where('goods_id', $goods_id)->first();

        $goods = $goodsModel ? $goodsModel->toArray()['award'] : 0;
        return $goods;
    }

    public function getMemberGoodsStock($goods_id)
    {
        $goodsModel = Goods::select('stock')->where('uniacid', \Yunshop::app()->uniacid)->where('id', $goods_id)->first();
        $stock = $goodsModel ? $goodsModel->stock : 0;
        return $stock;
    }

    private function getGoodsStock($goods_id)
    {
        $goodsModel = Goods::select('stock')->where('uniacid', \Yunshop::app()->uniacid)->where('id', $goods_id)->first();
        $stock = $goodsModel ? $goodsModel->stock : 0;
        return $stock;
    }

    /*
     * 获取分页数据
     */
    public function GetPageGoods()
    {
        if (app('plugins')->isEnabled('designer')) {
            $group_id = \YunShop::request()->group_id;
            $group_goods = new GoodsGroupGoods();
            $data = $group_goods->GetPageGoods($group_id);
            $datas = $data->paginate(12)
                ->toArray();

            $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
            $goods_model = new $goods_model;

            foreach ($datas['data'] as $key => $itme) {
                $datas['data'][$key] = unserialize($itme['goods']);//反序列化
                $rGoods = $goods_model->select()
                    ->where('id', $datas['data'][$key]['goodid'])
                    ->first();
                $datas['data'][$key]['vip_price'] = $rGoods->vip_price;
                $datas['data'][$key]['vip_next_price'] = $rGoods->vip_next_price;
                $datas['data'][$key]['sales'] = $rGoods->virtual_sales + $rGoods->show_sales;
                $datas['data'][$key]['vip_level_status'] = $rGoods->vip_level_status;
                $datas['data'][$key]['price_level'] = $rGoods->price_level;
                $datas['data'][$key]['name'] = $rGoods->title;
                $datas['data'][$key]['plugin_id'] = $rGoods->plugin_id;
                if ($rGoods->plugin_id == 69 && app('plugins')->isEnabled('snatch-regiment')) {
                    $snatch_goods = SnatchGoods::getSnatchGoods(["goods_id" => $rGoods->id])->where("yz_goods.status", 1)->orderBy("id", "desc")->get();
                    $snatch_goods = empty($snatch_goods) ? [] : $snatch_goods->toArray();
                    if (!empty($snatch_goods[0])) {
                        $price = CommonService::returnGoodsPrice($snatch_goods[0]);
                        if ($price > 0) {
                            $datas['data'][$key]['pricenow'] = $price;
                        }
                    }
                }
            }
            return $this->successJson('ok', $datas);
        }
    }

    /**
     * 装修2.0 获取分页数据
     * @param 装修ID decorate_id
     * @param 组件ID component_id
     */
    public function getDecoratePage()
    {
        if (!app('plugins')->isEnabled('decorate') || \Setting::get('plugin.decorate.is_open') != "1") {
            return $this->errorJson('装修插件未开启');
        }

        $decorateId = \YunShop::request()->decorate_id;         //装修ID
        $componentId = \YunShop::request()->component_id;       //组件ID
        $componentKey = \YunShop::request()->component_key;     //组件名称
        $componentInfo = \YunShop::request()->component_info;   //组件参数

        if (!$decorateId || !$componentId || !$componentKey) {
            return $this->errorJson('缺少必要参数');
        }

        $className = ucfirst(str_replace('U_', '', $componentKey));
        $classNamespace = "\Yunshop\Decorate\common\services\component\\";

        // 判断类是否存在（类名需要对应组件名）
        if (class_exists($classNamespace . $className)) {
            $myclass = new \ReflectionClass($classNamespace . $className); //获取组件对应的类
            $myclass = $myclass->newInstance($commonData);
            $list = $myclass->pageList();  //获取组件的分页数据
        }

        if (!$list) {
            return $this->errorJson('error');
        }

        return $this->successJson('success', $list);
    }

    //增加验证码功能
    public function captchaTest()
    {
        $captcha = app('captcha');
        $captcha_base64 = $captcha->create('default', true);
        return $captcha_base64;
    }

    /**
     * 原生小程序首页装修接口
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function wxapp(Request $request)
    {
        return $this->index($request);
    }

    /**
     * @return array 默认的首页元素(轮播图 & 商品 & 分类 & 商城设置)
     */
    public static function defaultDesign()
    {
        if (!Cache::has('shop_category')) {
            $set = Setting::get('shop.category');

            Cache::put('shop_category', $set, 4200);
        } else {
            $set = Cache::get('shop_category');
        }

        $set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));

        if (!Cache::has('shop_default_design')) {
            $result = array(
                'ads' => (new IndexController())->getAds(),
                'advs' => (new IndexController())->getAdv(),
                'brand' => (new IndexController())->getRecommentBrandList(),
                'category' => (new IndexController())->getRecommentCategoryList(),
                'time_goods' => (new IndexController())->getTimeLimitGoods(),
                'set' => $set,
                'goods' => (new IndexController())->getRecommentGoods(),
            );
            Cache::put('shop_default_design', $result, 1);

        } else {
            $result = Cache::get('shop_default_design');
        }
        return $result;
    }


    /**
     * @param $i 公众号ID
     * @param $mid 上级的uid
     * @param $type
     *
     * @return array 默认的底部菜单数据
     */
    public static function defaultMenu($i, $mid, $type)
    {
        app('plugins')->isEnabled('designer') ? $CustomizeMenu = DesignerMenu::getDefaultMenu() : null;

        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
            $CustomizeMenu = null;

            // 获取页面对象
            $page = new \Yunshop\Decorate\frotend\IndexController();

            //处理页面类型数据
            switch (\Yunshop::request()->type) {
                case '2':
                    $page->page_sort = "2";
                    break;
                default:
                    $page->page_sort = '1';
                    break;
            }
            $page->page_scene = '1';
            $defaultFooter = $page->getDefaultFooter();

            if (!$defaultFooter) {
                return "";
            }
        }

        if (!empty($defaultFooter)) {
            $Menu = $defaultFooter;
        } elseif (!empty($CustomizeMenu)) {
            $CustomizeMenu_list = $CustomizeMenu->toArray();

            if (is_array($CustomizeMenu_list) && !empty($CustomizeMenu_list['menus'])) {
                $Menu = json_decode(htmlspecialchars_decode($CustomizeMenu['menus']), true);
                foreach ($Menu as $key => $value) {
                    // $Menu[$key]['name']=$Menu[$key]['id'];
                    // $url = substr($Menu[$key]['url'],strripos($Menu[$key]['url'],"addons")-1);
                    if (strripos($Menu[$key]['url'], "addons") != false) {
                        $url = substr($Menu[$key]['url'], strripos($Menu[$key]['url'], "addons") - 1);
                    } else {
                        $url = $Menu[$key]['url'];
                    }

                    $Menu[$key]['url'] = $url ?: '';
                    //$Menu[$key]['url'] ="/addons/yun_shop/".'?#'.substr($Menu[$key]['url'],strripos($Menu[$key]['url'],"#/")+1)."&mid=" . $mid . "&type=" . $type;
                }
            }
        } else {
            //默认菜单
            $Menu = array(
                array(
                    "id" => 1,
                    "title" => "首页",
                    "icon" => "fa fa-home",
                    "url" => "/addons/yun_shop/?#/home?i=" . $i . "&mid=" . $mid . "&type=" . $type,
                    "name" => "home",
                    "subMenus" => [],
                    "textcolor" => "#70c10b",
                    "bgcolor" => "#24d7e6",
                    "bordercolor" => "#bfbfbf",
                    "iconcolor" => "#666666"
                ),
                array(
                    "id" => "menu_1489731310493",
                    "title" => "分类",
                    "icon" => "fa fa-th-large",
                    "url" => "/addons/yun_shop/?#/category?i=" . $i . "&mid=" . $mid . "&type=" . $type,
                    "name" => "category",
                    "subMenus" => [],
                    "textcolor" => "#70c10b",
                    "bgcolor" => "#24d7e6",
                    "iconcolor" => "#666666",
                    "bordercolor" => "#bfbfbf"
                ),
                array(
                    "id" => "menu_1489735163419",
                    "title" => "购物车",
                    "icon" => "fa fa-cart-plus",
                    "url" => "/addons/yun_shop/?#/cart?i=" . $i . "&mid=" . $mid . "&type=" . $type,
                    "name" => "cart",
                    "subMenus" => [],
                    "textcolor" => "#70c10b",
                    "bgcolor" => "#24d7e6",
                    "iconcolor" => "#666666",
                    "bordercolor" => "#bfbfbf"
                ),
                array(
                    "id" => "menu_1491619644306",
                    "title" => "会员中心",
                    "icon" => "fa fa-user",
                    "url" => "/addons/yun_shop/?#/member?i=" . $i . "&mid=" . $mid . "&type=" . $type,
                    "name" => "member",
                    "subMenus" => [],
                    "textcolor" => "#70c10b",
                    "bgcolor" => "#24d7e6",
                    "iconcolor" => "#666666",
                    "bordercolor" => "#bfbfbf"
                ),
            );
            $promoteMenu = array(
                "id" => "menu_1489731319695",
                "classt" => "no",
                "title" => "推广",
                "icon" => "fa fa-send",
                "url" => "/addons/yun_shop/?#/member/extension?i=" . $i . "&mid=" . $mid . "&type=" . $type,
                "name" => "extension",
                "subMenus" => [],
                "textcolor" => "#666666",
                "bgcolor" => "#837aef",
                "iconcolor" => "#666666",
                "bordercolor" => "#bfbfbf"
            );
            $extension_status = Setting::get('shop_app.pay.extension_status');
            if (isset($extension_status) && $extension_status == 0) {
                $extension_status = 0;
            } else {
                $extension_status = 1;
            }
            if ($type == 7 && $extension_status == 0) {
                unset($promoteMenu);
            } else {
                //是否显示推广按钮
                if (PortType::popularizeShow($type)) {
                    $Menu[4] = $Menu[3]; //第 5 个按钮改成"会员中心"
                    $Menu[3] = $Menu[2]; //第 4 个按钮改成"购物车"
                    $Menu[2] = $promoteMenu; //在第 3 个按钮的位置加入"推广"
                }
            }
        }

        //如果开启了"会员关系链", 则默认菜单里面添加"推广"菜单
        /*
        if(Cache::has('member_relation')){
            $relation = Cache::get('member_relation');
        } else {
            $relation = MemberRelation::getSetInfo()->first();
        }
        */
        //if($relation->status == 1){

        return $Menu;


    }

    /**
     * @return array 默认的底部菜单样式
     */
    public static function defaultMenuStyle()
    {
        return array(
            "previewbg" => "#ef372e",
            "height" => "49px",
            "textcolor" => "#666666",
            "textcolorhigh" => "#ff4949",
            "iconcolor" => "#666666",
            "iconcolorhigh" => "#ff4949",
            "bgcolor" => "#FFF",
            "bgcolorhigh" => "#FFF",
            "bordercolor" => "#010101",
            "bordercolorhigh" => "#bfbfbf",
            "showtext" => 1,
            "showborder" => "0",
            "showicon" => 2,
            "textcolor2" => "#666666",
            "bgcolor2" => "#fafafa",
            "bordercolor2" => "#1856f8",
            "showborder2" => 1,
            "bgalpha" => ".5",
        );
    }

    public function bindMobile()
    {

        $member_id = \YunShop::app()->getMemberId();
        \Log::info('member_id', $member_id);
        //强制绑定手机号
        if (Cache::has('shop_member')) {
            $member_set = Cache::get('shop_member');
            \Log::info('member_set-1', $member_set);
        } else {
            $member_set = Setting::get('shop.member');
            \Log::info('member_set-2', $member_set);
        }
        //        $is_bind_mobile = 0;
        //
        //        if (!is_null($member_set)) {
        //            if ((1 == $member_set['is_bind_mobile']) && $member_id && $member_id > 0) {
        //                if (Cache::has($member_id . '_member_info')) {
        //                    $member_model = Cache::get($member_id . '_member_info');
        //                } else {
        //                    $member_model = Member::getMemberById($member_id);
        //                }
        //
        //                if ($member_model && empty($member_model->mobile)) {
        //                    $is_bind_mobile = 1;
        //                }
        //            }
        //        }

        $is_bind_mobile = 0;

        if (!is_null($member_set)) {
            \Log::info('not_null_member_set', [$member_set]);
            if ((0 < $member_set['is_bind_mobile']) && $member_id && $member_id > 0) {
                \Log::info('0 < $member_set[is_bind_mobile]) && $member_id && $member_id > 0', [$member_set['is_bind_mobile'], $member_id]);

                if (Cache::has($member_id . '_member_info')) {
                    $member_model = Cache::get($member_id . '_member_info');
                    \Log::info('$member_model-1', $member_model);
                } else {
                    $member_model = Member::getMemberById($member_id);
                    \Log::info('$member_model-2', $member_model);
                }

                if ($member_model && empty($member_model->mobile)) {
                    \Log::info('$member_model && empty($member_model->mobile)', [$member_model, $member_model->mobile]);
                    $is_bind_mobile = intval($member_set['is_bind_mobile']);
                }
            }
        }
        if (\YunShop::request()->invite_code) {
            \Log::info('绑定手机号填写邀请码');
            //分销关系链
            \app\common\models\Member::createRealtion($member_id);
        }

        $result['is_bind_mobile'] = $is_bind_mobile;

        return $this->successJson('ok', $result);
    }

    public function isCloseSite()
    {
        $shop = Setting::get('shop.shop');
        $code = 0;

        if (isset($shop) && isset($shop['close']) && 1 == $shop['close']) {
            $code = -1;
        }

        return $this->successJson('ok', ['code' => $code]);
    }

    public function isBindMobile($member_set, $member_id)
    {
        $is_bind_mobile = 0;

        if ((0 < $member_set['is_bind_mobile']) && $member_id && $member_id > 0) {
            if (Cache::has($member_id . '_member_info')) {
                $member_model = Cache::get($member_id . '_member_info');
            } else {
                $member_model = Member::getMemberById($member_id);
            }

            if ($member_model && empty($member_model->mobile)) {
                $is_bind_mobile = intval($member_set['is_bind_mobile']);
            }
        }
        return $is_bind_mobile;
    }

    public function isValidatePage(Request $request, $integrated = null)
    {
        $member_id = \YunShop::app()->getMemberId();

        //强制绑定手机号
        if (Cache::has('shop_member')) {
            $member_set = Cache::get('shop_member');
        } else {
            $member_set = \Setting::get('shop.member');
        }

        if (!is_null($member_set)) {
            $data = [
                'is_bind_mobile' => $this->isBindMobile($member_set, $member_id),
                'invite_page' => 0,
                'is_invite' => 0,
                'is_login' => 0
            ];

            if ($data['is_bind_mobile']) {
                if (is_null($integrated)) {
                    return $this->successJson('强制绑定手机开启', $data);
                } else {
                    return show_json(1, $data);
                }
            }

            $type = \YunShop::request()->type;
            $invitation_log = [];
            if ($member_id) {
                if (Member::current()->mobile) {
                    $invitation_log = 1;
                } else {
                    $member = Member::current()->yzMember;
                    $invitation_log = MemberInvitationCodeLog::uniacid()->where('member_id', $member_id)->where('mid', $member->parent_id)->first();
                }
            }

            $invite_page = $member_set['invite_page'] ? 1 : 0;
            $data['invite_page'] = $type == 5 ? 0 : $invite_page;

            $data['is_invite'] = $invitation_log ? 1 : 0;
            $data['is_login'] = $member_id ? 1 : 0;

            if (is_null($integrated)) {
                return $this->successJson('邀请页面开关', $data);
            } else {
                return show_json(1, $data);
            }
        }

        return show_json(1, []);
    }

    public function getBalance()
    {
        $shop = \Setting::get('shop.shop');
        $credit = $shop['credit'] ?: '余额';

        return show_json(1, ['balance' => $credit]);
    }

    public function getLangSetting()
    {
        $lang = \Setting::get('shop.lang.lang');

        $data = [
            'test' => [],
            'commission' => [
                'title' => '',
                'commission' => '',
                'agent' => '',
                'level_name' => '',
                'commission_order' => '',
                'commission_amount' => '',
            ],
            'single_return' => [
                'title' => '',
                'single_return' => '',
                'return_name' => '',
                'return_queue' => '',
                'return_log' => '',
                'return_detail' => '',
                'return_amount' => '',
            ],
            'team_return' => [
                'title' => '',
                'team_return' => '',
                'return_name' => '',
                'team_level' => '',
                'return_log' => '',
                'return_detail' => '',
                'return_amount' => '',
                'return_rate' => '',
                'team_name' => '',
                'return_time' => '',
            ],
            'full_return' => [
                'title' => '',
                'full_return' => '',
                'return_name' => '',
                'full_return_log' => '',
                'return_detail' => '',
                'return_amount' => '',
            ],
            'team_dividend' => [
                'title' => '',
                'team_dividend' => '',
                'team_agent_centre' => '',
                'dividend' => '',
                'flat_prize' => '',
                'award_gratitude' => '',
                'dividend_amount' => '',
                'my_agent' => '',
            ],
            'area_dividend' => [
                'area_dividend_center' => '',
                'area_dividend' => '',
                'dividend_amount' => '',
            ],
            'income' => [
                'name_of_withdrawal' => '提现',
                'income_name' => '收入',
                'poundage_name' => '手续费',
                'special_service_tax' => '劳务税',
                'manual_withdrawal' => '手动提现',
            ],
            'agent' => [
                'agent' => '',
                'agent_num' => '',
                'agent_count' => '',
                'agent_order' => '',
                'agent_order_count' => '',
                'agent_goods_num' => '',
            ],
            'merchant' => [
                'title' => '招商',
                'merchant_people' => '招商员',
                'merchant_center' => '招商中心',
                'merchant_reward' => '分红',

            ]
        ];

        $langData = \Setting::get('shop.lang.' . $lang, $data);

        if (is_null($langData)) {
            $langData = $data;
        }
        if ($langData['income']['name_of_withdrawal'] == '') {
            $langData['income']['name_of_withdrawal'] = '提现';
        }
        if ($langData['income']['income_name'] == '') {
            $langData['income']['income_name'] = '收入';
        }
        if ($langData['income']['poundage_name'] == '') {
            $langData['income']['poundage_name'] = '手续费';
        }
        if ($langData['income']['special_service_tax'] == '') {
            $langData['income']['special_service_tax'] = '劳务税';
        }
        if ($langData['income']['manual_withdrawal'] == '') {
            $langData['income']['manual_withdrawal'] = '手动打款';
        }
        if (!$langData['appointment']['project']) {
            $langData['appointment']['project'] = '项目';
        }
        if (!$langData['appointment']['service']) {
            $langData['appointment']['service'] = '服务';
        }
        if (!$langData['appointment']['worker']) {
            $langData['appointment']['worker'] = '技师';
        }
        if (!$langData['merchant']['title']) $langData['merchant']['title'] = '招商';
        if (!$langData['merchant']['merchant_people']) $langData['merchant']['merchant_people'] = '招商员';
        if (!$langData['merchant']['merchant_center']) $langData['merchant']['merchant_center'] = '招商中心';
        if (!$langData['merchant']['merchant_reward']) $langData['merchant']['merchant_reward'] = '分红';


        if (app('plugins')->isEnabled('micro')) {
            $title = Setting::get('plugin.micro');
            $langData['micro']['title'] = $title['micro_title'] ?: '微店';
        }

        $langData['plugin_language'] = \app\common\services\LangService::getCurrentLang();

        return show_json(1, $langData);
    }

    protected function moRen()
    {
        return [
            'wechat' => [
                'vue_route' => [],
                'url' => '',
            ],
            'mini' => [
                'vue_route' => [],
                'url' => '',
            ],
            'wap' => [
                'vue_route' => [],
                'url' => '',
            ],
            'app' => [
                'vue_route' => [],
                'url' => '',
            ],
            'alipay' => [
                'vue_route' => [],
                'url' => '',
            ],
        ];
    }

    public function wxJsSdkConfig()
    {
        $member = \Setting::get('shop.member');

        if (isset($member['wechat_login_mode']) && 1 == $member['wechat_login_mode']) {
            return show_json(1, []);
        }

        $url = \YunShop::request()->url;
        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
        if (!$account->key) {
            return show_json(1, []);
        }
        $options = [
            'app_id' => $account->key,
            'secret' => $account->secret
        ];

        $app = EasyWeChat::officialAccount($options);

        $js = $app->jssdk;
        $js->setUrl($url);

        $config = $js->buildConfig(array(
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu',
            'scanQRCode',
            'updateAppMessageShareData',
            'updateTimelineShareData'
        ));
        $config = json_decode($config, 1);

        $info['uid'] = \YunShop::app()->getMemberId();

//        if (\YunShop::app()->getMemberId()) {
//            $info = Member::getUserInfos(\YunShop::app()->getMemberId())->first();
//
//            if (!empty($info)) {
//                $info = $info->toArray();
//            }
//        }

        $share = \Setting::get('shop.share');

        if ($share) {
            if ($share['icon']) {
                $share['icon'] = replace_yunshop(yz_tomedia($share['icon']));
            }
        }

        $shop['shop'] = \Setting::get('shop.shop');
        if (is_null($shop['shop'])) {
            $shop['shop']['name'] = '商家分享';
        }
        $shop['icon'] = replace_yunshop(yz_tomedia($shop['logo']));

        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('customer_service'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_service'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_service'), 'function');
            $ret = $class::$function(request()->id);
            if ($ret) {
                $shop['cservice'] = $ret;
            }
        }
        if (is_null($share) && is_null($shop)) {
            $share = [
                'title' => '商家分享',
                'icon' => '#',
                'desc' => '商家分享'
            ];
        }
        if (app('plugins')->isEnabled('designer')) {
            $index = (new RecordsController())->shareIndex();
            foreach ($index['data'] as $value) {
                foreach ($value['page_type_cast'] as $item) {
                    if ($item == 1) {
                        $designer = json_decode(htmlspecialchars_decode($value['page_info']))[0]->params;
                        if (!empty($share['icon']) && !empty($share['desc'])) {
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
            'info' => $info,   //商城设置
            'shop' => $shop,
            'share' => $share   //分享设置
        ];

        return show_json(1, $data);
    }

    public function getFirstGoodsPage()
    {
        $list = (new IndexController())->getRecommentGoods();
        return $this->successJson('', $list);
    }

    public function getCaptcha(Request $request, $integrated = null)
    {
        //增加验证码功能
        $status = Setting::get('shop.sms.status');
        if ($status == 1) {
            $captcha = self::captchaTest();
            $result['captcha'] = $captcha;
            $result['captcha']['status'] = $status;
        } else {
            $result['captcha']['status'] = $status;
        }
        if (is_null($integrated)) {
            return $this->successJson('ok', $result);
        } else {
            return show_json(1, $result);
        }

    }

    //该接口为全局需要的参数，别给我删了
    public function globalParameter($bool = false)
    {
        $data = [];
        //配送站
        if (app('plugins')->isEnabled('delivery-station')) {
            $data['is_open_delivery_station'] = \Setting::get('plugin.delivery_station.is_open') ? 1 : 0;
        } else {
            $data['is_open_delivery_station'] = 0;
        }

        if (app('plugins')->isEnabled('photo-order')) {
            $set = \Setting::get('plugin.photo-order');
            if ($set['is_open'] == 1) {
                $data['is_open_photo_order'] = 1;
                $data['photo_order_min_pohot'] = $set['min_pohot'];
                $data['photo_order_max_pohot'] = $set['max_pohot'];
            } else {
                $data['is_open_photo_order'] = 0;
            }
        } else {
            $data['is_open_photo_order'] = 0;
        }

        //会员订单配送方式为司机配送(7), 参数会在会员中心订单列表、供应商前端订单、门店前端订单用到
        $data['delivery_driver_open'] = \app\common\services\plugin\DeliveryDriverSet::whetherEnabled();

        // 自提点
        if (app('plugins')->isEnabled('package-deliver')) {
            $set = \Setting::get('plugin.package_deliver');
            $data['is_open_package_deliver'] = $set['is_package'];
        }

        // 广告市场
        if (app('plugins')->isEnabled('advert-market') && app('plugins')->isEnabled('store-cashier')) {
            $set = \Setting::get('plugin.advert-market');
            $data['is_open_advert_market'] = $set['is_open'];
        }
        $data['assemble_name'] = '安装服务';
        $data['assemble_worker_name'] = '安装师傅';
        if (app('plugins')->isEnabled('assemble')) {
            $data['assemble_name'] = Setting::get('plugin.assemble.assemble_name') ?: '安装服务';
            $data['assemble_worker_name'] = Setting::get('plugin.assemble.assemble_worker_name') ?: '安装师傅';
        }
        if (app('plugins')->isEnabled('consume-red-packet')) {
            $data['consume_red_packet_status'] = 1;
        } else {
            $data['consume_red_packet_status'] = 0;
        }

        if (app('plugins')->isEnabled('shop-esign')) {
            $data['is_open_shop_esign'] = 1;
        } else {
            $data['is_open_shop_esign'] = 0;
        }

        //汇聚支付
        if (app('plugins')->isEnabled('converge_pay') && \Setting::get('plugin.convergePay_set.wechat') == true) {
            $data['initial_id'] = Setting::get('plugin.convergePay_set.wechat.initial_id');//初始id
        }

        $data['ios_virtual_pay'] = $this->getIosVirtualPay();

        //注册，下单定位
        $data['order_locate'] = false;
        $data['register_locate'] = false;
        $data['bind_mobile_locate'] = false;
        if (app('plugins')->isEnabled('registration_area')) {
            $area_set = array_pluck(Setting::getAllByGroup('registration-area')->toArray(), 'value', 'key');
            if ($area_set['is_open'] == 1) {
                $data['order_locate'] = true;
                $data['register_locate'] = true;
                $data['bind_mobile_locate'] = true;
            }
        }

        //招商专员插件是否开启 true 开启 false 关闭
        if (app('plugins')->isEnabled('invest-people')) {
            $data['invest_people_name'] = \Yunshop\InvestPeople\services\InvestMemberView::pluginName();
            $data['invest_people_open'] = !\Setting::get('plugin.invest_people')['open'];
        } else {
            $data['invest_people_open'] = false;
        }


        $data['cart_num'] = \app\frontend\models\MemberCart::getCartNum(\YunShop::app()->getMemberId());

        if ($bool) {
            return show_json(1, $data);
        } else {
            return $this->successJson('ok', $data);
        }
    }


    public function getParams(Request $request)
    {
        $this->dataIntegrated((new MemberController())->getAdvertisement($request, true), 'advertisement');
        $this->dataIntegrated((new MemberController())->guideFollow($request, true), 'guide');

        return $this->successJson('', $this->apiData);
    }

    public function getNewLoveGoods($goods_id)
    {
        $goodsModel = GoodsLove::select('*')->where('uniacid', \Yunshop::app()->uniacid)->where('goods_id', $goods_id)->first();
        $goods = $goodsModel ? $goodsModel->toArray() : $this->getDefaultGoodsData();
        return $goods;

    }

    private function getDefaultGoodsData()
    {
        return [
            'award' => '0',
            'parent_award' => '0',
            'deduction' => '0',
            'award_proportion' => '0',
            'parent_award_proportion' => '0',
            'second_award_proportion' => '0',
            'deduction_proportion' => '0',
            'deduction_proportion_low' => '0',
            'commission' => [
                'rule' => [

                ],
            ],
        ];
    }

    private function getGoodsModelByIds($goods_ids)
    {
        $goodsModels = \app\frontend\models\Goods::select()
            ->whereIn('id', $goods_ids)
            ->get();

        return $goodsModels->isEmpty() ? [] : $goodsModels->toArray();
    }

    // 获取IOS虚拟支付开关
    public function getIosVirtualPay()
    {
        $is_ios = Client::osType();
        if ($is_ios == 1) {
            $ios_virtual_pay = Setting::get('shop.pay.ios_virtual_pay'); //0.关闭  1.不关闭
        }

        if ($ios_virtual_pay != 1) {
            $ios_virtual_pay = 0;
        }

        return $ios_virtual_pay;

    }

    //添加页面分享记录
    public function addPageShareRecord()
    {
        $url = request()->url;
        $uid = \YunShop::app()->getMemberId();
        $result = PageShareRecord::insert(['url' => $url, 'member_id' => $uid, 'uniacid' => \YunShop::app()->uniacid]);
        return $this->successJson('成功', $result);
    }
}
