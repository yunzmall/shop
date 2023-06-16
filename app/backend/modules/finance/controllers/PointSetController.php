<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/10
 * Time: 下午2:00
 */

namespace app\backend\modules\finance\controllers;


use app\backend\modules\finance\services\PointService;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\models\MemberGroup;
use app\common\services\point\PointToBalanceService;
use Carbon\Carbon;
use app\common\helpers\Url;
use Yunshop\Love\Common\Services\CommonService;

class PointSetController extends BaseController
{
//    public function test1()
//    {
//        (new PointToBalanceService())->transferStart();
//
//        dd('手动转入成功');
//    }
//    public function test2()
//    {
//        Setting::set('point.transfer_balance', [
//            'last_month' => date('m') -1,
//            'last_week'  => date('W') -1,
//            'last_day'   => date('d') -1
//        ]);
//
//        dd('重置转入时间成功');
//    }
    /**
     * @name 积分基础设置
     * @author yangyang
     */
    public function index()
    {
        if (request()->ajax()) {
            $point_data = \YunShop::request()->set;
            if ($point_data) {
                $point_data = $this->verifySetData($point_data);
                $result = (new PointService())->verifyPointData($point_data);
                if ($result) {
                    (new \app\common\services\operation\PointSetLog(['old' => $this->pointSet(), 'new' => $point_data], 'update'));
                    return $this->successJson('保存成功', $this->resultData());
                }
            }
            return $this->successJson('ok', $this->resultData());
        }
        return view('finance.point.set');
    }

    private function resultData()
    {
        return [
            'tab_list'     => PointService::getVueTags(),
            'set'          => $this->pointSet(),
            'week_data'    => $this->getWeekData(),
            'memberLevels' => $this->memberLevels(),
            'memberGroups' => $this->memberGroups(),
            'love_name'    => app('plugins')->isEnabled('love') && CommonService::getLoveName() ? CommonService::getLoveName() : '爱心值',
        ];
    }

    /**
     * 转换类型
     *
     * @param array $point_data
     * @return mixed array
     * @author yangyang
     */
    private function verifySetData($point_data)
    {
        $point_data['money'] = floatval($point_data['money']);
        $point_data['money_max'] = floatval($point_data['money_max']);
        $point_data['give_point'] = trim($point_data['give_point']);
        $point_data['first_parent_point'] = trim($point_data['first_parent_point']);
        $point_data['second_parent_point'] = trim($point_data['second_parent_point']);
        $point_data['enough_money'] = floatval($point_data['enough_money']);
        $point_data['enough_point'] = floatval($point_data['enough_point']);
        return $point_data;
    }

    //爱心值插件名称
    private function loveName()
    {
        $loveName = Setting::get('love.name');

        return $loveName ? $loveName : '爱心值';
    }

    //爱心值插件名称
    private function integralName()
    {
        if (app('plugins')->isEnabled('integral')) {
            return \Yunshop\Integral\Common\Services\SetService::getIntegralName();
        }
        return '消费积分';
    }

    //会员等级列表
    private function memberLevels()
    {
        return MemberLevel::getMemberLevelList();
    }

    //会员分组列表
    private function memberGroups()
    {
        return MemberGroup::records()->get();
    }

    private function pointSet()
    {
        $set = $this->SetData(Setting::get('point.set'));

        $set['love_name'] = $this->loveName();
        $set['integral_name'] = $this->integralName();

        return $set;
    }

    private function getWeekData()
    {
        return [
            Carbon::SUNDAY    => '星期日',
            Carbon::MONDAY    => '星期一',
            Carbon::TUESDAY   => '星期二',
            Carbon::WEDNESDAY => '星期三',
            Carbon::THURSDAY  => '星期四',
            Carbon::FRIDAY    => '星期五',
            Carbon::SATURDAY  => '星期六',
        ];
    }

    /**
     * 返回一天24时，对应key +1, 例：1 => 0:00
     * @return array
     */
    private function getDayData()
    {
        $dayData = [];
        for ($i = 0; $i <= 23; $i++) {
            $dayData += [
                $i + 1 => "当天" . $i . ":00 转入",
            ];
        }
        return $dayData;
    }

