<?php
/**
 * Created by PhpStorm.
 * Author:
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

class  PointLogController extends BaseController
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
//        $activity_mode = [
//            1   => PointService::POINT_MODE_GOODS_ATTACHED,
//            2   => PointService::POINT_MODE_ORDER_ATTACHED,
//            3   => PointService::POINT_MODE_POSTER_ATTACHED,
//            4   => PointService::POINT_MODE_ARTICLE_ATTACHED,
//            5   => PointService::POINT_MODE_ADMIN_ATTACHED,
//            6   => PointService::POINT_MODE_BY_ATTACHED,
//            7   => PointService::POINT_MODE_TEAM_ATTACHED,
//            8   => PointService::POINT_MODE_LIVE_ATTACHED,
//            9   => PointService::POINT_MODE_CASHIER_ATTACHED,
//            13  => PointService::POINT_MODE_TRANSFER_ATTACHED,
//            14  => PointService::POINT_MODE_RECIPIENT_ATTACHED,
//            15  => PointService::POINT_MODE_ROLLBACK_ATTACHED,
//            16  => PointService::POINT_MODE_COUPON_DEDUCTION_AWARD_ATTACHED,
//            17  => PointService::POINT_MODE_TASK_REWARD_ATTACHED,
//            18  => (app('plugins')->isEnabled('love')) ? '转入' . \Yunshop\Love\Common\Services\SetService::getLoveName() : PointService::POINT_MODE_TRANSFER_LOVE_ATTACHED,
//            19  => (app('plugins')->isEnabled('sign')) ? trans('Yunshop\Sign::sign.plugin_name') . '奖励' : PointService::POINT_MODE_SIGN_REWARD_ATTACHED,
//            20  => PointService::POINT_MODE_COURIER_REWARD_ATTACHED,
//            21  => (app('plugins')->isEnabled('froze')) ? SetService::getFrozeName() . '奖励' : PointService::POINT_MODE_FROZE_AWARD_ATTACHED,
//            23  => PointService::POINT_MODE_CREATE_ACTIVITY_ATTACHED,
//            24  => PointService::POINT_MODE_ACTIVITY_OVERDUE_ATTACHED,
//            25  => PointService::POINT_MODE_RECEIVE_ACTIVITY_ATTACHED,
//            26  => PointService::POINT_MODE_RECEIVE_OVERDUE_ATTACHED,
//            27  => PointService::POINT_MODE_COMMISSION_TRANSFER_ATTACHED,
//            28  => PointService::POINT_MODE_HOTEL_CASHIER_ATTACHED,
//            29  => PointService::POINT_MODE_EXCEL_RECHARGE_ATTACHED,
//            92  => PointService::POINT_MODE_RECHARGE_CODE_ATTACHED,
//            93  => PointService::POINT_MODE_STORE_ATTACHED,
//            94  => PointService::POINT_MODE_HOTEL_ATTACHED,
//            22  => PointService::POINT_MODE_COMMUNITY_REWARD_ATTACHED,
//            30  => PointService::POINT_MODE_CARD_VISIT_REWARD_ATTACHED,
//            31  => PointService::POINT_MODE_CARD_REGISTER_REWARD_ATTACHED,
//            32  => PointService::POINT_MODE_PRESENTATION_ATTACHED,
//            33  => (app('plugins')->isEnabled('love')) ? \Yunshop\Love\Common\Services\SetService::getLoveName() . '提现扣除' : PointService::POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION_ATTACHED,
//            34  => PointService::POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED,
//            35  => PointService::POINT_MODE_DRAW_CHARGE_GRT_ATTACHED,
//            36  => PointService::POINT_MODE_DRAW_CHARGE_DEDUCTION_ATTACHED,
//            37  => PointService::POINT_MODE_DRAW_REWARD_GRT_ATTACHED,
//            38  => PointService::POINT_MODE_CONVERT_ATTACHED,
//            40  => PointService::POINT_MODE_CONSUMPTION_POINTS_ATTACHED,
//            41  => PointService::POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS_ATTACHED,
//            42  => PointService::POINT_MODE_ROOM_ACTIVITY_POINTS_ATTACHED,
//            43  => PointService::POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS_ATTACHED,
//            44  => PointService::POINT_MODE_ROOM_REWARD_TRANSFER_POINTS_ATTACHED,
//            45  => PointService::POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS_ATTACHED,
//            46  => PointService::POINT_AUCTION_REWARD_RECIPIENT_POINTS_ATTACHED,
//            47  => PointService::POINT_INCOME_WITHDRAW_AWARD_ATTACHED,
//            48  => PointService::POINT_MODE_TRANSFER_BALANCE_ATTACHED,
//            49  => PointService::POINT_MODE_BIND_MOBILE_ATTACHED,
//            50  => PointService::POINT_MODE_LAYER_CHAIN_ATTACHED,
//            51  => PointService::POINT_MODE_LAYER_CHAIN_RECHARGE_ATTACHED,
//            52  => PointService::POINT_MODE_HEALTH_ASSESSMENT_ATTACHED,
//            53  => PointService::POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE_ATTACHED,
//            54  => PointService::POINT_MODE_HEALTH_ASSESSMENT_ATTACHED,
//            55  => PointService::POINT_INCOME_WITHDRAW_AWARD_ATTACHED_SCALE,
//            56  => PointService::POINT_MODE_MICRO_COMMUNITIES_REWARD,
//            57  => PointService::POINT_MODE_CONFERENCE_REWARD,
//            58  => PointService::POINT_MODE_STORE_SHAREHOLDER_ATTACHED,
//            59  => PointService::POINT_MODE_ANSWER_REWARD_ATTACHED,
//            60  => PointService::POINT_MODE_ANSWER_REWARD_PARENT_ATTACHED,
//            61  => PointService::POINT_MODE_POINT_EXCHANGE_ATTACHED,
//            62  => PointService::POINT_MODE_SNATCH_REGIMENT_ATTACHED,
//            63  => PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN_ATTACHED,
//            64  => PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER_ATTACHED,
//            65  => PointService::POINT_MODE_COMMUNITY_RELAY_ATTACHED,
//            66  => PointService::POINT_MODE_REGISTRATION_REWARDS_PARENT_ATTACHED,
//            67  => PointService::POINT_MODE_REGISTRATION_AWARD_ATTACHED,
//            68  => PointService::POINT_MODE_OPEN_GROUP_DEDUCTION_ATTACHED,
//            69  => PointService::POINT_MODE_EXCHANGE_REDPACK_CHALLENGE_ATTACHED,
//            71  => PointService::POINT_MODE_STAR_SPELL_ATTACHED,
//            72  => PointService::POINT_MODE_STAR_SPELL_LOST_ATTACHED,
//            73  => PointService::TEAM_POINTS_REWARD_ATTACHED,
//            74  => PointService::POINT_MODE_LOCK_DRAW_ATTACHED,
//            75  => PointService::POINT_MODE_BLIND_BOX_LOST_ATTACHED,
//            76  => PointService::POINT_MODE_CIRCLE_ADD_ATTACHED,
//            78  => PointService::POINT_MODE_CONSUMER_REWARD_ATTACHED,
//            77  => PointService::POINT_MODE_LINK_SERVICE_ATTACHED,
//            79  => PointService::POINT_MODE_STORE_RESERVE_ATTACHED,
//            80  => PointService::POINT_MODE_ZHUZHER_CREDIT_LOST_ATTACHED,
//            81  => PointService::POINT_MODE_DEPOSIT_LADDER_REWARD,
//            82  => PointService::POINT_MODE_FIGHT_GROUP_LOTTERY_COMFORT_ATTACHED,
//            83  => PointService::POINT_MODE_LOVE_REDPACK_ATTACHED,
//            84  => PointService::POINT_MODE_ZHP_LOST_ATTACHED,
//            85  => PointService::POINT_MODE_TEAM_DIVIDEND_ATTACHED,
//            86  => PointService::CPS_SUB_PLATFORM_ATTACHED,
//            88  => PointService::POINT_MODE_COUPON_STORE_REWARD_ATTACHED,
//            90  => PointService::POINT_MODE_NEW_MEDIA_LIKE_ATTACHED,
//            91  => PointService::POINT_MODE_NEW_MEDIA_ATTENTION_ATTACHED,
//            95  => PointService::POINT_MODE_NEW_MEDIA_COMMENT_ATTACHED,
//            96  => PointService::POINT_MODE_NEW_MEDIA_REWARD_ATTACHED,
//            97  => PointService::POINT_MODE_NEW_MEDIA_SUPERIOR_ATTACHED,
//            98  => PointService::POINT_MODE_NEW_MEDIA_EXCHANGE_ATTACHED,
//            99  => PointService::POINT_MODE_NEW_MEDIA_READ_ATTACHED,
//            100 => PointService::POINT_MODE_NEW_MEDIA_FORWARD_ATTACHED,
//            101 => PointService::POINT_MODE_NEW_MEDIA_FAVORITES_ATTACHED,
//            102 => PointService::GROUP_WORK_AWARD_ATTACHED,
//            103 => PointService::GROUP_WORK_HEAD_AWARD_ATTACHED,
//            104 => PointService::GROUP_WORK_PARENT_AWARD_ATTACHED,
//            105 => PointService::POINT_MODE_VIDEO_WATCH_REWARD_ATTACHED,
//            106 => PointService::POINT_MODE_VIDEO_TEAM_REWARD_ATTACHED,
//            107 => PointService::POINT_MODE_FLYERS_ADVERTISE_ATTACHED,
//            108 => PointService::POINT_MODE_POINT_MIDDLE_SYNC_ATTACHED,
//            112 => PointService::POINT_MODE_QQ_ADVERTISE_POINT_ATTACHED,
//            111 => PointService::POINT_MODE_LOVE_TRANSFER_ATTACHED,
//            115 => PointService::POINT_MODE_GROUP_CHAT_ACTIVITY_REWARD_ATTACHED,
//            117 => PointService::POINT_MODE_CUSTOMER_INCREASE_REWARD_ATTACHED,
//            118 => PointService::INTEGRAL_POINT_ATTACHED,
//            119 => PointService::YS_SYSTEM_POINT_SYNC_NAME,
//            120 => PointService::POINT_MODE_VIDEO_WATCH_TAKE_ATTACHED,
//            121 => PointService::POINT_MODE_PARKING_PAY_COUPON_ATTACHED,
//            122 => PointService::POINT_MODE_LOVE_WITHDRAW_FINAL_REDUCE_ATTACHED,
//            123 => PointService::POINT_MODE_STORE_BALANCE_RECHARGE_ATTACHED,
//            124 => PointService::POINT_MODE_YWM_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED,
//            125 => PointService::POINT_MODE_LOVE_BUY_DEDUCTE_REDUCE_ATTACHED,
//            126 => PointService::POINT_MODE_SUBSCRIPTION_ATTACHED,
//            127 => PointService::POINT_MODE_ROOM_RED_PACK_RECEIVE_ATTACHED,
//            128 => PointService::POINT_MODE_ROOM_RED_PACK_REFUND_ATTACHED,
//            129 => PointService::POINT_MODE_ROOM_RED_PACK_SEND_ATTACHED,
//            130  => (app('plugins')->isEnabled('love')) ? '冻结'.\Yunshop\Love\Common\Services\SetService::getLoveName().'激活'  : PointService::POINT_MODE_LOVE_FROZE_ACTIVE_ATTACHED,
//            131 => PointService::POINT_MODE_NEWCOMER_FISSION_ACTIVE_ATTACHED,
//            132 => PointService::POINT_MODE_TRANSFER_INTEGRAL_ATTACHED,
//            133 => PointService::POINT_MODE_BLB_CASHIER_ATTACHED,
//            134 => PointService::FACE_TO_FACE_BUY_ATTACHED,
//            135 => PointService::FACE_TO_FACE_MEMBER_GIFT_ATTACHED,
//            136 => PointService::FACE_TO_FACE_MERCHANT_GIFT_ATTACHED,
//            139 => PointService::POINT_EXCHANGE_OUT_ATTACHED,
//            140 => PointService::POINT_EXCHANGE_IN_ATTACHED,
//            141 => PointService::POINT_MODE_FIRST_PARENT_REWARD_ATTACHED,
//            142 => PointService::POINT_MODE_SECOND_PARENT_REWARD_ATTACHED,
//            143 => PointService::POINT_MODE_FIRST_PARENT_REFUND_ATTACHED,
//            144 => PointService::POINT_MODE_SECOND_PARENT_REFUND_ATTACHED,
//            145 => PointService::POINT_MODE_POINT_EXCHANGE_LOVE_ATTACHED,
//            146 => PointService::POINT_MODE_POOL_RESET_ATTACHED,
//            147 => PointService::ACTIVITY_REWARD_INTEGRAL_ATTACHED,
//            148 => PointService::POINT_MODE_AREA_DIVIDEND_ATTACHED,
//            149 => PointService::POINT_MODE_LOVE_SPEED_POOL_CLEAR_ATTACHED,
//            153 => PointService::POINT_MODE_AREA_DIVIDEND_AWARD_ATTACHED,
//            154 => PointService::POINT_MODE_AREA_MERCHANT_AWARD_ATTACHED,
//            155 => PointService::POINT_MODE_FACE_TO_FACE_AWARD_ATTACHED,
//        ];

        $activity_mode = PointService::getAllSourceName();
        $list = $builer->paginate($pageSize);
        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
        return view('finance.point.point_log', [
            'list'         => $list,
            'activityMode' => $activity_mode,
            'pager'        => $pager,
            'memberGroup'  => MemberGroup::getMemberGroupList(),
            'memberLevel'  => MemberLevel::getMemberLevelList(),
            'search'       => $search,
        ])->render();
    }

    public function test()
    {
        (new PointQueueJob(\YunShop::app()->uniacid))->handle();
        dd('执行成功');
        exit;
    }
}
