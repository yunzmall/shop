<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/21
 * Time: 18:45
 */

namespace app\common\services\notice\applet\withdraw;


use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\WithDrawData;

class WithDrawArrivalNotice extends BaseMessageBody
{

    use MiniNoticeTemplate,WithDrawData;

    public $withdraw;

    public function __construct($withdraw)
    {
        $this->withdraw = $withdraw;
        $this->getWithdrawModel($withdraw);
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'name1'=>['value'=> $this->nickname()],// 提现时间
            'amount2'=>['value'=> $this->withdraw->amounts],//提现金额
            'date3'=>['value'=>  date("Y-m-d H:i:s",time())],// 提现方式
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.

        $this->getTemplate("提现成功通知");
        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }

        $this->organizeData();
        $this->getMember();
        \Log::debug("新版小程序消息-提现到账1",$this->temp_id);
        \Log::debug("新版小程序消息-提现到账2",$this->openid);
        \Log::debug("新版小程序消息-提现到账3",$this->data);
        $result =  (new AppletMessageNotice($this->temp_id,$this->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}