<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 16:20
 */

namespace app\common\providers;



use app\common\payment\PaymentConfig;
use app\common\payment\PaymentDirector;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{

	protected $defer = true;

	public function register()
	{
		$this->app->bind('Payment',PaymentDirector::class);
		$paymentMethod = PaymentConfig::get();
		$this->app->tag($paymentMethod,'paymentMethod');
	}

	public function provides()
	{
		return ['Payment'];
	}

}