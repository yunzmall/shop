<?php


namespace app\frontend\modules\goods\services;



use Yunshop\StoreCashier\common\models\StoreSetting;
use Yunshop\StoreCashier\store\models\StoreGoods;

class GoodsDetailService extends GoodsDetailBaseService
{
	public $plugin_id = 0;

	public $goods_type = 'goods';


	public function getGoodsSale()
	{
		//获取商城设置: 判断 积分、余额 是否有自定义名称
		$shopSet = \Setting::get('shop.shop');
		$sale = [];
		$sale[] = $this->getFreightSale();
		$sale[] = $this->getPointAllSale($shopSet);
		$sale[] = $this->getFullSale();
		$sale[] = $this->getBalanceSale($shopSet);
		$sale[] = $this->getPointSale($shopSet);
		$sale[] = $this->getCouponSale();
		return array_filter($sale);
	}

	private function getFreightSale()
	{
		$data = [];
		if ($this->goods_model->hasOneSale->ed_num || $this->goods_model->hasOneSale->ed_money) {
			$data['name'] = '包邮';
			$data['key'] = 'ed_num';
			$data['type'] = 'array';
			if ($this->goods_model->hasOneSale->ed_num) {
				$data['value'][] = '本商品满' . $this->goods_model->hasOneSale->ed_num . '件包邮';
			}
			if ($this->goods_model->hasOneSale->ed_money) {
				$data['value'][] = '本商品满￥' . $this->goods_model->hasOneSale->ed_money . '包邮';

			}
		}
		return $data;
	}

	private function getPointAllSale($shopSet)
	{
		$data = [];
		if($this->goods_model->hasOneSale->all_point_deduct && $this->goods_model->hasOneSale->has_all_point_deduct){//商品设置
			$data['name'] = $shopSet['credit1'] ? $shopSet['credit1'].'全额抵扣':'积分全额抵扣';
			$data['key'] = 'all_point_deduct';
			$data['type'] = 'string';
			$data['value'] = '可使用' . $this->goods_model->hasOneSale->all_point_deduct .'个'.($shopSet['credit1'] ? $shopSet['credit1'] .'全额抵扣购买' : '积分全额抵扣购买');
		}
		return $data;
	}

	private function getFullSale()
	{
		$data = [];
		if ((bccomp($this->goods_model->hasOneSale->ed_full, 0.00, 2) == 1) && (bccomp($this->goods_model->hasOneSale->ed_reduction, 0.00, 2) == 1)) {
			$data['name'] = '满减';
			$data['key'] = 'ed_full';
			$data['type'] = 'string';
			$data['value'] = '本商品满￥' . $this->goods_model->hasOneSale->ed_full . '立减￥' . $this->goods_model->hasOneSale->ed_reduction;
		}
		return $data;
	}

	private function getBalanceSale($shopSet)
	{
		$data = [];
		if ($this->goods_model->hasOneSale->award_balance) {
			$data['name'] = $shopSet['credit'] ?: '余额';
			$data['key'] = 'award_balance';
			$data['type'] = 'string';
			$data['value'] = '购买赠送' . $this->goods_model->hasOneSale->award_balance . $data['name'];
		}
		return $data;
	}

	private function getPointSale($shopSet)
	{
		$point = app('GoodsDetail')->make('GoodsDetailInstance')->getPointSet()?:[];
		$data['name'] = $shopSet['credit1'] ?: '积分';
		$data['key'] = 'point';
		$data['type'] = 'array';
		$set = \Setting::get('point.set');
		if ((strlen($this->goods_model->hasOneSale->point) === 0) || $this->goods_model->hasOneSale->point != 0) {
			if ($this->goods_model->hasOneSale->point) {
				$points = $this->goods_model->hasOneSale->point;
			} elseif (!empty($point['value']['set']['give_point']) && $point['value']['set']['give_point'] != 0) {//门店抵扣设置
				$points = $point['value']['set']['give_point'].'%';
			} else {
				$points = $set['give_point'] ? $set['give_point'] : 0;
			}
			if (!empty($points)) {
				$data['value'][] = '购买赠送' . $points . $data['name'];
			}
		}
		//设置不等于0,支持积分抵扣
		//积分抵扣优先级 商品独立设置 ---> 门店设置 ---> 积分统一设置

		if ($set['point_deduct'] && (strlen($this->goods_model->hasOneSale->max_point_deduct) === 0 || $this->goods_model->hasOneSale->max_point_deduct != 0)) {
			if ($this->goods_model->hasOneSale->max_point_deduct) {
				$max_point_deduct = $this->goods_model->hasOneSale->max_point_deduct . '元';
			} elseif (strlen($point['value']['set']['money_max']) !== 0) {
				if (!($point['value']['set']['money_max'] === 0 || $point['value']['set']['money_max'] === '0')) {
					$max_point_deduct = $point['value']['set']['money_max'] . '%';
				}
			} else {
				$max_point_deduct = $set['money_max'] ? $set['money_max'] . '%': 0;
			}

			if (!empty(mb_substr($max_point_deduct, 0,-1))) {
				$data['value'][] = '最高抵扣' . $max_point_deduct;
			}
		}

		if ($set['point_deduct'] && (strlen($this->goods_model->hasOneSale->min_point_deduct) === 0 || $this->goods_model->hasOneSale->min_point_deduct != 0)) {
			if ($this->goods_model->hasOneSale->min_point_deduct) {
				$min_point_deduct = $this->goods_model->hasOneSale->min_point_deduct . '元';
			} else {
				$min_point_deduct = $set['money_min'] ? $set['money_min'] . '%' : 0;
			}
			if (!empty(mb_substr($min_point_deduct, 0,-1))) {
				$data['value'][] = '最少抵扣' . $min_point_deduct;
			}
		}

		if (!empty($data['value'])) {
			return $data;
		}
		return [];
	}

	private function getCouponSale()
	{
		$data = [];
		if ($this->goods_model->hasOneGoodsCoupon->is_give) {
			$data['name'] = '购买返券';
			$data['key'] = 'coupon';
			$data['type'] = 'string';
			$data['value'] = $this->goods_model->hasOneGoodsCoupon->send_type ? '商品订单完成返优惠券' : '每月一号返优惠券';
		}
		return $data;
	}

}