<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/7/20
 * Time: 11:15
 */

namespace app\frontend\modules\payment\controllers;


use app\common\components\BaseController;
use app\common\events\order\AfterOrderPaidRedirectEvent;
use app\common\models\Order;
use app\common\models\OrderPay;
use app\common\helpers\Url;

class CallbackPageController extends BaseController
{
    public function index()
    {
        $pay_sn = request()->input('sn');

        if ($this->getPayType($pay_sn) != 'PN') {
            return $this->successJson('不是订单支付', ['redirect' => Url::absoluteApp('home')]);
        }

        $orderPay = OrderPay::where('pay_sn', $pay_sn)->with(['orders'])->first();

        $redirect = $this->shopRedirect($orderPay);

        event($event = new AfterOrderPaidRedirectEvent($orderPay->orders,$orderPay->id));
        $plugin_redirect = $event->getData()['redirect'];

        if ($plugin_redirect) {
            $redirect = $plugin_redirect;
        }

        if (strexists($redirect, 'http://') || strexists($redirect, 'https://')) {
            $min_redirect_url = $this->shopAppletsRedirect();
        } else {
            $min_redirect_url = $redirect;
        }

        $data = [
            'redirect' => $redirect,
            'min_redirect' => $min_redirect_url,
        ];


        return $this->successJson('重定向地址', $data);
    }

    protected function getPayType($sn)
    {
        if (!empty($sn)) {
            $tag = substr($sn, 0, 2);
            return $tag;
        }
        return '';
    }


    protected function shopAppletsRedirect()
    {
        //支付跳转
        $min_redirect_url = '';
        $trade = \Setting::get('shop.trade');
        if (!is_null($trade) && isset($trade['min_redirect_url']) && !empty($trade['min_redirect_url'])) {
            $min_redirect_url = $trade['min_redirect_url'];
        }

        return $min_redirect_url;
    }


    protected function shopRedirect(OrderPay $orderPay)
    {
        $redirect =  Url::absoluteApp('home');

        $trade = \Setting::get('shop.trade');
        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
//            $redirect = $trade['redirect_url'] . '&outtradeno=' . request()->input('sn'); //如果设置的链接不是当前域名，带参数跳转空白
            $redirect = $trade['redirect_url'];

        }

        //优惠卷分享页
        $share_bool = \app\frontend\modules\coupon\services\ShareCouponService::showIndex($orderPay->order_ids, $orderPay->uid);
        if ($share_bool) {
            $ids = rtrim(implode('_', $orderPay->order_ids), '_');
            $redirect = Url::absoluteApp('coupon/share/'.$ids, ['i' => \YunShop::app()->uniacid, 'mid'=> $orderPay->uid]);
        }

