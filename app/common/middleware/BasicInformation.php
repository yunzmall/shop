<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/2/19
 * Time: 上午11:53
 */

namespace app\common\middleware;


use app\backend\modules\member\models\MemberRelation;
use app\common\events\CommonExtraEvent;
use app\common\helpers\Cache;
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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if(!$response)
        {
            return $response;
        }
        $content = $response->getContent();
        $response->setContent($this->handleContent($request,$content));
        return $response;
    }

    private function handleContent($request,$content)
    {
        app('db')->cacheSelect = false;
        $content = json_decode($content,true);

        if(request()->basic_info == 1)
        {
            $content = array_merge($content,['basic_info'=>$this->getBasicInfo($request,$content)]);
        }
        if(request()->validate_page == 1)
        {
            $content = array_merge($content,$this->getValidatePage($request));
        }

        //公共参数扩展
        //$content = array_merge($content,$this->CommonExtra($request));

        return json_encode($content);
    }

    private function getBasicInfo($request,$content=[])
    {

        $data = [
            'popularize_page' =>  (new PopularizePageShowController())->index($request,true)['json'],
            'balance' => (new HomePageController())->getBalance()['json'],
            'lang' => (new HomePageController())->getLangSetting()['json'],
            'globalParameter' => (new HomePageController())->globalParameter(true)['json'],
            'plugin_setting' => PluginsConfig::current()->get()
        ];

        if (!miniVersionCompare('1.1.88') || !versionCompare('1.1.084')) {
            $data['home'] =  empty($content['data'])?[]:$content['data'];

            if ($content['page_type'] !== 'home') {
                $data['home'] = (new HomePageController())->index($request,true)['json'];
            }
        } else {
            $data['home'] = $this->getPublicData();
        }

        return $data;

//		if (!miniVersionCompare('1.1.88') || !versionCompare('1.1.084')) {
//			$data = [
//				'popularize_page' =>  (new PopularizePageShowController())->index($request,true)['json'],
//				'home' => empty($content['data'])?[]:$content['data'],
//				'balance' => (new HomePageController())->getBalance()['json'],
//				'lang' => (new HomePageController())->getLangSetting()['json'],
//				'globalParameter' => (new HomePageController())->globalParameter(true)['json'],
//				'plugin_setting' => PluginsConfig::current()->get()
//			];
//
//			if ($content['page_type'] !== 'home') {
//				$data['home'] = (new HomePageController())->index($request,true)['json'];
//			}
//			return $data;
//		}
//		return [
//            'popularize_page' =>  (new PopularizePageShowController())->index($request,true)['json'],
//            'home' => $this->getPublicData(),
//            'balance' => (new HomePageController())->getBalance()['json'],
//            'lang' => (new HomePageController())->getLangSetting()['json'],
//            'globalParameter' => (new HomePageController())->globalParameter(true)['json'],
//            'plugin_setting' => PluginsConfig::current()->get()
//        ];
    }

    private function getValidatePage($request)
    {
        return ['validate_page'=>(new MemberController())->isValidatePage($request,true)['json']];
    }

    private function CommonExtra()
    {
        $data = [];
        $extra = event(new CommonExtraEvent());

        if (!empty($extra)) {
            foreach ($extra as $val) {
                $data['extra'] = $val;
            }
        }

        return $data;
    }


	private function getPublicData()
	{
		$uid = \YunShop::app()->getMemberId();
		if (!Cache::has("public_setting")) {
			//商城设置
			$shop_setting = \Setting::get('shop.shop');
			$member_set = \Setting::get('shop.member');
			$mailInfo = [
				'logo'				=> replace_yunshop(yz_tomedia($shop_setting['logo'])),
				'signimg'			=> replace_yunshop(yz_tomedia($shop_setting['signimg'])),
				'agent'				=> MemberRelation::getSetInfo()->value('status') ? true : false,
				'diycode'			=> html_entity_decode($shop_setting['diycode']),
				'is_bind_mobile'	=> $member_set['is_bind_mobile'],   //是否绑定手机号
			];
			//客服设置
			$mailInfo = array_merge($shop_setting,$mailInfo,(new ServiceController())->index());
			$setting['mailInfo'] = $mailInfo;
			$setting['mobile_login_code'] = $member_set['mobile_login_code'];
			$setting['register'] = \Setting::get('shop.register');
			//增加验证码功能
			$setting['captcha_status'] = \Setting::get('shop.sms.status');
			$setting['plugin']['goods_show'] = [];
			if (app('plugins')->isEnabled('goods-show')) {
				$setting['plugin']['goods_show'] = [
					'goods_group'	=>	\Setting::get('plugin.goods_show.goods_group'),
					'around_goods'	=>	\Setting::get('plugin.goods_show.around_goods'),
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
			$is_bind_mobile = Member::current()->mobile? 0:$setting['mailInfo']['is_bind_mobile'];
			$result['memberinfo']['uid'] = Member::current()->uid;
			//用户爱心值
			if (app('plugins')->isEnabled('love')) {
				$love_usable = \Yunshop\Love\Common\Models\MemberLove::select('usable')->where('member_id', $uid)->value('usable');
			}
			$result['memberinfo']['usable'] = empty($love_usable)?0:$love_usable;
		}
		$setting['mailInfo']['is_bind_mobile'] = (int) $is_bind_mobile;
		$result['mailInfo'] = $setting['mailInfo'];
		//验证码
		if (extension_loaded('fileinfo') && $setting['captcha_status'] == 1) {
			$result['captcha'] = app('captcha')->create('default', true);
			$result['captcha']['status'] = 1;
		}
		$result['system']['mobile_login_code'] = $setting['mobile_login_code'] ? 1 : 0;
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
			'ViewSet' => $public_data_service->getViewSet(),
			'is_decorate' => $public_data_service->is_decorate,
		];
		$result['item'] = array_merge($item,$public_data_service->getFootMenus());
		
		//会员注册设置
		$register_set = \Setting::get('shop.register');
		$shop = \Setting::get('shop.shop');
		
		if ($shop['is_agreement']){
			$register_set['new_agreement'] = RichText::get('shop.agreement');
			$register_set['agreement_name'] = $shop['agreement_name'];
		}
		$result['register_setting'] = $register_set;
		
		return $result;
	}


}