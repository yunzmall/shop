<?php


namespace app\framework\Toplan\Sms;


class SmsManagerServiceProvider extends \Toplan\Sms\SmsManagerServiceProvider
{
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../../../../vendor/toplan/laravel-sms/src/config/laravel-sms.php' => config_path('laravel-sms.php'),
		], 'config');

		$this->publishes([
			__DIR__ . '/../../../../vendor/toplan/laravel-sms/migrations/' => database_path('/migrations'),
		], 'migrations');

		if (config('laravel-sms.route.enable', true)) {
			require __DIR__ . '/../../../../vendor/toplan/laravel-sms/src/Toplan/LaravelSms/routes.php';
		}

		//require __DIR__ . '/validations.php';

		$this->phpSms();
	}
}