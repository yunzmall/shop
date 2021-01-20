<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 14:34
 */

namespace app\common\services\notice\official;


use app\common\models\Member;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class MemberUpgradeNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate;

    public $memberModel;
    public $old_level;
    public $new_level;

    public function __construct($member,$new)
    {
        $this->memberModel = $member;
        $this->new_level = $new;
        $set = \Setting::get('shop.member');
        $old_level = $set['level_name'] ?: '普通会员';
        $this->old_level = $this->memberModel->level->level_name ?: $old_level;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '粉丝昵称', 'value' => $this->memberModel->hasOneMember->hasOneFans->nickname],
            ['name' => '旧等级', 'value' => $this->old_level],
            ['name' => '新等级', 'value' => $this->new_level->level_name],
            ['name' => '时间', 'value' => date('Y-m-d H:i',time())],
            ['name' => '有效期', 'value' => $this->memberModel->validity.'天'],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate('customer_upgrade');
        $this->organizeData();

        \Log::debug("新版公众号消息-升级1",$this->template_id);
        \Log::debug("新版公众号消息-升级2",$this->memberModel->hasOneMember->hasOneFans->openid);
        \Log::debug("新版公众号消息-升级3",$this->data);

        $result = (new OfficialMessageNotice($this->temp_id,$this->memberModel->hasOneMember->hasOneFans->openid,$this->data,[],1,$this->url))->sendMessage();

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}