        return $redirect;
    }


    protected function tempData(OrderPay $orderPay)
    {

        $redirect = '';

        $orders = $orderPay->orders;

        //优惠卷分享页
        $share_bool = \app\frontend\modules\coupon\services\ShareCouponService::showIndex($orderPay->order_ids, $orderPay->uid);
        if ($share_bool) {
            $ids = rtrim(implode('_', $orderPay->order_ids), '_');
            $redirect = Url::absoluteApp('coupon/share/'.$ids, ['i' => \YunShop::app()->uniacid, 'mid'=> $orderPay->uid]);
        }

        // 拼团订单支付成功后跳转该团页面
        // 插件开启
        if (app('plugins')->isEnabled('fight-groups')) {
            // 只有一个订单
            if ($orders->count() == 1) {
                $order = $orders[0];
                // 是拼团的订单
                if ($order->plugin_id == 54 || $order->plugin_id == 32) {
                    $fightGroupsTeamMember = \Yunshop\FightGroups\common\models\FightGroupsTeamMember::uniacid()->with(['hasOneTeam'])->where('order_id', $order->id)->first();
                    // 有团员并且有团队，跳到拼团详情页
                    if (!empty($fightGroupsTeamMember) && !empty($fightGroupsTeamMember->hasOneTeam)) {
                        if ($fightGroupsTeamMember['store_id'] != 0) {
                            $redirect = Url::absoluteApp('group_detail/' . $fightGroupsTeamMember->hasOneTeam->id . '/' . $fightGroupsTeamMember['store_id'], ['i' => \YunShop::app()->uniacid]);
                        } else {
                            $redirect = Url::absoluteApp('group_detail/' . $fightGroupsTeamMember->hasOneTeam->id, ['i' => \YunShop::app()->uniacid]);
                        }
                    }
                }
            }
        }

        if (app('plugins')->isEnabled('snatch-regiment')) {
            // 只有一个订单
            if ($orders->count() == 1) {
                $order = $orders[0];
                //抢团订单
                if ($order->plugin_id == 69) {
                    $league = \Yunshop\SnatchRegiment\models\LeagueModel::uniacid()->where("order_id", $order->id)->first();
                    if (request()->type == 5) {
                        $redirect = Url::absoluteApp('grabGroup_detail/' . $league['leader_id'], ['i' => \YunShop::app()->uniacid]);
                    }

                    if (request()->type == 2) {
                        $redirect = '/packageE/grab_group/grab_group_detail/grab_group_detail?id=' . $league['leader_id'];
                    }
                }
            }
        }

        //预约商品订单支付成功后跳转预约插件设置的页面
        if (\app\common\modules\shop\ShopConfig::current()->get('plugin.appointment.exits')) {
            \Log::debug('pay appointment order $orders：', $orders);
            // 只有一个订单
            if ($orders->count() == 1) {
                $order = $orders[0];
                // 是预约商品的订单
                if ($order->plugin_id == 101) {
                    \Log::debug('pay appointment order $order->plugin_id：', $order->plugin_id);
                    $redirect = \Yunshop\Appointment\common\service\SetService::getPayReturnUrl();
                    \Log::debug('pay appointment order $appointment_redirect：', $redirect);
                }

            }
        }

        //慈善基金跳转小程序
        if (app('plugins')->isEnabled('charity-fund')) {
            // 只有一个订单
            if ($orders->count() == 1) {
                $order = $orders[0];
                $mini_type = 2;
                $is_open = \Yunshop\CharityFund\services\SetConfigService::getSetConfig('is_open');

                \Log::debug('慈善基金调试-微信支付', [$is_open, request()->type, $mini_type, $orders]);

                //todo 支付监听添加捐赠记录不是同步进行，导致没有查询到记录，此处不使用捐赠记录判断
                //开启了插件 && 当前端口为小程序 附加跳转链接参数2
                if ($is_open && request()->type == $mini_type) {
                    \Log::debug('pay charityFund order $order->id：', $order->id);

                    //跳转小程序
                    $redirect = '/packageG/index/index?is_charity_fund=1&order_id=' . $order->id;
                }
            }
        }

        //消费奖励跳转链接
        if (app('plugins')->isEnabled('consumer-reward')) {
            $pay_id = request()->input('order_pay_id');
            $plugin_ids = [31,32,0,44,92];
            foreach ($orders as $item){
                if (in_array($item->plugin_id,$plugin_ids)){//判断在消费奖励的订单内
                    if (request()->type == 2) {//小程序
                        //跳转小程序 携带参数is_show_charity_fund_poster
                        $redirect = '/packageH/consumerReward/consumerRewardPaySuccess/consumerRewardPaySuccess?pay_id=' . $pay_id;
                    }else{//公众号
                        $redirect = yzAppFullUrl('consumerRewardPaySuccess').'&pay_id='. $pay_id;
                    }
                    break;
                }
            }
        }

        //商品免单抽奖
        if (app('plugins')->isEnabled('free-lottery')) {
            $lotteryOrderCount = \Yunshop\FreeLottery\services\LotteryDrawService::isLotteryOrder($orderPay->order_ids);
            if ($lotteryOrderCount > 0) {
                if (request()->type == 2) {//小程序
                    //跳转小程序 携带参数is_show_charity_fund_poster
                    $redirect = '/packageH/free_of_charge/FreeLottery/FreeLottery?order_ids=' . implode(",",$orderPay->order_ids);
                }else{//公众号
                    $redirect = yzAppFullUrl('FreeLottery',['i' => \YunShop::app()->uniacid,'order_ids'=>implode(",",$orderPay->order_ids)]);
                }
            }
        }


        return $redirect;
    }

}