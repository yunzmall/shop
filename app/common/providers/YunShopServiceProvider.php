<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/8/17
 * Time: 17:29
 */

namespace app\common\providers;

use app\common\facades\RichText;
use app\common\facades\Setting;
use app\common\models\AccountWechats;
use app\platform\modules\system\models\SystemSetting;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\ServiceProvider;
use app\common\services\Utils;

class YunShopServiceProvider extends ServiceProvider
{

	public function boot()
	{
		if (config('app.framework') != 'platform' || (!\YunShop::isWeb() && !\YunShop::isWechatApi())) {
			Setting::$uniqueAccountId = \YunShop::app()->uniacid;
			RichText::$uniqueAccountId = \YunShop::app()->uniacid;
			return;
		}
		global $_W;
		$uniacid = request()->uniacid ?:request()->cookie('uniacid');
		Utils::addUniacid($uniacid);
		$account = AccountWechats::getAccountByUniacid($uniacid);
		$remote = $this->getRemoteInfo();
		$_W = [
			'uniacid'          => $uniacid,
			'acid'             => $uniacid,
			'account'          => $account ? $account->toArray() : [],
			'openid'           => '',
			'uid'              => \Auth::guard('admin')->user()->uid,
			'username'         => \Auth::guard('admin')->user()->username,
			'siteroot'         => request()->getSchemeAndHttpHost() . '/',
			'siteurl'          => request()->getUri(),
			'attachurl'        => $remote['attachurl'],
			'attachurl_local'  => request()->getSchemeAndHttpHost() . '/static/upload/',
			'attachurl_remote' => $remote['attachurl_remote'],
			'charset'          => 'utf-8'
		];
		//设置uniacid
		Setting::$uniqueAccountId = \YunShop::app()->uniacid;
		RichText::$uniqueAccountId = \YunShop::app()->uniacid;
	}

	private function getRemoteInfo()
	{
		$type = [
			2 => 'alioss',
			4 => 'cos'
		];
		$remote = SystemSetting::settingLoad('remote');
		if ($remote['type'] != 0) {
			return [
				'attachurl' => $remote[$type[$remote['type']]]['url'],
				'attachurl_remote' => $remote[$type[$remote['type']]]['url']
			];
		}
		return [
			'attachurl' => request()->getSchemeAndHttpHost() . '/static/upload/',
			'attachurl_remote' => ''
		];

	}


}
