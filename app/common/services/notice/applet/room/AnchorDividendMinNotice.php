<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/22
 * Time: 15:24
 */

namespace app\common\services\notice\applet\room;


use app\common\models\Member;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class AnchorDividendMinNotice extends BaseMessageBody
{
    use OrderNoticeData,MiniNoticeTemplate;

    public $orderModel;
    public $money;
    public $memberModel;

    public function __construct($order,$money,$member_id)
    {
        $this->orderModel = $order;
        $this->money = $money;
        $this->memberModel = Member::uniacid()->where("uid",$member_id)->first();
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            "time5" => ['value'=>date('Y-m-d H:i:s', time())],
            "thing3" => ['value'=>$this->checkDataLength($this->member->nickname,20)],
            "thing4" => ['value'=>$this->checkDataLength($this->goodsTitle,20)],
            "thing1" => ['value'=>"订单分红"],
            "amount2" => ['value'=>$this->money]
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->processData($this->orderModel);
        $this->getTemplate("佣金到账提醒");

        if (empty($this->temp_open)) {
            $back['message'] = "消息通知未开启";
            \Log::debug($back['message']);
            return ;
        }

        $this->organizeData();

        \Log::debug("新版小程序消息-佣金到账1",$this->temp_id);
        \Log::debug("新版小程序消息-佣金到账2",$this->memberModel->hasOneMiniApp->openid);
        \Log::debug("新版小程序消息-佣金到账3",$this->data);
        $result =  (new AppletMessageNotice($this->temp_id,$this->memberModel->hasOneMiniApp->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}