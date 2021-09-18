<?php

namespace app\frontend\modules\home\services;

use app\common\helpers\Cache;
use app\common\services\popularize\PortType;
use app\frontend\modules\member\services\MemberLevelAuth;
use app\frontend\modules\shop\controllers\IndexController;

Class ShopPublicDataService
{
	public $is_decorate = 0;

	public $page_type;
	public $foot_cache = true;
	public $page_id;

	private static $instance;
	private function __construct()
	{
		$this->page_type = request()->type;
		$this->page_id = $page_id = (int) request()->page_id ?: 0;
	}

	public static function getInstance()
	{
		if (self::$instance instanceof self) {
			return self::$instance;
		}
		// 判断是使用新装修还是旧装修
		if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
			self::$instance = new \Yunshop\Decorate\services\ShopPublicDataService();
		} elseif (app('plugins')->isEnabled('designer')) {
			self::$instance = new \Yunshop\Designer\services\ShopPublicDataService();
		} else {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getFootMenus()
	{
		$this->foot_cache = false;
		$i = request()->i;
		$mid = request()->mid;
		$type = request()->type;
		//默认菜单
		$Menu = Array(
			Array(
				"id"          => 1,
				"title"       => "首页",
				"icon"        => "fa fa-home",
				"url"         => "/addons/yun_shop/?#/home?i=" . $i . "&mid=" . $mid . "&type=" . $type,
				"name"        => "home",
				"subMenus"    => [],
				"textcolor"   => "#70c10b",
				"bgcolor"     => "#24d7e6",
				"bordercolor" => "#bfbfbf",
				"iconcolor"   => "#666666"
			),
			Array(
				"id"          => "menu_1489731310493",
				"title"       => "分类",
				"icon"        => "fa fa-th-large",
				"url"         => "/addons/yun_shop/?#/category?i=" . $i . "&mid=" . $mid . "&type=" . $type,
				"name"        => "category",
				"subMenus"    => [],
				"textcolor"   => "#70c10b",
				"bgcolor"     => "#24d7e6",
				"iconcolor"   => "#666666",
				"bordercolor" => "#bfbfbf"
			),
			Array(
				"id"          => "menu_1489735163419",
				"title"       => "购物车",
				"icon"        => "fa fa-cart-plus",
				"url"         => "/addons/yun_shop/?#/cart?i=" . $i . "&mid=" . $mid . "&type=" . $type,
				"name"        => "cart",
				"subMenus"    => [],
				"textcolor"   => "#70c10b",
				"bgcolor"     => "#24d7e6",
				"iconcolor"   => "#666666",
				"bordercolor" => "#bfbfbf"
			),
			Array(
				"id"          => "menu_1491619644306",
				"title"       => "会员中心",
				"icon"        => "fa fa-user",
				"url"         => "/addons/yun_shop/?#/member?i=" . $i . "&mid=" . $mid . "&type=" . $type,
				"name"        => "member",
				"subMenus"    => [],
				"textcolor"   => "#70c10b",
				"bgcolor"     => "#24d7e6",
				"iconcolor"   => "#666666",
				"bordercolor" => "#bfbfbf"
			),
		);
		$promoteMenu = Array(
			"id"          => "menu_1489731319695",
			"classt"      => "no",
			"title"       => "推广",
			"icon"        => "fa fa-send",
			"url"         => "/addons/yun_shop/?#/member/extension?i=" . $i . "&mid=" . $mid . "&type=" . $type,
			"name"        => "extension",
			"subMenus"    => [],
			"textcolor"   => "#666666",
			"bgcolor"     => "#837aef",
			"iconcolor"   => "#666666",
			"bordercolor" => "#bfbfbf"
		);
		$extension_status = \Setting::get('shop_app.pay.extension_status');
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
		return ['menus'=>$Menu,'menustyle'=>$this->getFootMenusStyle(),'footermenu'=>'','footertype'=>''];
	}

	public function getViewSet()
	{
		return [];
	}

	public function getFootMenusStyle()
	{
		return Array(
			"previewbg"       => "#ef372e",
			"height"          => "49px",
			"textcolor"       => "#666666",
			"textcolorhigh"   => "#ff4949",
			"iconcolor"       => "#666666",
			"iconcolorhigh"   => "#ff4949",
			"bgcolor"         => "#FFF",
			"bgcolorhigh"     => "#FFF",
			"bordercolor"     => "#010101",
			"bordercolorhigh" => "#bfbfbf",
			"showtext"        => 1,
			"showborder"      => "0",
			"showicon"        => 2,
			"textcolor2"      => "#666666",
			"bgcolor2"        => "#fafafa",
			"bordercolor2"    => "#1856f8",
			"showborder2"     => 1,
			"bgalpha"         => ".5",
		);
	}

	public function getDefaultDesign()
	{
		if (!Cache::has('shop_category')) {
			$set = \Setting::get('shop.category');

			Cache::put('shop_category', $set, 4200);
		} else {
			$set = Cache::get('shop_category');
		}

		$set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));

		if (!Cache::has('shop_default_design')) {
			$result = Array(
				'ads'        => (new IndexController())->getAds(),
				'advs'       => (new IndexController())->getAdv(),
				'brand'      => (new IndexController())->getRecommentBrandList(),
				'category'   => (new IndexController())->getRecommentCategoryList(),
				'time_goods' => (new IndexController())->getTimeLimitGoods(),
				'set'        => $set,
				'goods'      => (new IndexController())->getRecommentGoods(),
			);
			Cache::put('shop_default_design', $result, 1);

		} else {
			$result = Cache::get('shop_default_design');
		}
		return $result;
	}

	public function getIndexData()
	{
		$result['default'] = $this->getDefaultDesign();
		$result['item']['data'] = ''; //前端需要该字段
		$result['item']['topmenu'] = [
			'menus'  => [],
			'params' => [],
			'isshow' => false
		];
		$result = $this->getLoginMemberLevel($result);
		return $result;
	}

	public function getLoginMemberLevel($result)
	{
		$member_id = \YunShop::app()->getMemberId();
		$allow_auth = $result['item']['pageinfo']['params']['checkitem'];//允许登录的用户等级
		$member_service = new MemberLevelAuth();
		$auth = $member_service->doAuth($member_id,$allow_auth);
		if (!$auth) {
			$result['item']['pageinfo']['params']['allowauth'] = 0;
		} else {
			$result['item']['pageinfo']['params']['allowauth'] = 1;
		}
		return $result;
	}




}
