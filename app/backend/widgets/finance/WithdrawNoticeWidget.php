<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/3/6
 * Time: 上午11:32
 */

namespace app\backend\widgets\finance;

use app\common\components\Widget;
use app\common\facades\Setting;
use app\common\models\notice\MessageTemp;

class WithdrawNoticeWidget extends Widget
{

    public function run()
    {
        $set = Setting::get('withdraw.notice');

        $temp_list = MessageTemp::getList();
        return view('finance.withdraw.withdraw-notice', [
            'set' => $set,
            'temp_list' => $temp_list,
        ])->render();
    }
}

