<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/6/27
 * Time: 上午10:14
 */

namespace app\frontend\modules\goods\services;

use app\framework\Repository\Collection;
use app\frontend\models\Goods;
use app\frontend\widgets\WidgetsConfig;
use Illuminate\Container\Container;
use function foo\func;

class GoodsDetailManager extends Container
{
	private $init;

	public function __construct()
	{
		$this->bind('Goods',function ($goodsDetail,$attributes = []) {
			$goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
			return new $goods_model($attributes);
		});

		$this->singleton('DetailsCollection',function ($goodsDetail) {
			return new Collection(array_merge([GoodsDetailService::class],array_values(WidgetsConfig::getConfig('goods_detail'))));
		});
	}

	//根据商品plugin_id取出对应的商品详情类
	public function setDetailInstance($goods_model)
	{
		$this->initDetailInstance();
		$this->singleton('GoodsDetailInstance',function ($goodsDetail) use ($goods_model) {
			$instance = $this->make('DetailsCollection')->where('plugin_id',$goods_model->plugin_id)->first();
			if (empty($instance)) {
				$instance = $this->make('DetailsCollection')->where('plugin_id', 0)->first();
			}
			return $instance;
		});
	}

	//实例化所有商品详情类
	private function initDetailInstance()
	{
		if ($this->init == true) {
			return;
		}
		$this->make('DetailsCollection')->transform(function ($detail) {
			return new $detail();
		});
		$this->init = true;
	}

}