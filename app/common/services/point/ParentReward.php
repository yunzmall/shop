<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/7 11:53 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:
 ****************************************************************/


namespace app\common\services\point;


use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\models\Goods;
use app\common\models\Member;
use app\common\models\point\ParentRewardLog;
use app\common\services\finance\PointService;
use app\common\services\goods\SaleGoods;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yunshop\StoreCashier\common\models\CashierGoods;
use Yunshop\StoreCashier\common\models\StoreOrder;
use Yunshop\StoreCashier\common\models\StoreSetting;

class ParentReward
{
    public $order;
    public $parent;
    public $grand;
    public $base_setting;
    public $point_setting;
    public $event;


    /**
     * @param $order
     * @param $event
     * @return void
     * 上级赠送积分
     */
    public function handle($order, $event)
    {

        $this->point_setting = Setting::get('point.set');
        $this->order = $order;
        $this->getMemberParent();
        $this->getBaseSetting();
        $this->event = $event;

        if (!$this->parent) {
            $this->debug('会员不存在上级');
            return;
        }
        $this->order->orderGoods->each(function ($v) {
            if ($v->isRefund()) {
                $this->debug('已售后订单商品不赠送上级积分', $v->id);
                return;
            }
            $goods_setting = $this->goodsSetting($v);
            if (!$goods_setting['base_amount']) {
                $this->debug('利润或实付金额为0', $v->id);
                return;
            }
            $res1 = $this->createCommission($goods_setting, $v, 1);
            $res2 = $this->createCommission($goods_setting, $v, 2);
            if ($res1 || $res2) {
                $this->award($this->order->id);
            }
        });
    }


    /**
     * @param $goods_setting
     * @param $order_goods
     * @param $level
     * @return bool
     * 生成奖励记录
     */
    protected function createCommission($goods_setting, $order_goods, $level)
    {
        if (ParentRewardLog::uniacid()->where('level', $level)->where('order_goods_id', $order_goods->id)->first()) {
            $this->debug('重复奖励', $order_goods->id);
            return false;
        }

        if ($goods_setting['point_type'] == 2) {
            if ($this->event != 'pay') {
                $this->debug('支付后赠送且当前非支付事件', $order_goods->id);
                return false;
            }
            if ($this->order->status < 1) {
                $this->debug('支付后赠送且订单未支付', $order_goods->id);
                return false;
            }
        }

        if ($goods_setting['point_type'] == 1) {
            if ($this->event != 'receive') {
                $this->debug('每月赠送且当前非完成事件', $order_goods->id);
                return false;
            }
            if ($this->order->status < 3) {
                $this->debug('每月赠送且订单未完成', $order_goods->id);
                return false;
            }
        }

        if ($goods_setting['point_type'] == 0) {
            if ($this->event != 'receive') {
                $this->debug('完成后赠送且当前非完成事件', $order_goods->id);
                return false;
            }
            if ($this->order->status < 3) {
                $this->debug('完成后赠送且订单未完成', $order_goods->id);
                return false;
            }
        }


        $member = $level == 1 ? $this->parent : $this->grand;
        if (!$member) {
            $this->debug($level . '级上级不存在', $order_goods->id);
            return false;
        }

        $type_key = ($level == 1 ? 'first' : 'second') . '_type';
        $number_key = ($level == 1 ? 'first' : 'second') . '_number';

        if ($goods_setting[$type_key] == 1) {
            $point = bcmul($goods_setting['base_amount'], bcdiv($goods_setting[$number_key], 100, 8), 2);
        } else {
            $point = bcmul($goods_setting[$number_key], $order_goods->total, 2);
        }

        $point = bccomp($point, 0, 2) == 1 ? $point : 0;
        if (!$point) {
            $this->debug($level . '级上级奖励积分为0', $order_goods->id);
            return false;
        }

        $create_data = [
            'uniacid' => \YunShop::app()->uniacid,
            'uid' => $member->uid,
            'order_id' => $this->order->id,
            'order_goods_id' => $order_goods->id,
            'expect_reward_time' => 0,
            'status' => 0,
            'level' => $level,
            'point' => $point,
        ];

        if ($goods_setting['point_type'] == 1) { //每月初发放
            if (bccomp($goods_setting['max_once_point'], 0, 2) != 1) {
                $this->debug('每月赠送积分为0', $order_goods->id);
                return false;
            }
            $insert_data = [];
            $time = time();
            do {
                $this_point = bccomp($goods_setting['max_once_point'], $create_data['point'], 2) == 1 ? $create_data['point'] : $goods_setting['max_once_point'];
                $create_data['point'] = bcsub($create_data['point'], $this_point, 2);
                $time = Carbon::createFromTimestamp($time)->endOfMonth()->timestamp + 1;
                $insert_data[] = array_merge($create_data, [
                    'point' => $this_point,
                    'expect_reward_time' => $time,
                ]);
            } while ($create_data['point'] > 0);
            if ($insert_data) {
                $insert_data = array_chunk($insert_data, 500);
                foreach ($insert_data as $insert) {
                    ParentRewardLog::insert($insert);
                }
            }
        } else { //立刻发放
            ParentRewardLog::create($create_data);
        }

        return true;

    }


