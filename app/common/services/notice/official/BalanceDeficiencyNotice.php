<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 11:08
 */

namespace app\common\services\notice\official;


use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\OfficialNoticeTemplate;

class BalanceDeficiencyNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate;

    public $member;
    public $balance;
    public $new;

    public function __construct($member,$balance,$new)
    {
        $this->member = $member;
        $this->balance = $balance;
        $this->new = $new;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '昵称', 'value' => $this->member->nickname],
            ['name' => '时间', 'value' => date('Y-m-d H:i', time())],
            ['name' => '通知额度', 'value' => $this->balance['blance_floor']],
            ['name' => '当前余额', 'value' => $this->new]
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.

        $this->getTemplate('balance_deficiency');

        $this->organizeData();
        \Log::debug("新版公众号消息-余额不足1",$this->template_id);
        \Log::debug("新版公众号消息-余额不足2",$this->member->hasOneFans->openid);
        \Log::debug("新版公众号消息-余额不足3",$this->data);
        $result = (new OfficialMessageNotice($this->temp_id,$this->member->hasOneFans->openid,$this->data,[],1,$this->url))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }

    }
}