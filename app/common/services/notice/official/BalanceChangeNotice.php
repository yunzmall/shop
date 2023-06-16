<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 10:37
 */

namespace app\common\services\notice\official;


use app\common\services\notice\BaseMessageBody;
use app\common\services\credit\ConstService;
use app\common\services\notice\share\OfficialNoticeTemplate;

class BalanceChangeNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate;

    public $member;
    public $new;
    public $change;
    public $type;

    public function __construct($member,$new,$change,$type)
    {
        $this->member = $member;
        $this->new = $new;
        $this->change = $change;
        $this->type = $type;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '昵称', 'value' => $this->member->nickname],
            ['name' => '时间', 'value' => date('Y-m-d H:i', time())],
            ['name' => '余额变动金额', 'value' => $this->change],
            ['name' => '余额变动类型', 'value' => $this->getType()],
            ['name' => '变动后余额数值', 'value' => $this->new]
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.

        $this->getTemplate('balance_change');

        $this->organizeData();
        \Log::debug("新版公众号消息-余额1",$this->template_id);
        \Log::debug("新版公众号消息-余额2",$this->member->hasOneFans->openid);
        \Log::debug("新版公众号消息-余额3",$this->data);
        $this->url = $this->url ? : yzAppFullUrl('/member/detailed');
        $result = (new OfficialMessageNotice($this->temp_id,$this->member->hasOneFans->openid,$this->data,[],1,$this->url))->sendMessage();

        if (app('plugins')->isEnabled('instation-message')) {
            //开启了站内消息插件
            event(new \Yunshop\InstationMessage\event\BalanceChangeEvent([
                'changeTime'=>date('Y-m-d H:i:s', time()),
                'changeType'=>$this->getType(),
                'changeNum'=>$this->change,
                'afterChange'=>$this->new,
                'member_id'=>$this->member->uid,
                'uniacid'=>\YunShop::app()->uniacid
            ]));
        }

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }

    }

    private function getType()
    {
        return (new ConstService(''))->sourceComment()[$this->type];
    }
}
