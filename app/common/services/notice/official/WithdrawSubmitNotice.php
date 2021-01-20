<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 14:40
 */

namespace app\common\services\notice\official;

use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class WithdrawSubmitNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate,OrderNoticeData;

    public $withdrawModel;


    public function __construct($withdrawModel)
    {
        $this->withdrawModel = $withdrawModel;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.\
        $this->data = [
            ['name' => '时间', 'value' => $this->withdrawModel->created_at->toDateTimeString()],
            ['name' => '金额', 'value' => $this->withdrawModel->amounts],
            ['name' => '手续费', 'value' => $this->withdrawModel->actual_poundage],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate('withdraw_submit');
        $this->organizeData();

        \Log::debug("新版公众号消息-提现允许1",$this->template_id);
        \Log::debug("新版公众号消息-提现允许2",$this->withdrawModel->hasOneMember->hasOneFans->openid);
        \Log::debug("新版公众号消息-提现允许3",$this->data);

        $result = (new OfficialMessageNotice($this->temp_id,$this->withdrawModel->hasOneMember->hasOneFans->openid,$this->data,[],1,$this->url))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}