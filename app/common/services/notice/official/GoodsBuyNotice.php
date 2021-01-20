<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/7
 * Time: 21:46
 */

namespace app\common\services\notice\official;


use app\backend\modules\goods\models\Notice;
use app\common\models\Order;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\OfficialNoticeTemplate;
use app\common\services\notice\share\OrderNoticeData;
use app\common\models\Member;

class GoodsBuyNotice extends BaseMessageBody
{

    protected $orderModel;
    protected $goodsNumber;//商品购买数量
    protected $goodsPrice;
    protected $orderStatus;//订单状态
    protected $goodName;
    use OrderNoticeData,OfficialNoticeTemplate,BackstageNoticeMember;

    public function __construct($order,$status)
    {
        $this->orderModel = $order;
        $this->orderStatus = $status;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            ['name' => '会员昵称', 'value' => $this->member->nickname],
            ['name' => '订单编号', 'value' => $this->order->order_sn],
            ['name' => '商品名称（含规格）', 'value' => $this->goodName],
            ['name' => '商品金额', 'value' => $this->goodsPrice],
            ['name' => '商品数量', 'value' => $this->goodsNumber],
            ['name' => '订单状态', 'value' => $this->order->status_name],
            ['name' => '时间', 'value' => $this->getOrderTime($this->orderStatus)],
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.

        $this->processData($this->orderModel);
        $this->getTemplate('buy_goods_msg');
        
        if(
            (empty(\Setting::get('shop.notice')['notice_enable']['created']) && $this->orderStatus == 1) ||
            (empty(\Setting::get('shop.notice')['notice_enable']['paid']) && $this->orderStatus == 2) ||
            (empty(\Setting::get('shop.notice')['notice_enable']['received']) && $this->orderStatus == 3)
        ){
            return;
        }

        $this->getBackMember();
        if (count($this->orderGoods) > 0) {
            foreach ($this->orderGoods as $kk=>$vv) {
                $openids = $this->openids;
                //获取该商品设置的通知人
                $notices = Notice::where('goods_id',$vv['goods_id'])
                    ->where('type',$this->orderStatus)
                    ->first();
                if ($notices) {
                    if (!$notices['uid']) {
                        continue;
                    }
                    $saler = Member::uniacid()
                        ->with('hasOneFans')
                        ->where('uid',$notices['uid'])
                        ->first();

                    if (!$saler) {
                        continue;
                    }
                    $saler = $saler->toArray();
                    if($saler && !empty($saler['has_one_fans']) && !in_array($saler['has_one_fans']['openid'],$openids)){
                        $openids[] = $saler['has_one_fans']['openid'];
                    }
                }

                $this->goodsNumber = $vv['total'];
                $this->goodsPrice = $vv['goods_price'];
                $option_title = empty($vv->goods_option_title) ? "" : $vv->goods_option_title;
                $this->goodName = $vv['title'].$option_title;
                $this->organizeData();

                \Log::debug("新版公众号消息-商品1-".$kk,$this->template_id);
                \Log::debug("新版公众号消息-商品2-".$kk,$openids);
                \Log::debug("新版公众号消息-商品3-".$kk,$this->data);

                $result = (new OfficialMessageNotice($this->temp_id,0,$this->data,$openids,1,$this->url))->sendMessage();

                if ($result['status'] == 0) {
                    \Log::debug($result['message'],$this->order);
                }
            }
        }
    }

    private function getOrderTime($status)
    {
        if ($status == 1) {
            $order_time = $this->timeData['create_time'];
        } else if ($status == 2) {
            $order_time = $this->timeData['pay_time'];
        } else if ($status == 3) {
            $order_time = $this->timeData['finish_time'];
        }
        return $order_time;
    }
}