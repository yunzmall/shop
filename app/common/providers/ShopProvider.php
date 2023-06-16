<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/6/20
 * Time: 上午10:07
 */

namespace app\common\providers;

use app\backend\modules\supervisord\services\Supervisor;
use app\common\facades\Setting;
use app\common\facades\SiteSetting;
use app\common\helpers\SettingCache;
use app\common\modules\express\KDN;
use app\common\managers\ModelExpansionManager;
use app\common\models\BaseModel;
use app\common\modules\sms\SmsService;
use app\common\modules\status\StatusContainer;
use app\common\services\easyWechat\container\PaymentContainer;
use app\common\services\easyWechat\container\WorkContainer;
use app\common\services\easyWechat\container\OfficialAccountContainer;
use app\common\services\member\center\MemberCenterManage;
use app\frontend\modules\coin\CoinManager;
use app\frontend\modules\deduction\DeductionManager;
use app\frontend\modules\goods\services\GoodsDetailManager;
use app\frontend\modules\goods\services\GoodsManager;
use app\common\modules\order\OrderManager;
use app\frontend\modules\payment\managers\PaymentManager;
use Illuminate\Support\ServiceProvider;
use app\common\modules\express\Logistics;

class ShopProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('SettingCache', function () {
            return new SettingCache();
        });
        $this->app->singleton('supervisor', function () {
            $supervisord = SiteSetting::get('supervisor');
            if ($supervisord['service_type'] == 1) {
                $ip = $supervisord['service'];
                $host_num = count($supervisord['service']);
            } elseif ($supervisord['service_type'] == 2) {
                $ip = ['http://localhost'];
                $host_num = count($supervisord['service']);
            } else {
                $ip = $supervisord['address']['ip'] ?: 'http://127.0.0.1';
                $ip = [$ip];
                $host_num = 1;
            }
            return new Supervisor($ip, 9001,$host_num);
        });
        $this->app->singleton('ModelExpansionManager', function () {
            return new ModelExpansionManager();

        });
        $this->app->singleton('CoinManager', function () {
            return new CoinManager();
        });
        $this->app->singleton('DeductionManager', function () {
            return new DeductionManager();
        });
        $this->app->singleton('GoodsManager', function () {
            return new GoodsManager();
        });
        $this->app->singleton('OrderManager', function () {
            return new OrderManager();
        });
        //商品挂件
        $this->app->singleton('GoodsWidgetContainer', function () {
            return new  \app\backend\modules\goods\widget\manage\GoodsWidgetContainer();
        });

        //购物车容器
        $this->app->singleton('CartContainer', function () {
            return new \app\frontend\modules\cart\manager\CartContainer();
        });

        $this->app->singleton('StatusContainer', function () {
            return new StatusContainer();
        });
        $this->app->singleton('express', function () {
            return new KDN(Setting::get('shop.express_info.KDN.eBusinessID'), Setting::get('shop.express_info.KDN.appKey'), config('app.express.KDN.reqURL'));
        });

        $this->app->singleton('logistics', function () {
            return new Logistics();
        });

        $this->app->singleton('sms', function () {
            return new SmsService();
        });

        $this->app->singleton('GoodsDetail', function () {
			return new GoodsDetailManager();
        });

        $this->app->singleton('MemberCenter', function () {
            return new MemberCenterManage();
        });

        $this->app->singleton('BusinessMsgNotice', function () {
            return new \business\common\notice\BusinessNoticeManager();
        });


//        $this->app->bind('external_contact', function ($app) {
//            return new \app\common\services\easyWechat\Client($app);
//        });
    }

    public function boot()
    {
    }
}