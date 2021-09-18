<?php
/**
 * 系统通知消息服务
 */
namespace app\common\services;

use app\backend\modules\coupon\models\Coupon;
use app\common\facades\SiteSetting;
use app\common\models\systemMsg\SysMsgLog;
use app\backend\modules\order\models\Order;
use app\common\models\UniAccount;
use app\common\models\Withdraw;
use app\framework\Support\Facades\Log;

class SystemMsgService
{
    //链接跳转配置
    public static $url = [
        'order' => 'order.list.index',  //商城全部订单页面
        'cashier_order' => 'plugin.store-cashier.admin.order.detail',//商城收银台订单
        'store_order' => 'plugin.store-cashier.admin.stores.store-order.detail',//商城门店订单
        'group_order' => 'plugin.fight-groups.admin.controllers.order-management.list-order',//商城拼团订单
        'lease_order' => 'plugin.lease-toy.admin.order.index',//商城租凭订单
        'supplier_order' => 'plugin.supplier.admin.controllers.order.supplier-order.index',//商城供应商订单
        'jd_order' => 'plugin.jd-supply.admin.order-list.index',//商城聚合供应链订单
        'yz_order' => 'plugin.yz-supply.admin.order-list.index',//商城芸众供应链订单
        'appointment_order' => 'plugin.appointment.admin.order.index',//商城预约订单
        'withdraw' => 'withdraw.records', //提现记录页面
        'coupon' => 'coupon.coupon.index', //优惠券列表页面
        'goods' => 'goods.goods.index', //商品列表页面
        'goods_edit' => 'goods.goods.edit', //商品编辑页面
        'store_goods_edit'=>'plugin.store-cashier.admin.goods.edit',//门店商品编辑页面
        'cashier_goods_edit'=>'plugin.store-cashier.admin.goods.edit',//门店收银台商品编辑页面
        'hotel_goods_edit'=>'plugin.hotel.admin.goods.edit',//酒店商品编辑页面
        'lease_toy_goods_edit'=>'plugin.lease-toy.admin.goods.edit',//租聘商品编辑页面
        'net_car_goods_edit'=>'plugin.net-car.admin.net-car-goods.edit',//网约车商品编辑页面
        'jd_supply_goods_edit'=>'plugin.jd-supply.admin.shop-goods.edit',//京东-聚合供应链商品编辑页面
        'yz_supply_goods_edit'=>'plugin.yz-supply.admin.shop-goods.edit',//芸众供应链商品编辑页面
        'supplier_goods_edit'=>'plugin.supplier.admin.controllers.goods.goods-operation.edit',//供应商商品编辑页面
        'supplier_apply' => 'plugin.supplier.admin.controllers.apply.supplier-apply.index', //供应商申请页面
        'area-dividend_apply' => 'plugin.area-dividend.admin.agent.agent-apply', //区域分红代理申请页面
        'merchant_apply' => 'plugin.merchant.backend.merchant-apply.index', //招商中心/招商员申请页面
        'store-cashier_apply' => 'plugin.store-cashier.admin.store-apply.apply-list', //门店申请页面
        'hotel_apply' => 'plugin.hotel.admin.apply.index', //酒店申请页面
        'package-deliver_apply' => 'plugin.package-deliver.admin.apply.manage', //自提点申请页面
        'provider-platform_apply' => 'plugin.provider-platform.admin.tripartiteProvider.list.subplatform-audit', //第三方子平台入驻申请页面
        'advert-market_apply' => 'plugin.advert-market.admin.apply.manage.index', //广告主申请页面
        'room_apply' => 'plugin.room.admin.anchor-manage.apply', //主播申请页面
        'circle_apply' => 'plugin.circle.admin.circle.index', //圈子审核页面
    ];
    public function __construct($uniacid = '')
    {
        if ($uniacid) {
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniacid;
        } else{
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid;
        }
    }

    /**
     * @param $msgType
     * @param array $params
     * @return bool
     */
    public function sendSysMsg($msgType,array $params)
    {
        switch ($msgType)
        {
            case  1:
                //系统通知
            case 2:
                //订单通知
            case 3:
                //提现通知
            case 4:
                //申请通知
            case 5:
                //商品库存
            case 6:
                //优惠券
                return $this->addMessage($params,$msgType);break;
            default:
                \Log::error("系统通知-------消息类型错误");
                return false;
        }
    }

    //添加消息数据到数据库
    private function addMessage($params,$msgType)
    {
        //todo 可使用队列？
        $data = [
            'uniacid' => \YunShop::app()->uniacid,
            'type_id' => $msgType,
            'title' => $params['title'],
            'content' => $params['content'],
            'redirect_url' => $params['redirect_url'],
            'redirect_param' => $params['redirect_param'],
            'msg_data' => serialize($params),
        ];
        $sysMsg = new SysMsgLog();
        $sysMsg->fill($data);
        if($sysMsg->save()){
            return true;
        }
        \Log::error("---------系统通知失败---------");
        return false;
    }

