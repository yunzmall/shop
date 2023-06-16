<?php
/**
 * Author:
 * Date: 2017/5/25
 * Time: 下午4:55
 */

namespace app\common\services\member\level;


use app\common\events\member\MemberLevelUpgradeEvent;
use app\common\events\member\MemberLevelValidityEvent;
use app\common\events\order\AfterOrderPaidEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\facades\Setting;
use app\common\models\Member;
use app\common\models\member\MemberChildren;
use app\common\models\MemberLevel;
use app\common\models\MemberShopInfo;
use app\common\models\notice\MessageTemp;
use app\common\models\Order;
use app\common\services\MessageService;
use app\common\services\notice\official\MemberUpgradeNotice;
use Yunshop\UniversalCard\models\ConsumeCoupon;
use Yunshop\UniversalCard\models\ConsumeCouponLog;
use Yunshop\UniversalCard\models\UniversalCardLevel;
use Yunshop\UniversalCard\services\MsgSendService;
use Exception;

class LevelUpgradeService
{
    private $orderModel;
    private $orderId;
    private $memberModel;
    private $new_level;
    private $validity;

    public function test($order)
    {
        $this->orderModel = $order;
        $this->memberModel = MemberShopInfo::ofMemberId($this->orderModel->uid)->withLevel()->first();
        $result = $this->check(0);
        dd('ok');
        exit;
        $this->setValidity($result); // 设置会员等级期限
        if ($result) {
            return $this->upgrade($result);
        }
        return '';
    }


    /*
     * 插件奖励会员等级升级
     * 插件:企业微信好友分裂、
     * member_id:升级的会员id
     * after_level 要升级到的会员等级，yz_member_level表模型
     */
    public function pluginUpgrade($member_id, $after_level)
    {
        try {
            \Log::debug('会员等级升级-插件升级开始' . $member_id, $after_level);
            if (!$this->memberModel = MemberShopInfo::ofMemberId($member_id)->withLevel()->first()) {
                throw new Exception('会员不存在');
            }
            $this->check(0, $after_level);
            $member_level_weight = intval($this->memberModel->level->level) ?: 0;
            $this->validity['is_goods'] = 1;
            $this->validity['goods_total'] = 1;
            if ($after_level->level > $member_level_weight){
                $this->validity['upgrade'] = 1;
                $this->validity['superposition'] = 1;
            }
            $this->setValidity($after_level->level > $member_level_weight);
            if ($this->validity['upgrade'] && $this->upgrade($after_level->id) !== true) {
                throw new Exception('未知原因');
            }
            \Log::debug('会员等级升级-插件升级结束' . $member_id, $after_level);
            return ['result' => 1, 'msg' => '成功', 'data' => []];
        } catch (Exception $e) {
            return ['result' => 0, 'msg' => '会员等级升级失败:' . $e->getMessage(), 'data' => []];
        }
    }

    public function checkUpgrade(AfterOrderReceivedEvent $event)
    {
        //event(new AfterOrderReceivedEvent(Order::where('status',3)->first()));
        $this->orderId = $event->getOrderModel()->id;
        $this->orderModel = Order::find($event->getOrderModel()->id);//$event->getOrderModel();

        $uniacid = $this->orderModel->uniacid;
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniacid;
        $this->memberModel = MemberShopInfo::ofMemberId($this->orderModel->uid)->withLevel()->first();
        if (is_null($this->memberModel)) {
                \Log::info('---==会员不存在==----');
            return;
        }

        $result = $this->check(0);
                \Log::info('---==check方法结果==----', $result);


        $this->setValidity($result); // 设置会员等级期限
        if ($result) {
            return $this->upgrade($result);
        }
        return '';
    }

    public function checkUpgradeAfterPaid(AfterOrderPaidEvent $event)
    {
        $this->orderId = $event->getOrderModel()->id;
        $this->orderModel = $event->getOrderModel();

        $uniacid = $this->orderModel->uniacid;
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniacid;
        //\Event::getListeners('app\common\events\order\AfterOrderPaidEvent');
        \Log::debug('checkUpgradeAfterPaid');
        $set = \Setting::get('shop.member');


        /*if (!is_null($set)) {
            $uniacid = $this->orderModel->uniacid;
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniacid;

            $set = Setting::get('shop.member');
        }*/
        if ($set['level_after'] != 1) {
            return;
        }

        $this->memberModel = MemberShopInfo::ofMemberId($this->orderModel->uid)->withLevel()->first();
        if (is_null($this->memberModel)) {
            return;
        }
        $result = $this->check(1);
        $this->setValidity($result); // 设置会员等级期限
        if ($result) {
            return $this->upgrade($result);
        }

        return '';
    }

