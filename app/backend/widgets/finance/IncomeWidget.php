<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/6
 * Time: 上午11:32
 */

namespace app\backend\widgets\finance;

use app\backend\modules\withdraw\models\WithdrawRichText;
use app\common\components\Widget;
use app\common\facades\Setting;

class IncomeWidget extends Widget
{

    public function run()
    {
        $set = Setting::get('withdraw.income');
        $withdraw_rich_text = WithdrawRichText::uniacid()->first();
        $set['servicetax'] = array_values($set['servicetax']);

        return view('finance.withdraw.withdraw-income', [
            'set' => $set,
            'income_count' => count($set['servicetax']),
            'withdraw_rich_text' => $withdraw_rich_text,
        ])->render();
    }
}

