<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/21
 * Time: 18:00
 */

namespace app\common\services\notice\applet\withdraw;


use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\WithDrawData;

class WithDrawAuditNotice extends BaseMessageBody
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
            'time1'=>['value'=> $this->withdraw->created_at->toDateTimeString()],// 提现时间
            'amount3'=>['value'=> $this->withdraw->amounts],//提现金额
            'thing2'=>['value'=>  $this->checkDataLength($this->payWayName(),20)],// 提现方式
            'phrase4'=>['value'=> $this->checkDataLength($this->getStatusComment($this->withdraw->status),5)],// 提现状态
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate("提现状态通知");
        $back = [];

        if (empty($this->temp_open)) {
            $back['message'] = $this->temp_title."消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }

        $this->organizeData();
        $this->getMember();
        \Log::debug("新版小程序消息-提现状态1",$this->temp_id);
        \Log::debug("新版小程序消息-提现状态2",$this->openid);
        \Log::debug("新版小程序消息-提现状态3",$this->data);
        $result =  (new AppletMessageNotice($this->temp_id,$this->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}