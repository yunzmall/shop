<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/1/3
 * Time: 9:18
 */

namespace app\common\services\goods;

use Carbon\Carbon;
use app\common\models\Goods;
use app\common\models\Order;
use app\common\models\Member;
use Illuminate\Support\Facades\DB;
use app\common\models\GoodsOption;
use app\common\models\MemberGroup;
use app\common\models\MemberLevel;
use app\common\models\goods\Privilege;
use app\common\exceptions\AppException;


class GoodsOptionBuyLimit
{
    /**
     * @var Privilege
     */
    public $privilege;


    /**
     * @var GoodsOption
     */
    public $goodsOption;


    public function __construct(Privilege $privilege, GoodsOption $goodsOption)
    {
        $this->privilege = $privilege;

        $this->goodsOption = $goodsOption;
    }

    public function getGoodsTitle()
    {
        $name = '';

        if ($this->goodsOption->goods) {
            $name .= $this->goodsOption->goods->title.'|';
        } else {
            $name .= 'ID:'.$this->goodsOption->goods_id.'|';
        }

        $name .= $this->goodsOption->title;

        return  $name;
    }


    public function getOrderGoodsWhere()
    {
        return  app('db')->getTablePrefix()."yz_order_goods.goods_option_id = {$this->goodsOption->id}";
    }

    /**
     * @param Member $member
     * @param $num
     * @throws AppException
     */
    public function goodsValidate(Member $member, $num)
    {

        //不在限制的规格内
        if (!in_array($this->goodsOption->id, $this->privilege->option_id_array)) {
            return;
        }


        $this->validateMinBuyLimit($num);
        $this->validateDayBuyTotalLimit($num);
        $this->validateOneBuyLimit($num);
        $this->validateDayBuyLimit($member,$num);
        $this->validateWeekBuyLimit($member,$num);
        $this->validateMonthBuyLimit($member,$num);
        $this->validateTotalBuyLimit($member,$num);
        $this->validateMemberLevelLimit($member);
        $this->validateMemberGroupLimit($member);
        $this->validateBuyMultipleLimit($num);
        $this->validateTimeLimits($member,$num);
    }

    /**
     * 商品单次购买最低数量
     * @param $num
     * @throws AppException
     */
    public function validateMinBuyLimit($num)
    {
        //只做平台商品验证，解除限制所以商品都会验证
        if (intval($this->privilege->min_buy_limit) > 0) {

            if ($num < $this->privilege->min_buy_limit) {
                throw new AppException('商品:(' . $this->getGoodsTitle() . '),未到达最低购买数量' . $this->privilege->min_buy_limit . '件');
            }
        }
    }

