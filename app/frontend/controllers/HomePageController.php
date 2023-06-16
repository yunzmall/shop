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
        'wxapp',
        'getParams',
        'getCaptcha'
    ];
    protected $ignoreAction = [
        'wxapp',
        'getCaptcha'
    ];

    public function index(Request $request)
    {
        app('db')->cacheSelect = true;
		// 获取对应的装修服务
		$data_service = ShopPublicDataService::getInstance();
		// 获取首页数据
		$result = $data_service->getIndexData();
		return $this->successJson('ok', $result);

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

        $decorateId = request()->input('decorate_id');         //装修ID
        $componentId = request()->input('component_id');       //组件ID
        $componentKey = request()->input('component_key');     //组件名称
        $componentInfo = request()->input('component_info');   //组件参数

        if (!$decorateId || !$componentId || !$componentKey) {
            return $this->errorJson('缺少必要参数');
        }

        $className = ucfirst(str_replace('U_', '', $componentKey));
        $classNamespace = "\Yunshop\Decorate\common\services\component\\";

        // 判断类是否存在（类名需要对应组件名）
        if (class_exists($classNamespace . $className)) {
            $myclass = new \ReflectionClass($classNamespace . $className); //获取组件对应的类
			$commonData = [];
			$myclass = $myclass->newInstance($commonData);
            $list = $myclass->pageList();  //获取组件的分页数据
        }

        if (!$list) {
            return $this->errorJson('error');
        }

        return $this->successJson('success', $list);
    }


	/**
	 * 小程序请求
	 * @param Request $request
	 * @return array|\Illuminate\Http\JsonResponse
	 * @throws MemberNotLoginException
	 */
    public function wxapp(Request $request)
    {
        return $this->index($request);
    }

    public function getFirstGoodsPage()
    {
        $list = (new IndexController())->getRecommentGoods();
        return $this->successJson('', $list);
    }

    public function getCaptcha()
    {
        //增加验证码功能
        $status = Setting::get('shop.sms.status');
        if ($status == 1) {
            $result['captcha'] = app('captcha')->create('default', true);
            $result['captcha']['status'] = $status;
        } else {
            $result['captcha']['status'] = $status;
        }
		return $this->successJson('ok', $result);
    }


    public function getParams(Request $request)
    {
        $this->dataIntegrated((new MemberController())->getAdvertisement($request, true), 'advertisement');
        $this->dataIntegrated((new MemberController())->guideFollow($request, true), 'guide');

        return $this->successJson('', $this->apiData);
    }


	/**
	 * 添加页面分享记录
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function addPageShareRecord()
    {
        $url = request()->url;
        $uid = \YunShop::app()->getMemberId();
        $result = PageShareRecord::insert(['url' => $url, 'member_id' => $uid, 'uniacid' => \YunShop::app()->uniacid]);
        return $this->successJson('成功', $result);
    }
}