    /**
     * @return void
     * 获取2层内上级
     */
    protected function getMemberParent()
    {
        $this->parent = null;
        $this->grand = null;

        $member = Member::uniacid()->with('yzMember')->find($this->order->uid);
        if ($member->yzMember->parent_id) {
            $this->parent = Member::uniacid()->with('yzMember')->find($member->yzMember->parent_id);
        }
        if ($this->parent && $this->parent->yzMember->parent_id) {
            $this->grand = Member::uniacid()->find($this->parent->yzMember->parent_id);
        }
    }


    /**
     * @return int[]
     * 获取奖励基础设置
     */
    protected function getBaseSetting()
    {
        $setting = $this->analysisSetting($this->point_setting['first_parent_point'], $this->point_setting['second_parent_point']);
        if ($this->order->plugin_id == 32) {
            if (app('plugins')->isEnabled('store-cashier')
                && ($store_id = StoreOrder::where('order_id', $this->order->id)->value('store_id'))
                && $store_setting = StoreSetting::where('store_id', $store_id)->where('key', 'point')->first()
            ) {
                $store_setting = $store_setting->value['set'] ? : [];
                $setting = $this->formSetting($this->analysisSetting($store_setting['first_parent_point'], $store_setting['second_parent_point']), $setting);
            }
        }
//        elseif ($this->order->plugin_id == 31) {
//            if (!app('plugins')->isEnabled('store-cashier')) {
//                return $this->analysisSetting(0, 0);
//            }
//            $goods_id = $this->order->orderGoods->first()->goods_id;
//            $setting = $this->formSetting($this->goodsSetting($goods_id), $setting, 1);
//        }
        $this->base_setting = $setting;
        return $this->base_setting;
    }


    /**
     * @param $setting
     * @param $base_setting
     * @param $form_type
     * @return mixed
     * 组装设置
     */
    protected function formSetting($setting, $base_setting, $form_type = 0)
    {
        if ($setting['first_number']) {
            $base_setting['first_number'] = $setting['first_number'];
            $base_setting['first_type'] = $setting['first_type'];
        }
        if ($setting['second_number']) {
            $base_setting['second_number'] = $setting['second_number'];
            $base_setting['second_type'] = $setting['second_type'];
        }
        if ($form_type == 1) {
            if (!$setting['first_number']) {
                $base_setting['first_number'] = 0;
            }
            if (!$setting['second_number']) {
                $base_setting['second_number'] = 0;
            }
        }
        if ($form_type == 2) {
            if ($setting['first_number'] === 0 || $setting['first_number'] === '0') {
                $base_setting['first_number'] = 0;
            }
            if ($setting['second_number'] === 0 || $setting['second_number'] === '0') {
                $base_setting['second_number'] = 0;
            }
        }
        return $base_setting;
    }

