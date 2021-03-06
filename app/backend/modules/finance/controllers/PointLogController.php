<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/11
 * Time: 上午11:44
 */

namespace app\backend\modules\finance\controllers;


use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\backend\modules\finance\models\PointLog as PoinLogModel;
use app\common\helpers\PaginationHelper;
use app\common\services\finance\PointService;
use Yunshop\Froze\Common\Services\SetService;
use app\Jobs\PointQueueJob;

class PointLogController extends BaseController
{
    public function index(\Illuminate\Http\Request $request)
    {
        $pageSize = 10;
        $search = $request->search;
        $builer = PoinLogModel::getPointLogList($search);
        if ($request->member_id) {
            $builer = $builer->where('member_id', $request->member_id);
        }

        //业务类型
        $activity_mode=[
            1=>PointService::POINT_MODE_GOODS_ATTACHED,
            2=>PointService::POINT_MODE_ORDER_ATTACHED,
            3=>PointService::POINT_MODE_POSTER_ATTACHED,
            4=>PointService::POINT_MODE_ARTICLE_ATTACHED,
            5=>PointService::POINT_MODE_ADMIN_ATTACHED,
            6=>PointService::POINT_MODE_BY_ATTACHED,
            7=>PointService::POINT_MODE_TEAM_ATTACHED,
            8=>PointService::POINT_MODE_LIVE_ATTACHED,
            9=>PointService::POINT_MODE_CASHIER_ATTACHED,
            13=>PointService::POINT_MODE_TRANSFER_ATTACHED,
            14=>PointService::POINT_MODE_RECIPIENT_ATTACHED,
            15=>PointService::POINT_MODE_ROLLBACK_ATTACHED,
            16=>PointService::POINT_MODE_COUPON_DEDUCTION_AWARD_ATTACHED,
            17=> PointService::POINT_MODE_TASK_REWARD_ATTACHED,
            18=>(app('plugins')->isEnabled('love'))?'转入'.\Yunshop\Love\Common\Services\SetService::getLoveName():PointService::POINT_MODE_TRANSFER_LOVE_ATTACHED,
            19=>(app('plugins')->isEnabled('sign'))?trans('Yunshop\Sign::sign.plugin_name') . '奖励': PointService::POINT_MODE_SIGN_REWARD_ATTACHED,
            20=> PointService::POINT_MODE_COURIER_REWARD_ATTACHED,
            21=>(app('plugins')->isEnabled('froze'))? SetService::getFrozeName(). '奖励': PointService::POINT_MODE_FROZE_AWARD_ATTACHED,
            23=> PointService::POINT_MODE_CREATE_ACTIVITY_ATTACHED,
            24=> PointService::POINT_MODE_ACTIVITY_OVERDUE_ATTACHED,
            25=> PointService::POINT_MODE_RECEIVE_ACTIVITY_ATTACHED,
            26=>PointService::POINT_MODE_RECEIVE_OVERDUE_ATTACHED,
            27=>PointService::POINT_MODE_COMMISSION_TRANSFER_ATTACHED,
            28=>PointService::POINT_MODE_HOTEL_CASHIER_ATTACHED,
            29=>PointService::POINT_MODE_EXCEL_RECHARGE_ATTACHED,
            92=> PointService::POINT_MODE_RECHARGE_CODE_ATTACHED,
            93=> PointService::POINT_MODE_STORE_ATTACHED,
            94=>PointService::POINT_MODE_HOTEL_ATTACHED,
            22=>PointService::POINT_MODE_COMMUNITY_REWARD_ATTACHED,
            30=> PointService::POINT_MODE_CARD_VISIT_REWARD_ATTACHED,
            31=>PointService::POINT_MODE_CARD_REGISTER_REWARD_ATTACHED,
            32=>PointService::POINT_MODE_PRESENTATION_ATTACHED,
            33=>(app('plugins')->isEnabled('love'))?\Yunshop\Love\Common\Services\SetService::getLoveName().'提现扣除' : PointService::POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION_ATTACHED,
            34=>PointService::POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED,
            35=>PointService::POINT_MODE_DRAW_CHARGE_GRT_ATTACHED,
            36=>PointService::POINT_MODE_DRAW_CHARGE_DEDUCTION_ATTACHED,
            37=>PointService::POINT_MODE_DRAW_REWARD_GRT_ATTACHED,
            38=>PointService::POINT_MODE_CONVERT_ATTACHED,
            40=>PointService::POINT_MODE_CONSUMPTION_POINTS_ATTACHED,
            41=>PointService::POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS_ATTACHED,
            42=>PointService::POINT_MODE_ROOM_ACTIVITY_POINTS_ATTACHED,
            43=>PointService::POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS_ATTACHED,
            44=>PointService::POINT_MODE_ROOM_REWARD_TRANSFER_POINTS_ATTACHED,
            45=>PointService::POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS_ATTACHED,
            46=>PointService::POINT_AUCTION_REWARD_RECIPIENT_POINTS_ATTACHED,
            47=>PointService::POINT_INCOME_WITHDRAW_AWARD_ATTACHED,
            48=> PointService::POINT_MODE_TRANSFER_BALANCE_ATTACHED,
            49=> PointService::POINT_MODE_BIND_MOBILE_ATTACHED,
            50=> PointService::POINT_MODE_LAYER_CHAIN_ATTACHED,
            51=> PointService::POINT_MODE_LAYER_CHAIN_RECHARGE_ATTACHED,
            52=> PointService::POINT_MODE_HEALTH_ASSESSMENT_ATTACHED,
            53=> PointService::POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE_ATTACHED,
            54=> PointService::POINT_MODE_HEALTH_ASSESSMENT_ATTACHED,
            55=> PointService::POINT_INCOME_WITHDRAW_AWARD_ATTACHED_SCALE,
            56=> PointService::POINT_MODE_MICRO_COMMUNITIES_REWARD,
            57=> PointService::POINT_MODE_CONFERENCE_REWARD,
            58=> PointService::POINT_MODE_STORE_SHAREHOLDER_ATTACHED,
            59=> PointService::POINT_MODE_ANSWER_REWARD_ATTACHED,
            60=> PointService::POINT_MODE_ANSWER_REWARD_PARENT_ATTACHED,
            61=> PointService::POINT_MODE_POINT_EXCHANGE_ATTACHED,
            62=> PointService::POINT_MODE_SNATCH_REGIMENT_ATTACHED,
            63=> PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN_ATTACHED,
            64=> PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER_ATTACHED,
            65=>PointService::POINT_MODE_COMMUNITY_RELAY_ATTACHED,
        ];

        $list = $builer->paginate($pageSize);
        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
        return view('finance.point.point_log', [
            'list'          => $list,
            'activityMode'=>$activity_mode,
            'pager'         => $pager,
            'memberGroup'   => MemberGroup::getMemberGroupList(),
            'memberLevel'   => MemberLevel::getMemberLevelList(),
            'search'        => $search
        ])->render();
    }

    public function test()
    {
        (new PointQueueJob(\YunShop::app()->uniacid))->handle();
        dd('执行成功');
        exit;
    }
}
