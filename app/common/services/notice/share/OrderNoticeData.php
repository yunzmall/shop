<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/4
 * Time: 10:05
 */

namespace app\common\services\notice\share;

Trait OrderNoticeData
{

    public $order;
    public $orderGoods;//订单商品
    public $address;//收货地址
    public $timeData;//时间数据
    public $member;//会员相关信息
    public $fans;//单个会员的fans(公众号)
    public $miniFans;//单个会员的fans（小程序）
    public $goodsId;//商品ID
    public $goodsTitle='';//商品标题
    public $goodsNum;//商品购买数量

    public function processData($order)
    {
        //订单数据整理
        $this->order = $order;

        //会员数据整理
        $this->member = $order->belongsToMember;

        //单个会员的Fans(公众号)
        $this->fans = $order->belongsToMember->hasOneFans;

        //单个会员的Fans(小程序)
        $this->miniFans = $order->belongsToMember->hasOneMiniApp;

        //地址数据整理
        $this->address = $order->address;

        //时间数据整理
        $this->timeData = [
            'create_time' => $order->create_time->toDateTimeString(),
            'pay_time' => $order->pay_time->toDateTimeString(),
            'finish_time' => $order->finish_time->toDateTimeString(),
            'cancel_time' => $order->cancel_time->toDateTimeString(),
            'send_time' => $order->send_time->toDateTimeString()
        ];

        //订单商品数据整理
        $orderGoods = $order->hasManyOrderGoods()->get();
        $orderGoods = empty($orderGoods) ? [] : $orderGoods->toArray();
        if (count($orderGoods)>0) {
            $this->orderGoods = $orderGoods;
            foreach ($orderGoods as $kk=>$vv) {
                $this->goodsId[] = $vv['goods_id'];

                $this->goodsTitle .= $vv['title'];
                if ($vv['goods_option_title']) {
                    $this->goodsTitle .= '[' . $vv['goods_option_title'] . '],';
                }
                $this->goodsNum += $vv['total'];
            }
            $this->goodsTitle = rtrim($this->goodsTitle,',');
        }
    }
}