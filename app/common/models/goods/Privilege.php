<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/2/28
 * Time: 上午10:54
 */

namespace app\common\models\goods;


use app\common\exceptions\AppException;
use app\common\models\BaseModel;
use app\common\models\Goods;
use app\common\models\GoodsOption;
use app\common\models\Member;
use app\common\models\MemberGroup;
use app\common\models\MemberLevel;
use app\common\models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use app\common\facades\Setting;

/**
 * Class Privilege
 * @package app\common\models\goods
 * @property int goods_id
 * @property string option_buy_limit
 * @property array option_id_array
 * @property string show_levels
 * @property string show_groups
 * @property string buy_levels
 * @property string buy_groups
 * @property int once_buy_limit
 * @property int total_buy_limit
 * @property int day_buy_limit
 * @property Carbon time_begin_limit
 * @property Carbon time_end_limit
 * @property int enable_time_limit
 * @property int week_buy_limit
 * @property int month_buy_limit
 * @property Goods goods

 */
class Privilege extends BaseModel
{
    public $table = 'yz_goods_privilege';

    static protected $needLog = true;

    public $attributes = [
        'show_levels' => '',
        'show_groups' => '',
        'buy_levels' => '',
        'buy_groups' => '',
        'option_buy_limit'=> '',
        'once_buy_limit' => 0,
        'total_buy_limit' => 0,
        'day_buy_limit' => 0,
        'week_buy_limit' => 0,
        'month_buy_limit' => 0,
        'time_begin_limit' => 0,
        'time_end_limit' => 0,
        'enable_time_limit' => 0,
        'min_buy_limit' => 0,
        'day_buy_total_limit' => 0,
        'buy_multiple' => 0,
        'buy_limit_status' => 0,
        'buy_limit_name' => '',
        'time_limits' => []
    ];
    /**
     *  不可填充字段.
     *
     * @var array
     */
    protected $guarded = ['created_at', 'updated_at','time_begin_limit','time_end_limit'];

    /**
     * 获取商品权限信息
     * @param $goodsId
     * @return self
     */
    public static function getGoodsPrivilegeInfo($goodsId)
    {
        $goodsPrivilegeInfo = self::where('goods_id', $goodsId)
            ->first();
        return $goodsPrivilegeInfo;
    }


    /**
     * 自定义字段名
     * 可使用
     * @return array
     */
    public function atributeNames()
    {
        return [
            'show_levels' => '会员浏览等级',
            'show_groups' => '会员浏览分组',
            'buy_levels' => '会员购买等级',
            'buy_groups' => '会员购买分组',
            'once_buy_limit' => '单次购买限制',
            'total_buy_limit' => '总购买限制',
            'day_buy_limit' => '每天购买限制',
            'week_buy_limit' => '每周购买限制',
            'month_buy_limit' => '每月购买限制',
            'time_begin_limit' => '限时起始时间',
            'time_end_limit' => '限时结束时间',
            'min_buy_limit' => '会员起购数量',
            'buy_multiple' => '会员购买倍数',
            'buy_limit_status' => '限购时段',
            'buy_limit_name' => '自定义前端显示名称',
            'time_limits' => '限购时段时间'
        ];
    }


    public function rules()
    {
        return [
            'show_levels' => '',
            'show_groups' => '',
            'buy_levels' => '',
            'buy_groups' => '',
            'once_buy_limit' => 'numeric',
            'total_buy_limit' => 'numeric',
            'day_buy_limit' => 'numeric',
            'week_buy_limit' => 'numeric',
            'month_buy_limit' => 'numeric',
            'time_begin_limit' => '',
            'time_end_limit' => '',
            'min_buy_limit' => 'numeric',
            'buy_multiple' => 'numeric',
            'buy_limit_status' => 'numeric',
            'buy_limit_name' => '',
            'time_limits' => 'array',
        ];
    }
    protected $casts = [
        'time_begin_limit' => 'datetime',
        'time_end_limit' => 'datetime',
        'time_limits' => 'json'
    ];

    public function goods()
    {
        return $this->belongsTo(Goods::class);
    }



    public function getOptionIdArrayAttribute()
    {
        return array_filter(explode(',', $this->option_buy_limit), function ($item) {
            return !empty($item);
        });
    }


