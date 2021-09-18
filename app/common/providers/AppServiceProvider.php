<?php

namespace app\common\providers;

use App;
use app\common\facades\RichText;
use app\common\models\AccountWechats;
use app\common\modules\site\SiteSetting;
use app\common\modules\site\SiteSettingCache;
use app\common\modules\site\WqUniSetting;
use app\common\repositories\OptionRepository;
use app\common\services\mews\captcha\src\Captcha;
use app\framework\Log\CronLog;
use app\framework\Log\SqlLog;
use app\framework\Log\TraceLog;
use app\common\facades\Setting;
use app\platform\Repository\SystemSetting;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\ServiceProvider;
use app\common\services\Utils;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    	//安装验证
		$this->verifyInstall();
		\Cron::setDisablePreventOverlapping();
		\Cron::setLogger((new CronLog())->getLogger()->getLogger());
        \Event::listen(StatementPrepared::class, function ($event) {
            $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
        });
	}

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
		//微信接口不输出错误
		if (strpos(request()->getRequestUri(), '/api.php') >= 0) {
			error_reporting(0);
		}
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Orangehill\Iseed\IseedServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Way\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
			DB::listen(function ($sql) {
					foreach ($sql->bindings as $i => $binding) {
						if ($binding instanceof \DateTime) {
							$sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
						} else {
							if (is_string($binding)) {
								$sql->bindings[$i] = "'$binding'";
							}
						}
					}
					// Insert bindings into query
					$query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);
					$query = vsprintf($query, $sql->bindings);
					// Save the query to file
					(new SqlLog())->getLogger()->getLogger()->addInfo($query);;
			});
        }
        //增加模板扩展tpl
        \View::addExtension('tpl', 'blade');
        //配置表
        $this->app->singleton('options',  OptionRepository::class);
        $this->app->singleton('siteSetting',  SiteSetting::class);
        $this->app->singleton('siteSettingCache',  SiteSettingCache::class);
        $this->app->singleton('SystemSetting', SystemSetting::class);
        $this->app->singleton('WqUniSetting', WqUniSetting::class);
        /**
         * 设置
         */
		$this->app->bind('setting', function() {
            return new Setting();
        });
		$this->app->bind('captcha', Captcha::class);
        $this->app->singleton('Log.trace', function () {
            return new TraceLog();
        });

    }

	private function verifyInstall()
	{
		if (config('app.framework') == 'platform' && !file_exists(base_path().'/bootstrap/install.lock') && !strpos(request()->path(), 'install')) {
			response()->json([
				'result' => 0,
				'msg' => '',
				'data' => ['status' => -4]
			], 200, ['charset' => 'utf-8'])->send();
			exit;
		}
	}


}
