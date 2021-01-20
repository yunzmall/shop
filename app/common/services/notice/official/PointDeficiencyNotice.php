<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 10:26
 */

namespace app\common\services\notice\official;


use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\OfficialNoticeTemplate;

class PointDeficiencyNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate;

    public $member;
    public $point;
    public $set;


    public function __construct($member,$point)
    {
        $this->member = $member;
        $this->point = $point;
        $this->set = \Setting::get('point.set');
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '昵称', 'value' => $this->member['nickname']],
            ['name' => '时间', 'value' => date('Y-m-d H:i', time())],
            ['name' => '通知额度', 'value' => $this->set['point_floor']],
            ['name' => '当前积分', 'value' => $this->point['after_point']],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        $this->getTemplate('point_deficiency');
        $this->organizeData();

        \Log::debug("新版公众号消息-积分不足1",$this->template_id);
        \Log::debug("新版公众号消息-积分不足2",$this->member->hasOneFans->openid);
        \Log::debug("新版公众号消息-积分不足3",$this->data);

        $result = (new OfficialMessageNotice($this->temp_id,$this->member->hasOneFans->openid,$this->data,[],1,$this->url))->sendMessage();
        if ($result['status'] == 0) {
            \Log::debug($result['message']);
        }
    }
}