    public function setValidity($isUpgrate = false)
    {
        $set = \Setting::get('shop.member');
        if (!$set['term']) {
            return;
        }
        if (!$this->validity['is_goods']) {
            return;
        }
        $current_level_id = '';
        if ($this->validity['upgrade']) {
            $current_level_id = $this->new_level->id;
            $validity = $this->new_level->validity * $this->validity['goods_total'];
        } else {
            //bug 会员当前等级 > 新的等级  有效期不应该叠加, 当等级相等时才叠加
            //$validity = $this->memberModel->validity + $this->new_level->validity * $this->validity['goods_total'];
            if ($this->validity['superposition']) {
                $current_level_id = $this->memberModel->level->id;
                $validity = $this->memberModel->validity + $this->new_level->validity * $this->validity['goods_total'];
            }
        }
        if (isset($validity)) {
            //一卡通消费券
            if (app('plugins')->isEnabled('consume-coupon-switch') && Setting::get('plugin.consume_coupon_switch.status') == 1) {
                if (app('plugins')->isEnabled('universal-card') && Setting::get('plugin.universal_card.switch') == 1) {
                    $consume_coupon = ConsumeCoupon::uniacid()->where('member_id', $this->memberModel->member_id)->first();
                    $universalCardLevel = UniversalCardLevel::uniacid()->where('member_level_id', $current_level_id)->first();
                    if ($universalCardLevel->consume_coupon_num) {
                        $get_consume_coupon = $universalCardLevel->consume_coupon_num * $this->validity['goods_total'];
                        if ($consume_coupon) {
                            $consume_coupon->amount = $consume_coupon->amount + $get_consume_coupon;
                        } else {
                            $consume_coupon = new ConsumeCoupon();
                            $consume_coupon->uniacid = $this->memberModel->uniacid;
                            $consume_coupon->member_id = $this->memberModel->member_id;
                            $consume_coupon->amount = $consume_coupon->amount + $get_consume_coupon;
                        }
                        $consume_coupon->amount = round($consume_coupon->amount, 2);
                        $consume_coupon->status = 1;
                        if ($consume_coupon->save()) {
                            $data = [
                                'uniacid' => \YunShop::app()->uniacid,
                                'member_id' => $this->memberModel->member_id,
                                'store_id' => 0,
                                'order_id' => $this->orderModel->id,
                                'type' => 1,
                                'amount' => $get_consume_coupon,
                                'created_at' => time(),
                            ];
                            ConsumeCouponLog::create($data);
                            MsgSendService::getConsumeCoupon($get_consume_coupon, $this->memberModel->member_id);
                        };
                    }
                }
            }
            $this->memberModel->validity = $validity;
            $this->memberModel->downgrade_at = 0;
            $this->memberModel->save();
            if (!$isUpgrate) {
                $levelId = intval($this->new_level->id);
                event(new MemberLevelValidityEvent($this->memberModel, $this->validity['goods_total'], $levelId));
            }
        }
    }

    private function check($status,$new_level = null)
    {
        $set = \Setting::get('shop.member');
                \Log::info('---==等级设置信息==----', [unserialize($set), json_decode($set, true)]);

        if (!$new_level) {
            //获取可升级的最高等级
            switch ($set['level_type']) {
                case 0:
                    $this->new_level = $this->checkOrderMoney();
                    break;
                case 1:
                    $this->new_level = $this->checkOrderCount();
                    break;
                case 2:
                    if ($status == 1) {
                        if ($set['level_after']) {
                            $this->new_level = $this->checkGoodsId();
                        }
                    } else {
                        if (!$set['level_after']) {
                            $this->new_level = $this->checkGoodsId();
                        }
                    }
                    break;
                case 3:
                    // 此方法不返回新等级,升级单独处理
                    $this->new_level = $this->checkTeamPerformance();
                    break;
                default:
                    $level = '';
            }
        } else {
            $this->new_level = $new_level;
        }

        \Log::debug('判断是否升级',$this->new_level);
        //比对当前等级权重，判断是否升级
        if ($this->new_level) {
            $memberLevel = isset($this->memberModel->level->level) ? $this->memberModel->level->level : 0;

                \Log::info('---==会员等级信息==----', [$memberLevel, $this->new_level->level]);

            if ($this->new_level->level == $memberLevel) {
                $this->validity['superposition'] = true; //会员期限叠加
            }

            if ($this->new_level->level > $memberLevel) {
                $this->validity['upgrade'] = true; // 会员期限 升级 期限叠加
                return $this->new_level->id;
            }
            return '';
        }
        return '';
    }