    /**
     *
     * @param Member $member
     * @param $num
     * @throws AppException
     */
    public function validate(Member $member,$num)
    {
        $this->validateTimeLimit();

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
     * 开启规格权限验证按指定商品规格判断
     * @param GoodsOption $goodsOption
     * @param Member $member
     * @param $num
     * @throws AppException
     */
    public function optionValidate(GoodsOption $goodsOption,Member $member,$num)
    {
        $this->validateTimeLimit();
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
        if (intval($this->min_buy_limit) > 0) {

            if ($num < $this->min_buy_limit) {
                throw new AppException('商品:(' . $this->goods->title . '),未到达最低购买数量' . $this->min_buy_limit . '件');
            }
        }
    }

    /**
     * todo 这个好像没用了，因限时购的数据记录不在这张表了，yz_goods_limitbuy 这张表记录和验证
     * 限时购
     * @throws AppException
     */
    public function validateTimeLimit()
    {
        if ($this->enable_time_limit) {
            if (Carbon::now()->lessThan($this->time_begin_limit)) {
                throw new AppException('商品(' . $this->goods->title . ')将于' . $this->time_begin_limit->toDateTimeString() . '开启限时购买');
            }
            if (Carbon::now()->greaterThanOrEqualTo($this->time_end_limit)) {
                throw new AppException('商品(' . $this->goods->title . ')该商品已于' . $this->time_end_limit->toDateTimeString() . '结束限时购买');
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

        if ($this->day_buy_total_limit > 0) {
            $start_time = Carbon::today()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $rang = [$start_time,$end_time];
            $history_num =
                \app\common\models\OrderGoods::select('yz_order_goods.*')
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->where('yz_order_goods.goods_id', $this->goods_id)
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$rang)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->day_buy_total_limit) {
                throw new AppException('商品(' . $this->goods->title . ')每日最多售出' . $this->day_buy_total_limit . '件');
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
        if ($this->once_buy_limit > 0) {
            if ($num > $this->once_buy_limit) {
                throw new AppException('商品(' . $this->goods->title . ')单次最多可购买' . $this->once_buy_limit . '件');
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

        if ($this->day_buy_limit > 0) {
            $start_time = Carbon::today()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $rang = [$start_time,$end_time];
            $history_num = $member
                ->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->where('yz_order_goods.goods_id', $this->goods_id)
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$rang)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->day_buy_limit) {
                throw new AppException('您今天已购买' . $history_num . '件商品(' . $this->goods->title . '),该商品每天最多可购买' . $this->day_buy_limit . '件');
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
        if ($this->week_buy_limit > 0) {
            $start_time = Carbon::now()->startOfWeek()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $rang = [$start_time,$end_time];
            $history_num = $member
                ->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->where('yz_order_goods.goods_id', $this->goods_id)
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$rang)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->week_buy_limit) {
                throw new AppException('您这周已购买' . $history_num . '件商品(' . $this->goods->title . '),该商品每周最多可购买' . $this->week_buy_limit . '件');
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
        if ($this->month_buy_limit > 0) {
            $start_time = Carbon::now()->startOfMonth()->timestamp;
            $end_time = Carbon::now()->timestamp;
            $range = [$start_time,$end_time];

            // 购买限制不查询关闭的订单
            // $orderIds = Order::select(['id', 'uid', 'status', 'created_at'])
            //     ->where('uid', $member->uid)
            //     ->where('status', '!=' ,Order::CLOSE)
            //     ->whereBetween('created_at',$range)
            //     ->pluck('id');
            //
            // $history_num = $member
            //     ->orderGoods()
            //     ->where('goods_id', $this->goods_id)
            //     ->whereBetween('created_at',$range)
            //     ->whereIn('order_id', $orderIds)
            //     ->sum('total');

            $history_num = $member
                ->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->where('yz_order_goods.goods_id', $this->goods_id)
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->whereBetween('yz_order_goods.created_at',$range)
                ->sum('yz_order_goods.total');
            if ($history_num + $num > $this->month_buy_limit) {
                throw new AppException('您这个月已购买' . $history_num . '件商品(' . $this->goods->title . '),该商品每月最多可购买' . $this->month_buy_limit . '件');
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
        if ($this->total_buy_limit > 0) {
            $history_num = $member->orderGoods()
                ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                ->where('yz_order_goods.goods_id', $this->goods_id)
                ->where('yz_order.status', '!=' ,Order::CLOSE)
                ->sum('yz_order_goods.total');

            if ($history_num + $num > $this->total_buy_limit) {
                throw new AppException('您已购买' . $history_num . '件商品(' . $this->goods->title . '),最多可购买' . $this->total_buy_limit . '件');
            }
        }


        // if ($this->total_buy_limit > 0) {
        //     $history_num = $member
        //         ->orderGoods()
        //         ->leftJoin('yz_order', 'yz_order.id', '=', 'order_id')
        //         ->where('goods_id', $this->goods_id)
        //         ->where('yz_order.status','!=' ,Order::CLOSE)
        //         ->sum('total');
        // }
    }

    /**
     * 用户等级限制
     * @param Member $member
     * @throws AppException
     */
    public function validateMemberLevelLimit(Member $member)
    {

        if (empty($this->buy_levels) && $this->buy_levels !== '0') {
            return;
        }

        $buy_levels = explode(',', $this->buy_levels);

        if ($this->buy_levels !== '0') {
            $level_names = MemberLevel::select(DB::raw('group_concat(level_name) as level_name'))->whereIn('id', $buy_levels)->value('level_name');
            if (empty($level_names)) {
                return;
            }
        }
        if (!in_array($member->yzMember->level_id, $buy_levels)) {
            $ordinaryMember = in_array('0', $buy_levels)? '普通会员 ':'';

            throw new AppException('商品(' . $this->goods->title . ')仅限' . $ordinaryMember.$level_names . '购买');
        }
    }

    /**
     * 用户组限购
     * @param Member $member
     * @throws AppException
     */
    public function validateMemberGroupLimit(Member $member)
    {
        if (empty($this->buy_groups)) {
            return;
        }
        $buy_groups = explode(',', $this->buy_groups);
        $group_names = MemberGroup::select(DB::raw('group_concat(group_name) as level_name'))->whereIn('id', $buy_groups)->value('level_name');
        if (empty($group_names)) {
            return;
        }
        if (!in_array($member->yzMember->group_id, $buy_groups)) {
            throw new AppException('(' . $this->goods->title . ')该商品仅限[' . $group_names . ']购买');
        }
    }

    /**
     * 用户等級限制浏览
     * @param $goodsModel
     * @param $member
     * @throws AppException
     */
    public static function validatePrivilegeLevel($goodsModel, $member)
    {
        if (empty($goodsModel->hasOnePrivilege->show_levels) && $goodsModel->hasOnePrivilege->show_levels !== '0') {
            return;
        }

        $show_levels = explode(',', $goodsModel->hasOnePrivilege->show_levels);
        if (!is_array($show_levels)) {
            $show_levels[0] = intval($show_levels);
        }

        $level_names = MemberLevel::select(DB::raw('group_concat(level_name) as level_name'))
            ->whereIn('id', $show_levels)
            ->pluck('level_name')
            ->toArray();

        $ordinary_name = '';
        if(Setting::get('shop.member')['level_name'] != ''){
            $level_name = Setting::get('shop.member')['level_name'];
            $member_name = $level_name;
        }else{
            $member_name = '普通会员';
        }
        if (count($show_levels) > 1 && in_array(0, $show_levels)) {
            $ordinary_name = $member_name;
        }

        if (count($show_levels) == 1 && in_array(0, $show_levels)) {
            $level_names = [
                0 => $member_name,
            ];
        }

        if (empty($level_names)) {
            return;
        }

        if (!in_array($member->level_id, $show_levels)) {
            throw new AppException('商品(' . $goodsModel->title . ')仅限' . $ordinary_name . implode(',', $level_names) . '浏览');
        }
    }

	/**
	 * 用户组限制浏览
	 * @param $goodsModel
	 * @param $member
	 * @throws AppException
	 */
	public static function validatePrivilegeGroup($goodsModel, $member)
	{
		if (empty($goodsModel->hasOnePrivilege->show_groups) && $goodsModel->hasOnePrivilege->show_groups !== '0') {
			return;
		}
		$show_groups = explode(',', $goodsModel->hasOnePrivilege->show_groups);
		if ($goodsModel->hasOnePrivilege->show_groups === '0') {
			$group_names = '无分组';
		} else {
			$group_names = MemberGroup::select(DB::raw('group_concat(group_name) as group_name'))->whereIn('id', $show_groups)->value('group_name');
			if (empty($group_names)) {
				return;
			}
		}

        if (!in_array($member->group_id, $show_groups)) {
            throw new AppException('(' . $goodsModel->title . ')该商品仅限[' . $group_names . ']浏览');
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
        if (intval($this->buy_multiple) > 0) {

            if ($num % $this->buy_multiple != 0) {
                throw new AppException('商品:(' . $this->goods->title . '),购买数量需为' . $this->buy_multiple . '的倍数');
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
        if ($this->buy_limit_status == 1) {
            $now_time = time();

            $time_limits = $this->time_limits;
            $existTime = false;//是否存在时间段内
            foreach ($time_limits as $limit_time_arr) {
                $start_time = $limit_time_arr['time_limit'][0] / 1000;
                $end_time = $limit_time_arr['time_limit'][1] / 1000;
                if ($now_time >= $start_time && $now_time < $end_time) {
                    $history_num = $member->orderGoods()
                        ->join('yz_order', 'yz_order_goods.order_id', '=', 'yz_order.id')
                        ->where('yz_order_goods.goods_id', $this->goods_id)
                        ->where('yz_order.status', '!=' ,Order::CLOSE)
                        ->whereBetween('yz_order_goods.created_at',[$start_time,$end_time])
                        ->sum('yz_order_goods.total');

                    $existTime = true;
                    if ($history_num + $num > $limit_time_arr['limit_number']) {
                        throw new AppException('商品(' . $this->goods->title . '),限购' . $limit_time_arr['limit_number'] . '件');
                    }
                }
            }

            if (!$existTime) {
                throw new AppException('商品(' . $this->goods->title . ')未到购买时间暂不能购买');
            }
        }

    }
}