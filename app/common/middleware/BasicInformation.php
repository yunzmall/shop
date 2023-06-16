<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/2/19
 * Time: 上午11:53
 */

namespace app\common\middleware;


use app\backend\modules\goods\models\GoodsSetting;
use app\backend\modules\member\models\MemberRelation;
use app\common\events\CommonExtraEvent;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\helpers\Client;
use app\common\services\popularize\PortType;
use app\common\traits\JsonTrait;
use app\frontend\controllers\HomePageController;
use app\frontend\models\Member;
use app\frontend\modules\finance\controllers\PopularizePageShowController;
use app\frontend\modules\home\services\ShopPublicDataService;
use app\frontend\modules\member\controllers\MemberController;
use app\common\modules\shop\PluginsConfig;
use app\frontend\modules\member\controllers\ServiceController;
use Yunshop\Decorate\models\DecorateFooterModel;
use Yunshop\Decorate\models\DecorateModel;
use Yunshop\Love\Common\Services\SetService;
use Yunshop\NewMemberPrize\frontend\controllers\NewMemberPrizeController;
use app\common\facades\RichText;


class BasicInformation
{
    use JsonTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        if (!$response) {
            return $response;
        }
        $content = $response->getContent();
        $response->setContent($this->handleContent($request, $content));
        return $response;
    }

    private function handleContent($request, $content)
    {
        app('db')->cacheSelect = false;
        $content = json_decode($content, true);
        if (request()->basic_info == 1) {
            $content = array_merge($content, ['basic_info' => $this->getBasicInfo($request, $content)]);
        }
        if (request()->validate_page == 1) {
            $content = array_merge($content, $this->getValidatePage($request));
        }

        return json_encode($content);
    }

    private function getBasicInfo($request, $content = [])
    {
        $consolidated_payment = \Setting::get('shop.trade')['consolidated_payment'];
        return [
            'popularize_page'            => (new PopularizePageShowController())->index($request, true)['json'],
            'balance'                    => Setting::get('shop.shop')['credit'] ?: '余额',
            'point'                      => Setting::get('shop.shop.credit1') ?: '积分',
            'integral'                   => Setting::get('integral.plugin_name') ?: '消费积分',
            'ecological_point'           => Setting::get('plugin.ecological_point.plugin_name') ?: '供销积分',
            'love'                       => Setting::get('love.name') ?: '爱心值',
            'lang'                       => $this->getLangSetting(),
            'globalParameter'            => $this->globalParameter(),
            'plugin_setting'             => PluginsConfig::current()->get(),
            'consolidated_payment'       => is_null($consolidated_payment) ? 1 : $consolidated_payment,
            'home'                       => $this->getPublicData(),
            'thumb_parameter'            => $this->getThumbParameter(),
            'upload_max_file_size'       => $this->getFileSize(),
            'theme_color'                => Setting::get('shop.shop')['theme_color'] ?: '#fe9a51',
            'price_difference_pool_name' => Setting::get('plugin.price_difference_pool.plugin_name') ?: '资金池',
            'price_difference_pool'      => app('plugins')->isEnabled('price-difference-pool') && Setting::get("plugin.price_difference_pool.plugin_switch"),
            'customer_center'            => app('plugins')->isEnabled('customer-center') && Setting::get("plugin.customer-center.is_open"),
        ];
    }

    private function getValidatePage($request)
    {
        return ['validate_page' => (new MemberController())->isValidatePage($request, true)['json']];
    }

    private function getFileSize()
    {
        $file_size = 0;
        $file_size_str = ini_get('upload_max_filesize');
        if (strexists($file_size_str, 'M')) {
            $file_size = strstr($file_size_str, 'M', true) * 1024 * 1024;
        }
        if (strexists($file_size_str, 'G')) {
            $file_size = strstr($file_size_str, 'G', true) * 1024 * 1024 * 1024;
        }
        if (strexists($file_size_str, 'T')) {
            $file_size = strstr($file_size_str, 'T', true) * 1024 * 1024 * 1024 * 1024;
        }
        return $file_size;
    }

    private function getPublicData()
    {
        $uid = \YunShop::app()->getMemberId();
        if (!Cache::has("public_setting")) {
            //商城设置
            $shop_setting = Setting::get('shop.shop');
            $member_set = Setting::get('shop.member');
            $sms_set = Setting::get('shop.sms');
            $goodsSet = GoodsSetting::uniacid()->select('scribing_show')->first();
            $mailInfo = [
                'logo'             => replace_yunshop(yz_tomedia($shop_setting['logo'])),
                'signimg'          => replace_yunshop(yz_tomedia($shop_setting['signimg'])),
                'agent'            => MemberRelation::getSetInfo()->value('status') ? true : false,
                'diycode'          => html_entity_decode($shop_setting['diycode']),
                'is_bind_mobile'   => $member_set['is_bind_mobile'],   //是否绑定手机号
                'bind_mobile_page' => $member_set['bind_mobile_page'] ?: [],   //指定绑定手机号的页面
                'service_mobile'   => Setting::get('shop.contact')['phone'], //商城设置客服电话
                'scribing_show'    => $goodsSet['scribing_show'] ? 1 : 0, //原价划线显示
            ];
            //客服设置
            $mailInfo = array_merge($shop_setting, $mailInfo, (new ServiceController())->index());
            $setting['mailInfo'] = $mailInfo;
            $setting['mobile_login_code'] = $member_set['mobile_login_code'];
            $setting['register'] = Setting::get('shop.register');
            //增加验证码功能
            $setting['captcha_status'] = $sms_set['status'];
            $setting['country_code'] = $sms_set['country_code'];
            $setting['plugin']['goods_show'] = [];
            if (app('plugins')->isEnabled('goods-show')) {
                $setting['plugin']['goods_show'] = [
                    'goods_group'  => Setting::get('plugin.goods_show.goods_group'),
                    'around_goods' => Setting::get('plugin.goods_show.around_goods'),
                ];
            }
            if (app('plugins')->isEnabled('love')) {
                $setting['love_name'] = SetService::getLoveSet()['name'];//获取爱心值基础设置
            }
            Cache::put("public_setting", $setting, 3600);
        } else {
            $setting = Cache::get("public_setting");
        }
        //强制绑定手机号
        $is_bind_mobile = 0;
        if ($uid) {
            $is_bind_mobile = Member::current()->mobile ? 0 : $setting['mailInfo']['is_bind_mobile'];
            $result['memberinfo']['uid'] = Member::current()->uid;
            //用户爱心值
            if (app('plugins')->isEnabled('love')) {
                $love_usable = \Yunshop\Love\Common\Models\MemberLove::select('usable')->where(
                    'member_id',
                    $uid
                )->value('usable');
            }
            $result['memberinfo']['usable'] = empty($love_usable) ? 0 : $love_usable;
        }
        $setting['mailInfo']['is_bind_mobile'] = (int)$is_bind_mobile;
        $result['mailInfo'] = $setting['mailInfo'];
        //验证码
        if (extension_loaded('fileinfo') && $setting['captcha_status'] == 1) {
            $result['captcha'] = app('captcha')->create('default', true);
            $result['captcha']['status'] = 1;
        }
        $result['system']['mobile_login_code'] = $setting['mobile_login_code'] ? 1 : 0;
        $result['system']['country_code'] = $setting['country_code'] ? 1 : 0;
        //小程序验证推广按钮是否开启
        $result['system']['btn_romotion'] = PortType::popularizeShow(request()->type);
        //会员注册设置
        $result['register_setting'] = $setting['register'];
        //商品展示组件
        $result['plugin']['goods_show'] = $setting['plugin']['goods_show'];
        $result['plugin']['new_member_prize'] = [];
        if (app('plugins')->isEnabled('new-member-prize')) {
            $result['plugin']['new_member_prize'] = (new NewMemberPrizeController())->index(request()->type);
        }
        $result['designer']['love_name'] = $setting['love_name'];

        $public_data_service = ShopPublicDataService::getInstance();
        $item = [
            'ViewSet'     => $public_data_service->getViewSet(),
            'is_decorate' => $public_data_service->is_decorate,
        ];
        $result['item'] = array_merge($item, $public_data_service->getFootMenus());

        //会员注册设置
        $register_set = Setting::get('shop.register');
        $shop = Setting::get('shop.shop');

        if ($shop['is_agreement']) {
            $register_set['new_agreement'] = RichText::get('shop.agreement');
            $register_set['agreement_name'] = $shop['agreement_name'];
        }
        $result['register_setting'] = $register_set;
        return $result;
    }

    /**
     * 获取语言设置
     * @return array|mixed
     */
    private function getLangSetting()
    {
        $lang = Setting::get('shop.lang.lang');

        $data = [
            'test'          => [],
            'commission'    => [
                'title'             => '',
                'commission'        => '',
                'agent'             => '',
                'level_name'        => '',
                'commission_order'  => '',
                'commission_amount' => '',
            ],
            'single_return' => [
                'title'         => '',
                'single_return' => '',
                'return_name'   => '',
                'return_queue'  => '',
                'return_log'    => '',
                'return_detail' => '',
                'return_amount' => '',
            ],
            'team_return'   => [
                'title'         => '',
                'team_return'   => '',
                'return_name'   => '',
                'team_level'    => '',
                'return_log'    => '',
                'return_detail' => '',
                'return_amount' => '',
                'return_rate'   => '',
                'team_name'     => '',
                'return_time'   => '',
            ],
            'full_return'   => [
                'title'           => '',
                'full_return'     => '',
                'return_name'     => '',
                'full_return_log' => '',
                'return_detail'   => '',
                'return_amount'   => '',
            ],
            'team_dividend' => [
                'title'             => '',
                'team_dividend'     => '',
                'team_agent_centre' => '',
                'dividend'          => '',
                'flat_prize'        => '',
                'award_gratitude'   => '',
                'dividend_amount'   => '',
                'my_agent'          => '',
            ],
            'area_dividend' => [
                'area_dividend_center' => '',
                'area_dividend'        => '',
                'dividend_amount'      => '',
            ],
            'income'        => [
                'name_of_withdrawal'  => '提现',
                'income_name'         => '收入',
                'poundage_name'       => '手续费',
                'special_service_tax' => '劳务税',
                'manual_withdrawal'   => '手动提现',
            ],
            'agent'         => [
                'agent'             => '',
                'agent_num'         => '',
                'agent_count'       => '',
                'agent_order'       => '',
                'agent_order_count' => '',
                'agent_goods_num'   => '',
            ],
            'merchant'      => [
                'title'           => '招商',
                'merchant_people' => '招商员',
                'merchant_center' => '招商中心',
                'merchant_reward' => '分红',

            ]
        ];

        $langData = Setting::get('shop.lang.' . $lang, $data);

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
        if (!$langData['reserve_simple']['service']) {
            $langData['reserve_simple']['service'] = '服务';
        }
        if (!$langData['reserve_simple']['reserve_obj']) {
            $langData['reserve_simple']['reserve_obj'] = '预约人员';
        }
        if (!$langData['merchant']['title']) {
            $langData['merchant']['title'] = '招商';
        }
        if (!$langData['merchant']['merchant_people']) {
            $langData['merchant']['merchant_people'] = '招商员';
        }
        if (!$langData['merchant']['merchant_center']) {
            $langData['merchant']['merchant_center'] = '招商中心';
        }
        if (!$langData['merchant']['merchant_reward']) {
            $langData['merchant']['merchant_reward'] = '分红';
        }
        if (!$langData['store_projects']['project']) {
            $langData['store_projects']['project'] = '项目';
        }
        if (!$langData['area_dividend']['title']) {
            $langData['area_dividend']['title'] = '区域分红';
        }

        if (app('plugins')->isEnabled('micro')) {
            $title = Setting::get('plugin.micro');
            $langData['micro']['title'] = $title['micro_title'] ?: '微店';
        }

        $langData['plugin_language'] = \app\common\services\LangService::getCurrentLang();

        return $langData;
    }

    //该接口为全局需要的参数，别给我删了
    private function globalParameter()
    {
        //配送站
        if (app('plugins')->isEnabled('delivery-station')) {
            $data['is_open_delivery_station'] = Setting::get('plugin.delivery_station.is_open') ? 1 : 0;
        } else {
            $data['is_open_delivery_station'] = 0;
        }

        if (app('plugins')->isEnabled('photo-order')) {
            $set = Setting::get('plugin.photo-order');
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
            $set = Setting::get('plugin.package_deliver');
            $data['is_open_package_deliver'] = $set['is_package'];
        }

        // 广告市场
        if (app('plugins')->isEnabled('advert-market') && app('plugins')->isEnabled('store-cashier')) {
            $set = Setting::get('plugin.advert-market');
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
        $data['is_open_shop_esign_v2'] = 0;
        if (app('plugins')->isEnabled('shop-esign-v2')) {
            $data['is_open_shop_esign_v2'] = 1;
        }

        //汇聚支付
        if (app('plugins')->isEnabled('converge_pay') && Setting::get('plugin.convergePay_set.wechat') == true) {
            $data['initial_id'] = Setting::get('plugin.convergePay_set.wechat.initial_id');//初始id
        }

        $data['gh_id'] = '';
        if (app('plugins')->isEnabled('min-app') && ($gh_id = Setting::get('plugin.min_app.min_original_id'))) {
            $data['gh_id'] = $gh_id;
        }

        $ios_virtual_pay = 0;
        if (Client::osType() == 1 && Setting::get('shop.pay.ios_virtual_pay') == 1) {
            $ios_virtual_pay = 1;
        }
        $data['ios_virtual_pay'] = $ios_virtual_pay;

        //注册，下单定位
        $data['order_locate'] = false;
        $data['register_locate'] = false;
        $data['bind_mobile_locate'] = false;
        //会员价
        $data['vip_show'] = Setting::get('shop.member')['vip_price'] == 1 ? true : false;

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
            $data['invest_people_open'] = !Setting::get('plugin.invest_people')['open'];
        } else {
            $data['invest_people_open'] = false;
        }


        $data['cart_num'] = \app\frontend\models\MemberCart::getCartNum(\YunShop::app()->getMemberId());


        //益生系统
        if (app('plugins')->isEnabled('ys-system')) {
            $data['crm_account_bind'] = \Yunshop\YsSystem\common\AccountBindState::open(\YunShop::app()->getMemberId());
        } else {
            $data['crm_account_bind'] = 0;
        }

        //会员团队
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('team_judge')) && \YunShop::app()->getMemberId(
            )) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('team_judge'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('team_judge'), 'function');
            $member_team = $class::$function(\YunShop::app()->getMemberId());
            if ($member_team['res']) {
                $data['member_team'] = $member_team['notice'];
            }
        }

        //默认地址
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('default_address_judge'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('default_address_judge'), 'class');
            $function = array_get(
                \app\common\modules\shop\ShopConfig::current()->get('default_address_judge'),
                'function'
            );
            $default_address_judge = $class::$function();
            if ($default_address_judge['hide_address']) {
                $data['hide_address'] = true;
            }
            if ($default_address_judge['hide_dispatch']) {
                $data['hide_dispatch'] = true;
            }
        }
        $data['coffee_machine_open_state'] = app('plugins')->isEnabled('coffee-machine') && \Yunshop\CoffeeMachine\services\SettingService::getSetting()['open_state'] ? 1 : 0;
        $data['hide_total_sales'] = Setting::get('goods.hide_goods_sales') ? 1 : 0;
        return $data;
    }

    private function getThumbParameter()
    {
        return '';
        if (config('app.framework') == 'platform') {
            $systemSetting = app('SystemSetting');
            if ($remote = $systemSetting->get('remote')) {
                $setting[$remote['key']] = unserialize($remote['value']);
            }
            $upload_type = $setting['remote']['type'];
        } else {
            global $_W;
            //公众号独立配置信息 优先使用公众号独立配置
            $uni_setting = app('WqUniSetting')->get()->toArray();
            if (!empty($uni_setting['remote']) && iunserializer($uni_setting['remote'])['type'] != 0) {
                $setting['remote'] = iunserializer($uni_setting['remote']);
                $upload_type = $setting['remote']['type'];
            } else {
                $setting = $_W['setting'];
                $upload_type = $setting['remote']['type'];
            }
        }
        switch ($upload_type) {
            case 2:
                $parameter = '?x-oss-process=image/resize,mfit,h_350,w_350';
                break;
            case 4:
                $parameter = '?imageView2/2/w/350/h/350';
                break;
            default:
                $parameter = '';
        }
        return $parameter;
    }


}
