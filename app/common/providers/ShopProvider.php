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
            if ($supervisord['service_type']) {
                $ip = $supervisord['service'];
            } else {
                $ip = $supervisord['address']['ip'] ?: 'http://127.0.0.1';
                $ip = [$ip];
            }
            return new Supervisor($ip, 9001);
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
        $this->app->singleton('PaymentManager', function () {
            return new PaymentManager();
        });
        $this->app->singleton('GoodsManager', function () {
            return new GoodsManager();
        });
        $this->app->singleton('OrderManager', function () {
            return new OrderManager();
        });

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

        $this->app->singleton('GoodsDetail',function () {
        	return new GoodsDetailManager();
		});
    }

    public function boot()
    {

    }
}