    public static function addWorkMessage($params,$uniacid='')
	{
		$queueSetting = SiteSetting::get('queue');
		if ($queueSetting['receive_message'] == 1) {
			return;
		}
		$data = [];
		$uniacid = $uniacid ?:\YunShop::app()->uniacid;
		if ($uniacid) {
			$uniAccount[] = ['uniacid'=>$uniacid];
		} else {
			$uniAccount = UniAccount::getEnable();
		}
		foreach ($uniAccount as $u) {
			$data[] = [
				'uniacid' => $u['uniacid'],
				'type_id' => 1,
				'title' => $params['title'],
				'content' => mb_substr($params['content'],0,800,'utf-8'),
				'redirect_url' => '',
				'redirect_param' => '',
				'msg_data' => serialize($params),
				'created_at' => time(),
				'updated_at' => time(),
			];
		}
		if ($data) {
			SysMsgLog::insert($data);
		}
	}

    private function getPlugins($order_sn,$order_id = 0)
    {
        return  $plugin = [
            0=>[
                'name'=>'商城',
                'redirect_url'=>'order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
            31 => [
                'name'=>'收银台',
                'redirect_url'=>'cashier_order',
                'redirect_param' => [
                    'order_id'=>$order_id
                ]
            ],
            32 => [
                'name'=>'门店',
                'redirect_url'=>'store_order',
                'redirect_param' => [
                    'id'=>$order_id,
                ]
            ],
            54=>[
                'name'=>'拼团',
                'redirect_url'=>'group_order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
            40=>[
                'name'=>'租赁',
                'redirect_url'=>'lease_order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
            92=>[
                'name'=>'供应商',
                'redirect_url'=>'supplier_order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
            44=>[
                'name'=>'聚合供应链',
                'redirect_url'=>'jd_order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
            120=>[
                'name'=>'聚合供应链',
                'redirect_url'=>'yz_order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
            101=>[
                'name'=>'门店预约',
                'redirect_url'=>'appointment_order',
                'redirect_param' => [
                    'search[ambiguous][string]'=>$order_sn,
                    'search[ambiguous][field]'=>'order'
                ]
            ],
        ];
    }

    //订单收货消息
    public function received($order)
    {
        $msg_type = 2;  //通知类型 1系统通知 2订单通知 3提现通知 4申请通知 5商品库存 6优惠券
        $plugin = $this->getPlugins($order->order_sn,$order->id);
        if(empty($plugin[$order->plugin_id])){
            return false;
        }
        $param = [
            'title'=>'您有'.$plugin[$order->plugin_id]['name'].'订单买家已确认收货！',
            'content' => '时间:'.$order->finish_time->toDateTimeString().' || '
                .'订单编号:'.$order->order_sn.' || '
                .'会员:'.$order->belongsToMember->nickname.' || '
                .'状态:'.$order->status_name,
            'redirect_url' => $plugin[$order->plugin_id]['redirect_url'],
            'redirect_param' => $plugin[$order->plugin_id]['redirect_param']
        ];
        return $this->sendSysMsg($msg_type,$param);
    }

    //订单退款、退货、换货
    public function applyRefundNotice($refundOrder)
    {
        $msg_type = 2;  //通知类型 1系统通知 2订单通知 3提现通知 4申请通知 5商品库存 6优惠券
        $nickname = $this->getMemberNickname($refundOrder->uid);
        $order = Order::find($refundOrder->order_id);
        if(empty($order)){
            Log::error('【系统消息通知】--订单退款-未找到订单信息');
            return false;
        }
        $plugin = $this->getPlugins($order->order_sn,$refundOrder->order_id);
        if(empty($plugin[$order->plugin_id])){
            return false;
        }
        $param = [
            'title'=>'您有'.$plugin[$order->plugin_id]['name'].$refundOrder->refund_type_name.'订单，请及时处理！',
            'content' => '时间:'.$refundOrder->create_time.' || '
                .'订单编号:'.$order->order_sn.' || '
                .'会员:'.$nickname.' || '
                .'状态:'.$refundOrder->status_name,
            'redirect_url' => $plugin[$order->plugin_id]['redirect_url'],
            'redirect_param' => $plugin[$order->plugin_id]['redirect_param']
        ];
        return $this->sendSysMsg($msg_type,$param);
    }

    //订单支付，待发货通知
    public function paid($order)
    {
        $msg_type = 2;  //通知类型 1系统通知 2订单通知 3提现通知 4申请通知 5商品库存 6优惠券
        $plugin = $this->getPlugins($order->order_sn,$order->id);
        if(empty($plugin[$order->plugin_id])){
            return false;
        }
        $param = [
            'title'=>'您有新的'.$plugin[$order->plugin_id]['name'].'订单，请及时处理！',
            'content' => '时间:'.$order['pay_time']->toDateTimeString().' || '
                .'订单编号:'.$order->order_sn.' || '
                .'会员:'.$order->belongsToMember->nickname.' || '
                .'状态:'.$order->status_name,
            'redirect_url' => $plugin[$order->plugin_id]['redirect_url'],
            'redirect_param' => $plugin[$order->plugin_id]['redirect_param']
        ];
        return $this->sendSysMsg($msg_type,$param);
    }

    //提现申请
    public function withdrawNotice($withdrawModel)
    {
        $msg_type = 3;  //通知类型 1系统通知 2订单通知 3提现通知 4申请通知 5商品库存 6优惠券

        $statusComment = [
            Withdraw::STATUS_INVALID => '审核无效',
            Withdraw::STATUS_INITIAL => '提现申请',
            Withdraw::STATUS_AUDIT   => '审核通过',
            Withdraw::STATUS_PAY     => '已打款',
            Withdraw::STATUS_REBUT   => '审核驳回',
            Withdraw::STATUS_PAYING  => '打款中',
        ];

        $param = [
            'title'=>'您有会员发起'.$withdrawModel->type_name.'提现，请及时处理！',
            'content' => '时间:'.$withdrawModel->created_at.' || '
                .'提现编号:'.$withdrawModel->withdraw_sn.' || '
                .'会员:'.$withdrawModel->hasOneMember->nickname.' || '
                .'提现金额:'.$withdrawModel->amounts.' || '
//                .' 方式:'.$statusComment[$withdrawModel->pay_way_name],
                .'状态:'.$statusComment[$withdrawModel->status],
            'redirect_url' => 'withdraw',
            'redirect_param' => [
                'search[withdraw_sn]'=>$withdrawModel->withdraw_sn
            ]
        ];
        return $this->sendSysMsg($msg_type,$param);
    }

    //申请通知
    public function applyNotice($dataModel,$plugin = '')
    {
        $msg_type = 4;  //通知类型 1系统通知 2订单通知 3提现通知 4申请通知 5商品库存 6优惠券

        $param = $this->getParams($dataModel,$plugin);
        if(!$param){
            Log::error("申请通知--没有此插件类型通知");
            return false;
        }
        return $this->sendSysMsg($msg_type,$param);
    }

    //优惠券通知
    public function couponNotice($couponId)
    {
        //判断此次发送的优惠券是否剩余数量为0了，为0了说明有新的一种优惠券领取完成，执行发送消息
        $thisCoupon = Coupon::uniacid()
            ->withCount('hasManyMemberCoupon')
            ->where('id', '=', $couponId)
            ->first();
        if(empty($thisCoupon)){
            Log::error('优惠券系统消息通知----优惠券信息未找到');
            return false;
        }
        if($thisCoupon->plugin_id <> 0){
            //只计算商城优惠券，门店等的不计算
            return false;
        }

        if($thisCoupon['total'] == -1 ||$thisCoupon['total'] > $thisCoupon['has_many_member_coupon_count']){
            //该优惠券属于无限领取或者剩余数量不等于0
            return false;
        }

        //获取剩余数量为0的优惠券集合
        $couponData = Coupon::uniacid()
            ->withCount('hasManyMemberCoupon')
            ->where('total','>',-1)//非无限领取
            ->where('plugin_id',0)//商城优惠券
            ->get()->toArray();
        if(empty($couponData)){
            return false;
        }
        foreach ($couponData as $k=>$v){
            if($v['total'] >= $v['has_many_member_coupon_count']){
                unset($couponData[$k]);
            }
        }
        if(empty($couponData)){
            return false;
        }
        $msg_type = 6;
        $param = [
            'title'=>'您有'.count($couponData).'种优惠券数量为0，请及时处理！',
            'content' => '',
            'redirect_url' => 'coupon',
            'redirect_param' => [
                'last_stock'=>0
            ]
        ];
        return $this->sendSysMsg($msg_type,$param);
    }

    //限时购商品下架
    public function limitBuyClose($goods)
    {
        $msg_type = 5;
        $param = [
            'title'=>'您有限时购商品下架了，请及时处理！',
            'content' => '时间:'.date('Y-m-d H:i:s').' || '
                .'ID:'.$goods->id.' || '
                .'商品:'.$goods->title,
            'redirect_url' => 'goods',
            'redirect_param' => []
        ];
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $goods->uniacid;
        return $this->sendSysMsg($msg_type,$param);
    }

    //商品库存不足
    public function stockNotEnough($goods,$specs = null)
    {
        $redirect_url = [
            '0'=> 'goods_edit',
            '31'=>'store_goods_edit',
            '32'=>'cashier_goods_edit',
            '33'=>'hotel_goods_edit',
            '40'=>'lease_toy_goods_edit',
            '41'=>'net_car_goods_edit',
            '44'=>'jd_supply_goods_edit',
            '120'=>'yz_supply_goods_edit',
            '92'=>'supplier_goods_edit',
        ];
        $msg_type = 5;
        if(!empty($specs)){
            $content = '时间:'.date('Y-m-d H:i:s').' || '
                .'ID:'.$goods->id.' || '
                .'规格:'.$specs->title.' || '
                .'库存:'.$specs->stock;
        }else{
            $content = '时间:'.date('Y-m-d H:i:s').' || '
                .'ID:'.$goods->id.' || '
                .'库存:'.$goods->stock;
        }
        $param = [
            'title'=>'您有商品售罄，请及时处理！',
            'content' => $content,
            'redirect_url' => $redirect_url[$goods->plugin_id]?:'goods_edit',
            'redirect_param' => [
                'id'=>$goods->id
            ]
        ];
        return $this->sendSysMsg($msg_type,$param);
    }

    //申请通知获取各类型参数
    public function getParams($model,$plugin)
    {
        switch ($plugin){
            case 'supplier':
                $nickname = $this->getMemberNickname($model->member_id);
                return [
                    'title'=>'您有会员发起供应商申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.($model->status==1?'审核通过':'等待审核'),
                    'redirect_url' => 'supplier_apply',
                    'redirect_param' => [
                        'search[member]'=>$model->member_id
                    ]
                ];
            case 'area-dividend':
                $nickname = $this->getMemberNickname($model->member_id);
                return [
                    'title'=>'您有会员发起区域分红代理申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.$model->status_name,
                    'redirect_url' => 'area-dividend_apply',
                    'redirect_param' => [
                        'search[member]'=>$model->member_id
                    ]

                ];
            case 'merchant':
                $nickname = $this->getMemberNickname($model->member_id);
                return [
                    'title'=>'您有会员发起'.$model->apply_name.'申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.$model->status_name,
                    'redirect_url' => 'merchant_apply',
                    'redirect_param' => [
                        'search[is_center]'=>$model->is_center
                    ]
                ];
            case 'store-cashier':
                $nickname = $this->getMemberNickname($model->uid);
                return [
                    'title'=>'您有会员发起门店申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.$model->status_name,
                    'redirect_url' => 'store-cashier_apply',
                    'redirect_param' => [
                        'search[id]'=>$model->uid
                    ]
                ];
            case 'hotel':
                $nickname = $this->getMemberNickname($model->uid);
                return [
                    'title'=>'您有会员发起酒店申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.$model->status_name,
                    'redirect_url' => 'hotel_apply',
                    'redirect_param' => [
                        'search[id]'=>$model->uid
                    ]
                ];
            case 'package-deliver':
                $nickname = $this->getMemberNickname($model->uid);
                return [
                    'title'=>'您有会员发起自提点申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.$model->status_name,
                    'redirect_url' => 'package-deliver_apply',
                    'redirect_param' => [
                        'search[member]'=>$model->mobile
                    ]
                ];
            case 'provider-platform':
                $nickname = $this->getMemberNickname($model->uid);
                return [
                    'title'=>'您有会员发起第三方子平台入驻申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:审核中',
                    'redirect_url' => 'provider-platform_apply',
                    'redirect_param' => [
                        'search[name]'=>$model->mobile
                    ]
                ];
            case 'advert-market':
                $nickname = $this->getMemberNickname($model->uid);
                return [
                    'title'=>'您有会员发起广告主申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.($model->status==1?'审核通过':'待审核'),
                    'redirect_url' => 'advert-market_apply',
                    'redirect_param' => [
                        'search[uid]'=>$model->uid
                    ]
                ];
            case 'room':
                $nickname = $this->getMemberNickname($model->member_id);
                return [
                    'title'=>'您有会员发起主播申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.$model->status_name,
                    'redirect_url' => 'room_apply',
                    'redirect_param' => [
                        'search[member_id]'=>$model->member_id
                    ]
                ];
            case 'circle':
                $nickname = $this->getMemberNickname($model->member_id);
                return [
                    'title'=>'您有会员发起创建圈子申请，请及时处理！',
                    'content' => '时间:'.$model->created_at.' || 会员:'.$nickname.' || 状态:'.($model->is_review==1?'审核通过':'待审核'),
                    'redirect_url' => 'circle_apply',
                    'redirect_param' => [
                        'search[member_id]'=>$model->member_id
                    ]
                ];
            default:
                return false;
        }
    }

    public function getMemberNickname($member_id)
    {
        $nickname = \app\common\models\Member::where('uid', $member_id)->first()->nickname;
        if(empty($nickname)){
            return '未关注';
        }
        return $nickname;
    }

}