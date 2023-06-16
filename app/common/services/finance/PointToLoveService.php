<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/11/22 下午2:10
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\common\services\finance;


use app\common\facades\Setting;
use app\common\models\Member;
use app\common\models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yunshop\Love\Common\Services\LoveChangeService;

class PointToLoveService
{
	private $pointSet;

	private $pointAmount;

	private $orderCycleAmount;

	public function handleTransferQueue($uniacid)
	{
		Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniacid;

		$result = $this->transferStart();
		if ($result !== true) {
			\Log::info('--积分自动转入爱心值Uniacid:' . $uniacid . '自动转入失败--');
		}
		\Log::info('--积分自动转入爱心值Uniacid:' . $uniacid . '自动转入完成--');
	}

	private function pointSet()
	{
		return Setting::get('point.set');
	}

	/**
	 * 积分持有总和，作为除数，不能返回零
	 *
	 * @return float
	 */
	private function pointAmount()
	{
		if (!isset($this->pointAmount)) $this->pointAmount = $this->_pointAmount();

		return $this->pointAmount ?: 1;
	}

	/**
	 * _积分持有总和
	 *
	 * @return float
	 */
	private function _pointAmount()
	{
		//todo 需要排除已删除会员持有
		return Member::uniacid()->sum('credit1');
	}

	/**
	 * 周期订单总和
	 *
	 * @return float
	 */
	private function orderCycleAmount()
	{
		if (!isset($this->orderCycleAmount)) $this->orderCycleAmount = $this->_orderCycleAmount();

		return $this->orderCycleAmount;
	}

	/**
	 * _周期订单总和
	 *
	 * @return float
	 */
	private function _orderCycleAmount()
	{
		return Order::uniacid()->whereBetween('finish_time', $this->getTimeSlot())->sum('price');
	}

	/**
	 * 周期时间：每周/每天
	 *
	 * @return array
	 */
	private function getTimeSlot()
	{
		if ($this->pointSet['transfer_cycle'] == 1) {
			$startTime = Carbon::now()->subWeek(1)->startOfWeek()->timestamp;
			$endTime = Carbon::now()->subWeek(1)->endOfWeek()->timestamp;
		} else {
			$startTime = Carbon::yesterday()->startOfDay()->timestamp;
			$endTime = Carbon::yesterday()->endOfDay()->timestamp;
		}
		return [$startTime, $endTime];
	}


	private function changeValue($memberPoint, $rate)
	{
		if ($this->pointSet['transfer_compute_mode'] == 1) {

			$amount = bcdiv(bcmul($this->orderCycleAmount(), $rate, 4), 100, 4);

			$point = bcmul(bcdiv($amount, $this->pointAmount(), 4), $memberPoint, 2);

		} else {
			$point = bcdiv(bcmul($memberPoint, $rate, 4), 100, 2);
		}
		return $point;
	}

	public function transferStart()
	{
		$this->pointSet = $this->pointSet();

		$members = Member::uniacid()->where('credit1', '>', 0)->with('pointLove')->get();


		foreach ($members as $key => $member) {
			$rate = $this->getRate($member);
			$change_value = $this->changeValue($member->credit1, $rate);

			if ($change_value <= 0) {
				continue;
			}
			DB::beginTransaction();
			try {
				$point_change_data = [
					'point_income_type' => PointService::POINT_INCOME_LOSE,
					'point_mode' => PointService::POINT_MODE_TRANSFER_LOVE,
					'member_id' => $member->uid,
					'point' => -$change_value,
					'remark' => '积分自动转入：' . $change_value . '转入比例：' . $rate,
				];
				//修改用户积分
				$result = (new PointService($point_change_data))->changePoint();

				if (!$result) {
					Log::info('积分自动转入爱心值失败', print_r($point_change_data, true));
					DB::rollBack();
					continue;
				}
				$change_value = $this->getExchange($member, $change_value);
				$love_change_data = [
					'member_id' => $member->uid,
					'change_value' => $change_value,
					'operator' => 0,
					'operator_id' => 0,
					'remark' => '积分自动转入：' . $change_value . '转入比例：' . $rate,
					'relation' => ''
				];

				//修改爱心值
				$result = (new LoveChangeService())->pointTransfer($love_change_data);
				if (!$result) {
					Log::info('积分自动转入爱心值失败', print_r($love_change_data, true));
					DB::rollBack();
					continue;
				}
				DB::commit();
			} catch (\Exception $e) {
				Log::info('积分自动转入爱心值失败' . $e->getMessage(), print_r($love_change_data, true));
				DB::rollBack();
				continue;
			}

		}

		return true;
	}


	private function getRate($memberModel)
	{
		//如果转入类型是营业额，直接只用全局比例
		if ($this->pointSet['transfer_compute_mode'] == 1) {
			return $this->pointSet['transfer_love_rate'] ?: 0;
		}

		$set = $this->pointSet;

		$rate = 0;
		//如果全局比例为空、为零
		if (empty($set['transfer_love_rate'])) {
			$rate = 0;
		}

		//全局比例设置
		if (isset($set['transfer_love_rate']) && $set['transfer_love_rate'] > 0) {
			$rate = $set['transfer_love_rate'];
		}

		//会员独立设置判断
		if (isset($memberModel->pointLove) && $memberModel->pointLove->rate > 0) {
			$rate = $memberModel->pointLove->rate;

		}

		//独立设置为 -1，跳过此会员
		if (isset($memberModel->pointLove) && $memberModel->pointLove->rate == -1) {
			$rate = 0;
		}

		return $rate;
	}


	private function getExchange($memberModel, $change_value)
	{
		$set = Setting::get('point.set');

		$transfer_integral = 1;

		$transfer_love = 1;

		//如果全局比例为空
		if (empty($set['transfer_integral']) || empty($set['transfer_integral_love'])) {
			$transfer_integral = 1;

			$transfer_love = 1;
		}

		//全局比例设置
		if (isset($set['transfer_integral']) && $set['transfer_integral'] > 0) {
			$transfer_integral = $set['transfer_integral'];
		}

		//全局比例设置
		if (isset($set['transfer_integral_love']) && $set['transfer_integral_love'] > 0) {
			$transfer_love = $set['transfer_integral_love'];
		}

		//会员独立设置判断
		if (isset($memberModel->pointLove) && $memberModel->pointLove > 0) {
			//判断会员是否单独设置积分转入爱心值比例
			if ($memberModel->pointLove->transfer_love && $memberModel->pointLove->transfer_integral) {

				$transfer_love = $memberModel->pointLove->transfer_love;

				$transfer_integral = $memberModel->pointLove->transfer_integral;
			}
		}

		$rate = bcmul(bcdiv($transfer_love, $transfer_integral, 4), $change_value, 2);

		return $rate;

	}

}
