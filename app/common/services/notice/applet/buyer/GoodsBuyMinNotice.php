<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/7
 * Time: 21:16
 */

namespace app\common\services\notice\applet\buyer;

use app\common\models\Order;
use app\common\models\Notice;
use app\common\services\notice\applet\AppletMessageNotice;
use app\common\services\notice\BaseMessageBody;
use app\common\services\notice\share\BackstageNoticeMember;
use app\common\services\notice\share\GoodsNoticeData;
use app\common\services\notice\share\OrderNoticeData;
use app\common\services\notice\share\MiniNoticeTemplate;

class GoodsBuyMinNotice extends BaseMessageBody
{

    use OrderNoticeData,BackstageNoticeMember,MiniNoticeTemplate,GoodsNoticeData;

    public $orderModel;
    protected $goodsNum;//商品购买数量
    protected $goodsPrice;
    protected $orderStatus;//订单状态
    protected $goodName;
    public function __construct($order,$status)
    {
        $this->orderModel = $order;
        $this->orderStatus = $status;
    }

    public function organizeData()
    {
        // TODO: Implement organizeData() method.
        $this->data = [
            'keyword1'=>['value'=> $this->member->nickname],// 会员姓名
            'keyword2'=>['value'=>  $this->order->order_sn],//订单号
            'keyword3'=>['value'=> $this->goodName],// 物品名称
            'keyword4'=>['value'=> $this->goodsNum],//  数量
            'keyword5'=>['value'=> $this->goodsPrice],// 购买金额
            'keyword6'=>['value'=> $this->getOrderTime($this->orderStatus)],//购买时间
        ];
    }

    public function sendMessage()
    {
        // TODO: Implement sendMessage() method.
        if (count($this->orderGoods) > 0) {
            $this->processData($this->orderModel);
            $this->getBackMember();
            $this->getGoodsBuy($this->goodsId);

            foreach ($this->orderGoods as $kk=>$vv) {
                $status = $this->getGoodsStatus($vv['goods_id']);

                if (in_array($vv['goods_id'],$this->goodsBuy) && $status) {
                    $option_title = empty($vv->goods_option_title) ? "" : $vv->goods_option_title;
                    $this->goodsNum = $vv['total'];
                    $this->goodsPrice = $vv['goods_price'];
                    $this->goodName = $vv['title'].$option_title;
                    $openId = $this->getGoodsMember($vv['goods_id']);
                    $this->getTemplate("购买成功通知");
                    $this->organizeData();
                    $result =  (new AppletMessageNotice($this->temp_id,$openId,$this->data,[],2))->sendMessage();

                    if ($result['status'] == 0) {
                        \Log::debug($result['message']);
                    }
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

    private function getGoodsMember($goods_id)
    {
       if (count($this->goodsBuy) > 0) {
           foreach ($this->goodsBuy as $kk=>$vv) {
               if ($vv['goods_id'] == $goods_id) {
                   $openid = empty($vv['has_one_mini']) ? 0 : $vv['has_one_mini'];
                   return $openid;
               }
          }
       }

       return 0;
    }

    private function getGoodsStatus($goods_id)
    {
        if (count($this->goodsBuy) > 0) {
            foreach ($this->goodsBuy as $kk=>$vv) {
                if ($vv['goods_id'] == $goods_id && $vv['type'] == $this->orderStatus) {
                    return true;
                }
            }
        }

        return false;
    }
}