    /**
     * @param $order_goods
     * @return int|mixed|string
     * 获取奖励基础金额
     */
    protected function getBaseAmount($order_goods)
    {
        if ($this->point_setting['give_type'] == 1) { //利润
            $base_amount = 0;
            switch ($this->order->plugin_id) {
                case 31:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $cashier_goods = CashierGoods::where('goods_id', $order_goods->goods_id)->first();
                        if (bccomp($cashier_goods->shop_commission, 0, 8) == 1) {
                            $base_amount = bcmul($order_goods->payment_amount, bcdiv($cashier_goods->shop_commission, 100, 8), 2);
                        }
                    }
                    break;
                case 32:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $store_id = StoreOrder::where('order_id', $this->order->id)->value('store_id');
                        $store_setting = StoreSetting::where('store_id', $store_id)->where('key', 'store')->first();
                        $percent = $store_setting->value['shop_commission'] ?: 0;
                        if (bccomp($percent, 0, 8) == 1) {
                            $base_amount = bcmul($order_goods->payment_amount, bcdiv($percent, 100, 8), 2);
                        }
                    }
                    break;
                default:
                    $base_amount = bcsub($order_goods->payment_amount, $order_goods->goods_cost_price, 2);
            }
        } else { //实付价格
            $base_amount = $order_goods->payment_amount;
        }

        return bccomp($base_amount, 0, 2) == 1 ? $base_amount : 0;
    }

    /**
     * @param $order_goods
     * @return mixed
     * 获取商品独立设置
     */
    protected function goodsSetting($order_goods)
    {
        $goods_id = $order_goods->goods_id;
        $goods_sale = SaleGoods::where('goods_id', $goods_id)->first();
        $setting = $this->base_setting;
        if ($goods_sale) {
            $goods_setting = $this->analysisSetting($goods_sale->first_parent_point, $goods_sale->second_parent_point, 1);
            $setting = $this->formSetting($goods_setting, $setting, $this->order->plugin_id == 31 ? 1 : 2);
        }
        $setting['point_type'] = $goods_sale->point_type ?: 0;
        $setting['max_once_point'] = $goods_sale->max_once_point && bccomp($goods_sale->max_once_point, 0, 2) == 1 ? $goods_sale->max_once_point : 0;
        $setting['base_amount'] = $this->getBaseAmount($order_goods);
        return $setting;
    }


    /**
     * @param $first_percent
     * @param $second_percent
     * @return int[]
     * 解析设置
     */
    protected function analysisSetting($first_percent = '', $second_percent = '', $type = 0)
    {
        $setting = [
            'first_number' => 0,
            'second_number' => 0,
            'first_type' => 1,
            'second_type' => 1,
        ];
        foreach (['first', 'second'] as $v) {
            $percent_key = $v . '_percent';
            if ($type == 1 && !$$percent_key && $$percent_key !== 0 && $$percent_key !== '0') {
                $setting[$v . '_number'] = '';
            } elseif (floatval($$percent_key) && bccomp(floatval($$percent_key), 0, 2) == 1) {
                if (strstr($$percent_key, '%') === false) {
                    $setting[$v . '_type'] = 2;
                }
                $setting[$v . '_number'] = floatval($$percent_key);
            }
        }
        return $setting;
//        if (floatval($first_percent) && bccomp(floatval($first_percent), 0, 2) == 1) {
//            if (strstr($first_percent, '%') === false) {
//                $setting['first_type'] = 2;
//            }
//            $setting['first_number'] = floatval($first_percent);
//        }
    }


    /**
     * @param $order_id
     * @param $order_goods_id
     * @return void
     * 上级赠送积分回滚
     */
    public function refund($order_id = 0, $order_goods_id = 0)
    {
        $query = ParentRewardLog::uniacid()->where('status', '>=', 0);
        if (empty($order_id) && empty($order_goods_id)) {
            return;
        }
        if ($order_id) {
            $function = is_array($order_id) ? 'whereIn' : 'where';
            $query->$function('order_id', $order_id);
        }
        if ($order_goods_id) {
            $function = is_array($order_id) ? 'whereIn' : 'where';
            $query->$function('order_goods_id', $order_goods_id);
        }
        $list = $query->get();
        if ($list->isNotEmpty()) {
            $list->each(function ($v) {
                if ($v->status == 0) {
                    $v->status = -1;
                    $v->save();
                } elseif ($v->status == 1) {
                    try {
                        $data = [
                            'point_income_type' => PointService::POINT_INCOME_LOSE,
                            'member_id' => $v->uid,
                            'point_mode' => $v->level == 1 ? PointService::POINT_MODE_FIRST_PARENT_REFUND : PointService::POINT_MODE_SECOND_PARENT_REFUND,
                            'point' => bcsub(0, $v->point, 2),
                            'remark' => "购物赠送上级({$v->level}级)回退,订单ID{$v->order_id},订单商品表ID{$v->order_goods_id},记录ID{$v->id}",
                        ];
                        $pointService = new PointService($data);
                        $res = $pointService->changePoint($v->id);
                        if ($res === false) {
                            throw new ShopException('未知错误');
                        }
                        $v->status = -1;
                        $v->save();
                        DB::commit();
                    } catch (ShopException $e) {
                        DB::rollBack();
                        \Log::debug('积分上级赠送回滚失败', $v->id);
                    }
                }
            });
        }
    }


    /**
     * @param $order_id
     * @return void
     * 发放奖励
     */
    public function award($order_id = 0)
    {
        $query = ParentRewardLog::uniacid()
            ->where('status', 0)
            ->where('expect_reward_time', '<', time());
        if ($order_id) {
            $query->where('order_id', $order_id);
        } else {
            $query->limit(500);
        }
        $list = $query->get();

        if ($list->isNotEmpty()) {
            $list->each(function ($v) {
                try {
                    DB::beginTransaction();
                    $data = [
                        'point_income_type' => PointService::POINT_INCOME_GET,
                        'member_id' => $v->uid,
                        'point_mode' => $v->level == 1 ? PointService::POINT_MODE_FIRST_PARENT_REWARD : PointService::POINT_MODE_SECOND_PARENT_REWARD,
                        'point' => $v->point,
                        'remark' => "购物赠送上级({$v->level}级),订单ID{$v->order_id},订单商品表ID{$v->order_goods_id},记录ID{$v->id}",
                    ];
                    $pointService = new PointService($data);
                    $res = $pointService->changePoint($v->id);
                    if ($res === false) {
                        throw new ShopException('未知错误');
                    }
                    $v->status = 1;
                    $v->actual_reward_time = time();
                    $v->save();
                    DB::commit();
                } catch (ShopException $e) {
                    DB::rollBack();
                    $this->debug('发放积分失败,' . $e->getMessage(), $v->id);
                }
            });
        }

    }


    public function debug($msg = '', $data = '')
    {
        $base_msg = $this->order->id ? "上级赠送积分异常,订单{$this->order->id},原因:" : "上级赠送积分异常,原因:";
        \Log::debug($base_msg . $msg, $data);
    }

}
