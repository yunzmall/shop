<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/21
 * Time: 18:00
 */

namespace app\common\services\notice\applet\supplier;


use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\MiniNoticeTemplate;

class SupplierWithDrawAuditNotice extends BaseMessageBody
{

    use MiniNoticeTemplate;

    public $withdraw;

    public function __construct($withdraw)
    {
        $this->withdraw = $withdraw;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'time1'=>['value'=> $this->withdraw->created_at->toDateTimeString()],// 提现时间
            'amount3'=>['value'=> $this->withdraw->money],//提现金额
            'thing2'=>['value'=>  $this->checkDataLength($this->withdraw->type_name,20)],// 提现方式
            'phrase4'=>['value'=> $this->checkDataLength($this->withdraw->status_obj['name'],5)],// 提现状态
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

        \Log::debug("新版小程序消息-供应商提现1",$this->temp_id);
        \Log::debug("新版小程序消息-供应商提现2",$this->withdraw->hasOneSupplier->hasOneMember->hasOneMiniApp->openid);
        \Log::debug("新版小程序消息-供应商提现3",$this->data);

        $result =  (new AppletMessageNotice($this->temp_id,$this->withdraw->hasOneSupplier->hasOneMember->hasOneMiniApp->openid,$this->data,[],2))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}