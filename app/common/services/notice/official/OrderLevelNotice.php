<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/14
 * Time: 12:27
 */

namespace app\common\services\notice\official;

use app\common\models\MemberShopInfo;
use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;

class OrderLevelNotice extends BaseMessageBody
{

    use OfficialNoticeTemplate,OrderNoticeData,BackstageNoticeMember;

    public $orderModel;
    public $memberModel;
    public $firstData;
    public $secondData;
    public $firstOpenid;
    public $secondOpenid;
    public $firstUid;
    public $secondUid;
    public $order_status_name;

    public function __construct($order,$status_name)
    {
        $this->orderModel = $order;
        $this->order_status_name = $status_name;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '下级昵称', 'value' => $this->member->nickname],
            ['name' => '订单状态', 'value' => $this->order_status_name],
            ['name' => '订单号', 'value' => $this->orderModel->order_sn],
            ['name' => '订单金额', 'value' => $this->orderModel->price],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.

        if(\Setting::get('shop.notice.other_toggle') == false){
            \Log::debug("--两级消息通知---",'已关闭');
            return false;
        }

        $this->processData($this->orderModel);
        $this->getTemplate('other_toggle_temp');
        $this->organizeData();
        $this->subordinate();
        \Log::debug("新版公众号消息-一级1",$this->template_id);
        \Log::debug("新版公众号消息-一级2",$this->firstOpenid);
        \Log::debug("新版公众号消息-一级3",$this->data);
        if ($this->firstOpenid) {
            $firstResult = (new OfficialMessageNotice($this->temp_id,$this->firstOpenid,$this->firstData,[],1,$this->url))->sendMessage();
            if ($firstResult['status'] == 0) {
                \Log::debug("两级消息通知BUG",$firstResult['message']);
            }
        }
        $this->payed();
        \Log::debug("新版公众号消息-二级1",$this->template_id);
        \Log::debug("新版公众号消息-二级2",$this->secondOpenid);
        \Log::debug("新版公众号消息-二级3",$this->data);
        if ($this->secondOpenid) {
            $secondResult = (new OfficialMessageNotice($this->temp_id,$this->secondOpenid,$this->secondData,[],1,$this->url))->sendMessage();
            if ($secondResult['status'] == 0) {
                \Log::debug("两级消息通知BUG",$secondResult['message']);
            }
        }
    }

    public function subordinate()
    {
        $this->firstData = $this->data;

        $this->firstData[] = ['name' => '下级层级', 'value' => '一级'];

        $first = MemberShopInfo::uniacid()->with("hasOneMappingFans")->where("member_id",$this->member->yzMember->parent_id)->first();
        \Log::debug("新版公众号消息-二级4",$first);
        $this->firstUid = $first['member_id'];
        $this->secondUid = $first['parent_id'];
        $this->firstOpenid = $first['hasOneMappingFans']['openid'];
    }

    public function payed()
    {
        $this->secondData = $this->data;

        $this->secondData[] = ['name' => '下级层级', 'value' => '二级'];

        $second = MemberShopInfo::uniacid()->with("hasOneMappingFans")->where("member_id",$this->secondUid)->first();
        \Log::debug("新版公众号消息-二级5",$second);
        $this->secondOpenid = $second['hasOneMappingFans']['openid'];
    }

}