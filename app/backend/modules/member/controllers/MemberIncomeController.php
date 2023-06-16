<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/3/9
 * Time: 15:07
 */

namespace app\backend\modules\member\controllers;

use app\backend\modules\member\models\Member;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\member\services\MemberServices;
use app\common\components\BaseController;
use Yunshop\Commission\models\Agents;
use app\common\models\Income;

/**
 * 收入
 */
class MemberIncomeController extends BaseController
{
    /**
     * 加載模板
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        return view('member.income', [])->render();
    }
    /**
     * @return string
     * @throws \Throwable
     */
    public function show()
    {
//        $groups = MemberGroup::getMemberGroupList();
//        $levels = MemberLevel::getMemberLevelList();
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;
        if ($uid == 0 || !is_int($uid)) {
            $this->error('参数错误');
        }

        $member = Member::select(['uid', 'mobile', 'realname', 'avatar'])->uniacid()->where('uid', $uid)->first();
        $incomeModel = Income::getIncomes()->where('member_id', $uid)->get();
        $config = \app\backend\modules\income\Income::current()->getItems();
        unset($config['balance']);
        $incomeAll = [
            'income' => sprintf("%.2f",$incomeModel->sum('amount')),
            'withdraw' => sprintf("%.2f", $incomeModel->where('status', 1)->sum('amount')),
            'no_withdraw' => sprintf("%.2f", $incomeModel->where('status', 0)->sum('amount'))
        ];

        foreach ($config as $key => $item) {
            $typeModel = $incomeModel->where('incometable_type', $item['class']);
            $incomeData[$key] = [
                'type_name' => $item['title'],
                'income' => sprintf("%.2f", $typeModel->sum('amount')),
                'withdraw' => sprintf("%.2f", $typeModel->where('status', 1)->sum('amount')),
                'no_withdraw' => sprintf("%.2f", $typeModel->where('status', 0)->sum('amount'))
            ];
            if ($item['agent_class']) {
                $agentModel = $item['agent_class']::{$item['agent_name']}(\YunShop::app()->getMemberId());
                if ($item['agent_status']) {
                    $agentModel = $agentModel->where('status', 1);
                }
                //推广中心显示
                if (!$agentModel) {
                    $incomeData[$key]['can'] = false;
                } else {
                    $agent = $agentModel->first();
                    if ($agent) {
                        $incomeData[$key]['can'] = true;
                    } else {
                        $incomeData[$key]['can'] = false;
                    }
                }
            } else {
                $incomeData[$key]['can'] = true;
            }

        }
        return $this->successJson('ok', [
            'member' => $member,
            'incomeAll' => $incomeAll,
            'item' => $incomeData
        ]);
    }
}