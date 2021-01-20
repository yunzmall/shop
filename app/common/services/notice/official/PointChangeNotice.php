<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 10:09
 */

namespace app\common\services\notice\official;


use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\OfficialNoticeTemplate;

class PointChangeNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate;

    public $member;
    public $point;
    public $status;

    public function __construct($member,$point,$status)
    {
       $this->member = $member;
       $this->point = $point;
       $this->status = $status;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.

        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '昵称', 'value' => $this->member->nickname],
            ['name' => '时间', 'value' => date('Y-m-d H:i', time())],
            ['name' => '积分变动金额', 'value' => $this->point['point']],
            ['name' => '积分变动类型', 'value' => $this->status],
            ['name' => '变动后积分数值', 'value' => $this->point['after_point']]
        ];

    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate('point_change');
        $this->organizeData();

        \Log::debug("新版公众号消息-积分1",$this->template_id);
        \Log::debug("新版公众号消息-积分2",$this->member->hasOneFans->openid);
        \Log::debug("新版公众号消息-积分3",$this->data);

        $result = (new OfficialMessageNotice($this->temp_id,$this->member->hasOneFans->openid,$this->data,[],1,$this->url))->sendMessage();


        if (app('plugins')->isEnabled('instation-message')) {
            //开启了站内消息插件
            event(new \Yunshop\InstationMessage\event\PointChangeEvent([
                'changeTime'=>date('Y-m-d H:i', time()),
                'changeType'=>$this->status,
                'changeNum'=>$this->point['point'],
                'afterChange'=>$this->point['after_point'],
                'member_id'=>$this->member->uid,
                'uniacid'=>\YunShop::app()->uniacid
            ]));
        }

        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }

    }
}