    /**
     * 会员完成订单总金额，返回对应最高会员等级数组
     * @return mixed
     */
    private function checkOrderMoney()
    {
        $set = \Setting::get('shop.member');
        if ($set['level_after'] == 1) {
            //付款后
            $orderMoney = Order::where('uid', $this->orderModel->uid)->whereBetween('status', [Order::WAIT_SEND,Order::COMPLETE])->sum('price');
        } else {
            //完成后
            $orderMoney = Order::where('uid', $this->orderModel->uid)->where('status', Order::COMPLETE)->sum('price');
        }

        //获取满足条件的最高等级
        $level = MemberLevel::uniacid()->select('id', 'level', 'level_name')->whereBetween('order_money', [1, $orderMoney])->orderBy('level', 'desc')->first();

        return $level;
    }

    /**
     * 团队业绩(自购+一级+二级)
     * @return mixed
     */
    private function checkTeamPerformance()
    {
        $set = \Setting::get('shop.member');

        // 这重新查询是因为orderModel的uid是435但是实际下单的人事438,为啥,不知道.懵逼
        $order = Order::find($this->orderId);


        $this->memberModel = MemberShopInfo::ofMemberId($order->uid)->withLevel()->first();

        $uidArr = [
            $this->memberModel->member_id
        ];
        if ($this->memberModel->parent_id) {
            $uidArr[] = $this->memberModel->parent_id;
            $oneParent = MemberShopInfo::select(['member_id', 'parent_id'])
                ->where('member_id', $this->memberModel->parent_id)
                ->first();
            if ($oneParent->parent_id) {
                $uidArr[] = $oneParent->parent_id;
            }
        }


        $status = Order::COMPLETE;
        if ($set['level_after'] == 1) {
            //付款后
            $status = Order::WAIT_SEND;
        }


        foreach ($uidArr as $uid) {
            $teamPerformance = MemberChildren::select(['yz_member_children.child_id', 'yz_member_children.member_id', 'yz_member_children.level'])
                ->join('yz_order','yz_member_children.child_id','=','yz_order.uid')
                ->where('yz_order.status', '>=', $status)
                ->where('yz_member_children.level', '<', 3)
                ->where('yz_member_children.member_id', $uid)
                ->sum('yz_order.price');

            $teamPerformance += Order::where('uid', $uid)
                ->where('status', '>=', $status)
                ->sum('price');


            //获取满足条件的最高等级
            $level = MemberLevel::uniacid()
                ->select('id', 'level', 'level_name')
                ->whereBetween('team_performance', [1, $teamPerformance])
                ->orderBy('level', 'desc')
                ->first();


            if ($level) {
                $memberModel = MemberShopInfo::ofMemberId($uid)->withLevel()->first();

                //查询会员不存在则不升级
                if (is_null($memberModel)) {
                    continue;
                }

                $memberLevel = isset($memberModel->level->level) ? $memberModel->level->level : 0;
                if ($level->level > $memberLevel) {
                    $memberModel->level_id = $level->id;
                    $memberModel->upgrade_at = time();

                    if ($memberModel->save()) {
                        //会员等级升级触发事件
                        event(new MemberLevelUpgradeEvent($memberModel,false));
                        $pluginLevel=[
                            'member_id'=>$memberModel->member_id,
                            'level_id'=>$memberModel->level_id,
                            'plugin_type' => 1
                        ];
                        event(new \app\common\events\PluginLevelEvent($pluginLevel));
                        $this->notice($level);
                    }
                }
            }
        }

        return '';
    }

    /**
     * 会员完成订单总数量，返回对应最高会员等级数组
     * @return mixed
     */
    private function checkOrderCount()
    {
        $set = \Setting::get('shop.member');
        if ($set['level_after'] == 1) {
            //付款后
            $orderCount = Order::where('uid', $this->orderModel->uid)->whereBetween('status', [Order::WAIT_SEND,Order::COMPLETE])->count();
        } else {
            //完成后
            $orderCount = Order::where('uid', $this->orderModel->uid)->where('status', Order::COMPLETE)->count();
        }

        $level = MemberLevel::uniacid()->select('id', 'level', 'level_name')->whereBetween('order_count', [1, $orderCount])->orderBy('level', 'desc')->first();

        return $level;
    }