    /**
     * 商品每日购买限制
     * @param Member $member
     * @param int $num
     * @throws AppException
     */
    public function validateDayBuyTotalLimit($num = 1)
    {


        if ($this->privilege->day_buy_total_limit > 0) {
            $start_time = Carbon::today()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $rang = [$start_time,$end_time];
            $history_num =
                \app\common\models\OrderGoods::select('yz_order_goods.*')
                    ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                    ->whereRaw($this->getOrderGoodsWhere())
                    ->where('yz_order.status', '!=' ,Order::CLOSE)
                    ->whereBetween('yz_order_goods.created_at',$rang)
                    ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->privilege->day_buy_total_limit) {
                throw new AppException('商品(' . $this->getGoodsTitle() . ')每日最多售出' . $this->privilege->day_buy_total_limit . '件');
            }
        }
    }


    /**
     * 用户单次购买限制
     * @param $num
     * @throws AppException
     */
    public function validateOneBuyLimit($num = 1)
    {
        if ($this->privilege->once_buy_limit > 0) {
            if ($num > $this->privilege->once_buy_limit) {
                throw new AppException('商品(' . $this->getGoodsTitle() . ')单次最多可购买' . $this->privilege->once_buy_limit . '件');
            }
        }
    }

    /**
     * 用户每日购买限制
     * @param Member $member
     * @param int $num
     * @throws AppException
     */
    public function validateDayBuyLimit(Member $member,$num = 1)
    {

        if ($this->privilege->day_buy_limit > 0) {
            $start_time = Carbon::today()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $rang = [$start_time,$end_time];
            $history_num = $member
                ->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->whereRaw($this->getOrderGoodsWhere())
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$rang)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->privilege->day_buy_limit) {
                throw new AppException('您今天已购买' . $history_num . '件商品(' . $this->getGoodsTitle() . '),该商品每天最多可购买' . $this->privilege->day_buy_limit . '件');
            }
        }
    }

    /**
     * 用户每周购买限制
     * @param Member $member
     * @param int $num
     * @throws AppException
     */
    public function validateWeekBuyLimit(Member $member,$num = 1)
    {
        if ($this->privilege->week_buy_limit > 0) {
            $start_time = Carbon::now()->startOfWeek()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $rang = [$start_time,$end_time];
            $history_num = $member
                ->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->whereRaw($this->getOrderGoodsWhere())
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$rang)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->privilege->week_buy_limit) {
                throw new AppException('您这周已购买' . $history_num . '件商品(' . $this->getGoodsTitle() . '),该商品每周最多可购买' . $this->privilege->week_buy_limit . '件');
            }
        }
    }

    /**
     * 用户每月购买限制
     * @param Member $member
     * @param int $num
     * @throws AppException
     */
    public function validateMonthBuyLimit(Member $member,$num = 1)
    {
        if ($this->privilege->month_buy_limit > 0) {
            $start_time = Carbon::now()->startOfMonth()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $range = [$start_time,$end_time];

            $history_num = $member
                ->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->whereRaw($this->getOrderGoodsWhere())
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$range)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->privilege->month_buy_limit) {
                throw new AppException('您这个月已购买' . $history_num . '件商品(' . $this->getGoodsTitle() . '),该商品每月最多可购买' . $this->privilege->month_buy_limit . '件');
            }
        }
    }

    /**
     * 用户购买总数限制
     * @param Member $member
     * @param int $num
     * @throws AppException
     */
    public function validateTotalBuyLimit(Member $member,$num = 1)
    {
        if ($this->privilege->total_buy_limit > 0) {
            $history_num = $member->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->whereRaw($this->getOrderGoodsWhere())
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->sum('yz_order_goods.total');

            if ($history_num + $num > $this->privilege->total_buy_limit) {
                throw new AppException('您已购买' . $history_num . '件商品(' . $this->getGoodsTitle() . '),最多可购买' . $this->privilege->total_buy_limit . '件');
            }
        }

    }

    /**
     * 用户等级限制
     * @param Member $member
     * @throws AppException
     */
    public function validateMemberLevelLimit(Member $member)
    {

        if (empty($this->privilege->buy_levels) && $this->privilege->buy_levels !== '0') {
            return;
        }

        $buy_levels = explode(',', $this->privilege->buy_levels);

        if ($this->privilege->buy_levels !== '0') {
            $level_names = MemberLevel::select(DB::raw('group_concat(level_name) as level_name'))->whereIn('id', $buy_levels)->value('level_name');
            if (empty($level_names)) {
                return;
            }
        }
        if (!in_array($member->yzMember->level_id, $buy_levels)) {
            $ordinaryMember = in_array('0', $buy_levels)? '普通会员 ':'';

            throw new AppException('商品(' . $this->getGoodsTitle() . ')仅限' . $ordinaryMember.$level_names . '购买');
        }
    }

    /**
     * 用户组限购
     * @param Member $member
     * @throws AppException
     */
    public function validateMemberGroupLimit(Member $member)
    {
        if (empty($this->privilege->buy_groups)) {
            return;
        }
        $buy_groups = explode(',', $this->privilege->buy_groups);
        $group_names = MemberGroup::select(DB::raw('group_concat(group_name) as level_name'))->whereIn('id', $buy_groups)->value('level_name');
        if (empty($group_names)) {
            return;
        }
        if (!in_array($member->yzMember->group_id, $buy_groups)) {
            throw new AppException('(' . $this->getGoodsTitle() . ')该商品仅限[' . $group_names . ']购买');
        }
    }

    /**
     * 用户单次购买倍数限制
     * @param $num
     * @throws AppException
     */
    public function validateBuyMultipleLimit($num = 1)
    {
        //只做平台商品验证，解除限制所以商品都会验证
        if (intval($this->privilege->buy_multiple) > 0) {

            if ($num % $this->privilege->buy_multiple != 0) {
                throw new AppException('商品:(' . $this->getGoodsTitle() . '),购买数量需为' . $this->privilege->buy_multiple . '的倍数');
            }
        }
    }

    /**
     * 限时段购买
     * @param Member $member
     * @param $num
     * @return void
     * @throws AppException
     */
    public function validateTimeLimits(Member $member,$num = 1)
    {
        //限购时段开启验证
        if ($this->privilege->buy_limit_status == 1) {
            $now_time = time();

            $time_limits = $this->privilege->time_limits;
            $existTime = false;//是否存在时间段内
            foreach ($time_limits as $limit_time_arr) {
                $start_time = $limit_time_arr['time_limit'][0] / 1000;
                $end_time = $limit_time_arr['time_limit'][1] / 1000;
                if ($now_time >= $start_time && $now_time < $end_time) {
                    $history_num = $member->orderGoods()
                        ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                        ->where('yz_order_goods.goods_id', $this->goodsOption->goods_id)
                        ->where('yz_order.status', '!=' ,Order::CLOSE)
                        ->whereBetween('yz_order_goods.created_at',[$start_time,$end_time])
                        ->sum('yz_order_goods.total');

                    $existTime = true;
                    if ($history_num + $num > $limit_time_arr['limit_number']) {
                        throw new AppException('商品(' . $this->getGoodsTitle() . '),限购' . $limit_time_arr['limit_number'] . '件');
                    }
                }
            }

            if (!$existTime) {
                throw new AppException('商品(' . $this->getGoodsTitle() . ')未到购买时间暂不能购买');
            }
        }

    }
}