    /**
     * 默认设置参数
     * @return array
     */
    private function SetData($set)
    {
        return [
            "point_transfer" => $set['point_transfer'] ?: '0',
            "point_transfer_poundage" => $set['point_transfer_poundage'] ?: '0',
            "show_transferor" => $set['show_transferor'] ?: '0',
            "point_deduct" => $set['point_deduct'] ?: '0',
            "default_deduction" => $set['default_deduction'] ?: '0',
            "point_rollback" => $set['point_rollback'] ?: '0',
            "point_refund" => $set['point_refund'] ?: '0',
            "point_freight" => $set['point_freight'] ?: '0',
            "point_deduction_integer" => $set['point_deduction_integer'] ?: '0',
            "goods_page_deduct_show" => $set['goods_page_deduct_show'] ?: '0',
            "money" => $set['money'] ?: 0,
            "point" => $set['point'] ?: '0',
            "money_max" => $set['money_max'] ?: 0,
            "money_min" => $set['money_min'] ?: '0',
            "deduction_amount_type" => $set['deduction_amount_type'] ?: '0',
            "transfer_love" => $set['transfer_love'] ?: '0',
            "exchange_to_love_by_member" => $set['exchange_to_love_by_member'] ?: '0',
            "transfer_cycle" => $set['transfer_cycle'] ?: '0',
            "transfer_time_week" => $set['transfer_time_week'] ?: '',
            "transfer_time_hour" => $set['transfer_time_hour'] ?: '',
            "transfer_compute_mode" => $set['transfer_compute_mode'] ?: '0',
            "transfer_love_rate" => $set['transfer_love_rate'] ?: '0',
            "transfer_integral" => $set['transfer_integral'] ?: '0',
            "transfer_integral_love" => $set['transfer_integral_love'] ?: '0',
            "is_transfer_integral" => $set['is_transfer_integral'] ?: '0',
            "transfer_point_ratio" => $set['transfer_point_ratio'] ?: '0',
            "transfer_integral_ratio" => $set['transfer_integral_ratio'] ?: '0',
            "goods_point" => [
                "search_page" => $set['goods_point']['search_page'] ? "on" : '',
                "goods_page" => $set['goods_point']['goods_page'] ? "on" : '',
                "single_page" => $set['goods_point']['single_page'] ? "on" : '',
                'order_page' => $set['goods_point']['order_page'] ? "on" : '',
            ],
            "data_display_type" => $set['data_display_type'] ?: '0',
            "give_type" => $set['give_type'] ?: '0',
            "give_point" => $set['give_point'] ?: '0',
            "first_parent_point" => $set['first_parent_point'] ?: '0',
            "second_parent_point" => $set['second_parent_point'] ?: '0',
            "balance_pay_reward" => $set['balance_pay_reward'] ?: '0',
            "point_award_type" => $set['point_award_type'] ?: '0',
            "enough_money" => $set['enough_money'] ?: 0,
            "enough_point" => $set['enough_point'] ?: 0,
            "bind_mobile_award" => $set['bind_mobile_award'] ?: '0',
            "bind_mobile_award_point" => $set['bind_mobile_award_point'] ?: '0',
            "income_withdraw_award" => $set['income_withdraw_award'] ?: '0',
            "income_withdraw_award_scale" => $set['income_withdraw_award_scale'] ?: '0',
            "income_withdraw_award_scale_point" => $set['income_withdraw_award_scale_point'] ?: '0',
            "point_floor_on" => $set['point_floor_on'] ?: '0',
            "point_floor" => $set['point_floor'] ?: '0',
            "point_message_type" => $set['point_message_type'] ?: '0',
            "level_limit" => $set['level_limit'] ?: '',
            "group_type" => $set['group_type'] ?: '',
            'enoughs' =>$set['enoughs'] ?: [],
            "uids" => $set['uids'] ?: '',
        ];
    }
}