    /**
     * 当前订单中满足升级会员等级的 最高会员等级数组，空返回 array
     * @return array
     */
    private function checkGoodsId()
    {
        $reallevel = [];
        $goodsIds = array_pluck($this->orderModel->hasManyOrderGoods->toArray(), 'goods_id');

        \Log::debug('---==get_order_model==---', $this->orderModel);
        \Log::debug('---==get_member_model==---', $this->memberModel);
        // $level = MemberLevel::uniacid()->select('id', 'level', 'level_name', 'goods_id', 'validity')->whereIn('goods_id', $goodsIds)->orderBy('level', 'desc')->first();  // 原先逻辑为购买指定某一商品即可升级, 现为购买指定任易商品即可升级
        //获取

        $levelid = MemberLevel::find($this->memberModel->level_id);

        \Log::debug('---==levelid==---', $levelid);

        $levels = MemberLevel::uniacid()->where('level', '>=', $levelid->level ? : 0)->select('id', 'level', 'level_name', 'goods_id', 'validity')->orderBy('level', 'desc')->get();

        \Log::debug('---==levels==---', $levels);

        $this->validity['is_goods'] = true; // 商品升级 开启等级期限

        foreach ($this->orderModel->hasManyOrderGoods as $time) {
            // if ($time->goods_id == $level->goods_id) { // 原先逻辑为购买指定某一商品即可升级, 现为购买指定任易商品即可升级

            foreach ($levels as  $level) {

                $levelGoodsId = explode(',', $level->goods_id);

        \Log::debug('---==levelGoodsId==---', $levelGoodsId);
        \Log::debug('---==checkInarray==---', in_array($time->goods_id, $levelGoodsId));

                if (in_array($time->goods_id, $levelGoodsId)) {
                    
                    $this->validity['goods_total'] = $time->total;

                    $reallevel = MemberLevel::find($level->id);

                    \Log::debug('---===member_level_upgrade===---', $time->total);

                    //开启一卡通
                    if (app('plugins')->isEnabled('universal-card')) {
                        
                        if ($time->goods_option_id) {
                            $reallevel->validity = (new \Yunshop\UniversalCard\services\LevelUpgradeService())->upgrade($level->id, $time->goods_option_id);
                        }
                    }
                }
            }
        }
        // return $level ?: [];
        return $reallevel;
    }

    private function upgrade($levelId)
    {
        $this->memberModel->level_id = $levelId;
        $this->memberModel->upgrade_at = time();
        if ($this->memberModel->save()) {
            //会员等级升级触发事件
            $pluginLevel=[
                'member_id'=>$this->orderModel->uid,
                'level_id'=>$levelId,
                'plugin_type' => 1
            ];
            event(new MemberLevelUpgradeEvent($this->memberModel,false));
            event(new \app\common\events\PluginLevelEvent($pluginLevel));
            event(new MemberLevelValidityEvent($this->memberModel, $this->validity['goods_total'], $levelId));
            $this->notice();
            \Log::info('会员ID' . $this->memberModel->member_id . '会员等级升级成功，等级ID' . $levelId);
        } else {
            \Log::info('会员ID' . $this->memberModel->member_id . '会员等级升级失败，等级ID' . $levelId);
        }
        //todo 会员等级升级通知
        return true;
    }

    private function notice($newLevel = null)
    {
        //$template_id = \Setting::get('shop.notice.customer_upgrade');

        if(!empty($this->new_level)){
            $memberNotice = new MemberUpgradeNotice($this->memberModel,$this->new_level);
        }elseif (!empty($newLevel)){
            $memberNotice = new MemberUpgradeNotice($this->memberModel,$newLevel);
        }else{
            \Log::debug("会员升级消息--新等级信息为空",[$this->memberModel,$newLevel]);
            return true;
        }
        $memberNotice->sendMessage();
        return ;
        if (!trim($template_id)) {
            return '';
        }
        $memberModel = Member::select('uid', 'nickname', 'realname')->where('uid', $this->memberModel->member_id)->with('hasOneFans')->first();

        $member_name = $memberModel->realname ?: $memberModel->nickname;

        $set = \Setting::get('shop.member');
        $old_level = $set['level_name'] ?: '普通会员';
        $old_level = $this->memberModel->level->level_name ?: $old_level;

        $params = [
            ['name' => '粉丝昵称', 'value' => $member_name],
            ['name' => '旧等级', 'value' => $old_level],
            ['name' => '新等级', 'value' => $this->new_level->level_name],
            ['name' => '时间', 'value' => date('Y-m-d H:i',time())],
            ['name' => '有效期', 'value' => $this->memberModel->validity.'天'],
        ];

        $msg = MessageTemp::getSendMsg($template_id, $params);
        if (!$msg) {
            return;
        }
        $news_link = MessageTemp::find($template_id)->news_link;
        $news_link = $news_link ?:'';
        MessageService::notice(MessageTemp::$template_id, $msg, $memberModel->uid,'',$news_link);
    }


}
