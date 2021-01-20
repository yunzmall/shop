<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/22
 * Time: 14:57
 */

namespace app\common\services\notice\applet\room;


use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;

class PromoteOrderPayedMinNotice extends BaseMessageBody
{

    use MiniNoticeTemplate;

    public $order;
    public $buyerMember;
    public $dividend;
    public $anchorMember;

    public function __construct($order,$buyermember,$anchorMember,$dividend)
    {
        $this->order = $order;
        $this->buyerMember = $buyermember;
        $this->anchorMember = $anchorMember;
        $this->dividend = $dividend;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            "time3" => ['value'=>date('Y-m-d H:i:s', time())],
            "thing1" => ['value'=>$this->checkDataLength($this->buyerMember->nickname,20)],
            "character_string5" => ['value'=>$this->checkDataLength($this->order->order_sn,32)],
            "amount2" => ['value'=>$this->order->price],
            "amount4" => ['value'=>$this->dividend]
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate("粉丝下单通知");

        if (empty($this->temp_open)) {
            \Log::debug($this->temp_title."消息通知未开启");
            return ;
        }

        $this->organizeData();
        \Log::debug("新版小程序消息-粉丝下单1",$this->temp_id);
        \Log::debug("新版小程序消息-粉丝下单2",$this->anchorMember->hasOneMiniApp->openid);
        \Log::debug("新版小程序消息-粉丝下单3",$this->data);
        $result =  (new AppletMessageNotice($this->temp_id,$this->anchorMember->hasOneMiniApp->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}