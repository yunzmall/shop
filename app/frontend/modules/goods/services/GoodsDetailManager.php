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
use Illuminate\Container\Container;
use function foo\func;

class GoodsDetailManager extends Container
{

	public function __construct()
	{
		$this->bind('Goods',function ($goodsDetail,$attributes = []) {
			$goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
			return new $goods_model($attributes);
		});

		$this->singleton('DetailsCollection',function ($goodsDetail) {
			return new Collection([GoodsDetailService::class]);
		});
	}

	//实例化所有商品详情类
	public function initDetailInstance()
	{
		$this->make('DetailsCollection')->transform(function ($detail) {
			return new $detail();
		});
	}

	//根据商品plugin_id取出对应的商品详情类
	public function setDetailInstance($goods_model)
	{
		$this->singleton('GoodsDetailInstance',function ($goodsDetail) use ($goods_model) {
			$instance = $this->make('DetailsCollection')->where('plugin_id',$goods_model->plugin_id)->first();
			if (empty($instance)) {
				$instance = $this->make('DetailsCollection')->where('plugin_id', 0)->first();
			}
			return $instance;
		});
	}

	//加载所有插件的商品详情类
	public function setPlugin($class_name)
	{
		$this->make('DetailsCollection')->push($class_name);
	}
}