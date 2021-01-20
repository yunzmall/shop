<?php

/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/22
 * Time: 14:38
 */
namespace app\common\services\notice\applet\room;

use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;

class AuditFinding extends BaseMessageBody
{

    use MiniNoticeTemplate;

    public $member;

    public function __construct($member)
    {
        $this->member = $member;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            "thing2" => ['value'=>'申请通过'],
            "date1" => ['value'=>date("Y-m-d H:i:s",time())],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate("审核结果通知");

        if (empty($this->temp_open)) {
            $back['message'] = "消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }

        $this->organizeData();

        \Log::debug("新版小程序消息-直播审核1",$this->temp_id);
        \Log::debug("新版小程序消息-直播审核2",$this->member->hasOneMiniApp->openid);
        \Log::debug("新版小程序消息-直播审核3",$this->data);

        $result =  (new AppletMessageNotice($this->temp_id,$this->member->hasOneMiniApp->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}