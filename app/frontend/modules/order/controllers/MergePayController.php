<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/25
 * Time: 上午11:00
 */

namespace app\frontend\modules\order\controllers;

use app\common\components\ApiController;
use app\common\components\BaseController;
use app\common\events\order\AfterOrderPaidRedirectEvent;
use app\common\events\order\BeforeOrderPayEvent;
use app\common\events\payment\GetOrderPaymentTypeEvent;
use app\common\exceptions\AppException;
use app\common\exceptions\GoodsStockNotEnough;
use app\common\models\Order;
use app\common\models\OrderBehalfPayRecord;
use app\common\models\OrderPay;
use app\common\models\PayType;
use app\common\services\password\PasswordService;
use app\common\services\PayFactory;
use app\common\services\Session;
use app\frontend\models\Member;
use app\frontend\modules\order\OrderCollection;
use app\frontend\modules\order\services\OrderService;
use app\frontend\modules\orderPay\models\PreOrderPay;
use app\frontend\modules\payment\orderPayments\BasePayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use app\common\helpers\Url;
use Yunshop\StoreCashier\common\models\StoreOrder;


class MergePayController extends ApiController
{
    public $transactionActions = ['*'];
    /**
     * @var OrderCollection
     */
    protected $orders;
    protected $publicAction = ['alipay', 'alipayPayHj', 'yopAlipay', 'yopProAlipay', 'alipayScanPayHj', 'alipayJsapiPay', 'alipayToutiao', 'cloudAliPay', 'yunPayAlipay', 'wftAlipay'];
    protected $ignoreAction = ['alipay', 'alipayPayHj', 'yopAlipay', 'yopProAlipay', 'alipayScanPayHj', 'alipayJsapiPay', 'alipayToutiao', 'cloudAliPay', 'yunPayAlipay', 'wftAlipay'];


    /**
     * 获取支付按钮列表接口
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function index()
    {
        // 验证
        $this->validate([
            'order_ids' => 'required'
        ]);

        // 订单集合
        $orders = $this->orders(request()->input('order_ids'));


        // 用户余额
        $member = $orders->first()->belongsToMember()->select(['credit2'])->first()->toArray();

        // 生成支付记录 记录订单号,支付金额,用户,支付号
        $orderPay = new PreOrderPay();
        $orderPay->setOrders($orders);
        $orderPay->store();

        // 支付类型
        $buttons = $this->getPayTypeButtons($orderPay);

        //支付跳转
        $min_redirect_url = '';
        $trade = \Setting::get('shop.trade');
        if (!is_null($trade) && isset($trade['min_redirect_url']) && !empty($trade['min_redirect_url'])) {
            $min_redirect_url = $trade['min_redirect_url'];
        }

        $data = ['order_pay' => $orderPay, 'member' => $member, 'buttons' => $buttons, 'typename' => '', 'min_redirect_url' => $min_redirect_url];
        $order_contract = [];

        /*if (app('plugins')->isEnabled('shop-esign')) {
            if(count($orderPay['order_ids'])>0){
                $order_contract_list = (new \Yunshop\ShopEsign\common\models\SignOrder())->getContractByOrderIds($orderPay['order_ids']);
                if(!is_null($order_contract_list)){
                    $order_contract_list = $order_contract_list->toArray();
                    foreach($order_contract_list as $v){
                        $order_contract_tmp = [];
                        $order_contract_tmp['order_sn'] = $v['order_sn'];
                        $order_contract_tmp['contract_id'] = 0;
                        $order_contract_tmp['need_sign'] = 0;
                        if($v['has_one_contract']['id']){
                            $order_contract_tmp['contract_id'] = $v['has_one_contract']['id'];
                        }
                        if($v['has_one_contract']['status']==0){
                            $order_contract_tmp['need_sign'] = 1;
                        }
                        array_push($order_contract,$order_contract_tmp);
                    }
                }

            }

        }*/
        $data['order_contract'] = $order_contract;
        return $this->successJson('成功', $data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function anotherPayOrder()
    {
        $this->validate([
            'order_ids' => 'required',
            'pid'       => 'required'
        ]);

        // 订单集合
        $orders = $this->orders(request()->input('order_ids'));

        // 生成支付记录 记录订单号,支付金额,用户,支付号
        $orderPay = new PreOrderPay();
        $orderPay->setOrders($orders);
        $orderPay->store();

        // 支付类型
        $buttons = $this->getPayTypeButtons($orderPay);

        // todo bad taste
        $type = \YunShop::request()->type ?: 0;
        $buttons = collect($buttons)->filter(function ($value, $key) use ($type) {
            if ($value['name'] != '找人代付') {
                return $value;
            }
        });

        $member = Member::getMemberById(request()->input('pid'));
        //添加代付记录
        if (\YunShop::app()->getMemberId() != $orderPay->uid) {
            OrderBehalfPayRecord::create([
                'uniacid'      => \YunShop::app()->uniacid,
                'order_ids'    => $orderPay->order_ids,
                'order_pay_id' => $orderPay->id,
                'pay_sn'       => $orderPay->pay_sn,
                'member_id'    => $orderPay->uid,
                'behalf_type'  => $this->behalfType(),
                'behalf_id'    => \YunShop::app()->getMemberId()
            ]);
        }
        $data = ['order_pay' => $orderPay, 'member' => $member, 'buttons' => $buttons, 'typename' => ''];

        $data['plugin_name'] = app('plugins')->isEnabled('parent-payment') ? PARENT_PAYMENT : '上级代付';

        return $this->successJson('成功', $data);
    }

    private function behalfType()
    {
        return request()->input('plugin') == 'parent_payment' ? 2 : 1;
    }

    /**
     * 支付的时候,生成支付记录的时候,通过订单ids获取订单集合
     * @param $orderIds
     * @return OrderCollection
     * @throws AppException
     */
    private function orders($orderIds)
    {
        if (!is_array($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }
        array_walk($orderIds, function ($orderId) {
            if (!is_numeric($orderId)) {
                throw new AppException('(ID:' . $orderId . ')订单号id必须为数字');
            }
        });

        $this->orders = OrderCollection::make(Order::select(['status', 'id', 'order_sn', 'price', 'uid', 'plugin_id'])->whereIn('id', $orderIds)->get());

        if ($this->orders->count() != count($orderIds)) {
            throw new AppException('(ID:' . implode(',', $orderIds) . ')未找到订单');
        }
        $this->orders->load('orderGoods');

        return $this->orders;
    }

    /**
     * 通过事件获取支付按钮
     * @param \app\frontend\models\OrderPay $orderPay
     * @return Collection
     */
    private function getPayTypeButtons(\app\frontend\models\OrderPay $orderPay)
    {
        // 获取可用的支付方式
        $result = $orderPay->getPaymentTypes()->map(function (BasePayment $paymentType) {

            //余额
            if ($paymentType->getCode() == 'balance') {
                if ($paymentType->getName() !== \Setting::get('shop.shop.credit')) {
                    $names = \Setting::get('shop.shop.credit');
                }
            }
            //预存款
            if ($paymentType->getCode() == 'DepositPay') {
                if (app('plugins')->isEnabled('team-rewards')) {
                    $names = TEAM_REWARDS_DEPOSIT . '支付';
                }
            }
            //上级代付
            if ($paymentType->getCode() == 'parentPayment') {
                if (app('plugins')->isEnabled('parent-payment')) {
                    $names = PARENT_PAYMENT;
                }
            }
            return [
                'name'          => $names ?: $paymentType->getName(),
                'value'         => $paymentType->getId(),
                'need_password' => $paymentType->needPassword(),
                'code'          => $paymentType->getCode(),
            ];
        });
        return $result;
    }

    /**
     * 微信支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function wechatPay()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);
        if (\Setting::get('shop.pay.weixin') == false) {
            throw new AppException('商城未开启微信支付');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));


        //支付类型 小程序、公众号
        if (request()->input('type') == 2 || \YunShop::request()->app_type == 'wechat') {
            $payTypeId = PayFactory::WECHAT_MIN_PAY;
        } else {
            $payTypeId = PayFactory::PAY_WEACHAT;
        }


        $data = $orderPay->getPayResult($payTypeId);
        $data['js'] = json_decode($data['js'], 1);

        $trade = \Setting::get('shop.trade');
        $redirect = $admin_set_redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $redirect = $trade['redirect_url'] . '&outtradeno=' . request()->input('order_pay_id');
        }
        //跳转页面
		$orders = Order::whereIn('id', $orderPay->order_ids)->get();
		event($event = new AfterOrderPaidRedirectEvent($orders,$orderPay->id));
		$data['redirect'] = $event->getData()['redirect']?:$redirect;
        return $this->successJson('成功', $data);
    }

    /**
     * 支付宝支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function alipay()
    {
        if (\Setting::get('shop.pay.alipay') == false) {
            throw new AppException('商城未开启支付宝支付');
        }
        if (request()->has('uid')) {
            Session::set('member_id', request()->query('uid'));
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_ALIPAY);


        return $this->successJson('成功', $data);
    }

    /**
     * 微信app支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatAppPay()
    {
        if (\Setting::get('shop_app.pay.weixin') == false) {
            throw new AppException('商城未开启微信支付');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_APP_WEACHAT);
        return $this->successJson('成功', $data);
    }

    /**
     * 微信聚合CPSAPP支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function wechatCpsAppPay()
    {

        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);

        if (!app('plugins')->isEnabled('aggregation-cps')){
            throw new AppException('聚合CPS插件未开启');
        }

        $setting = \Setting::get('plugin.aggregation-cps.pay_info');
        if (!$setting['weixin_pay']){
            throw new AppException('聚合CPS未开启微信支付');
        }

        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::WECHAT_CPS_APP_PAY);

        $arr = [
            'appid'=>$data['config']['appId'],
            'partnerid'=>$data['config']['partnerid'],
            'prepayid'=>$data['config']['prepayId'],
            'package'=>'Sign=WXPay',
            'noncestr'=>$data['config']['nonceStr'],
            'timestamp'=>$data['config']['timestamp'],
        ];

        ksort($arr);
        $str  = '';
        foreach ($arr as $k=>$v){
            $str .=$k.'='.$v.'&';
        }
        $str .='key='.$setting['weixin_apisecret'];
        $data['config']['paySign'] = strtoupper(md5($str));
        $data['redirect_url'] = \Setting::get('shop.trade.redirect_url') ? : '';

        return $this->successJson('成功', $data);

    }


    /**
     * 支付宝app支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function alipayAppRay()
    {
        if (\Setting::get('shop_app.pay.alipay') == false) {
            throw new AppException('商城未开启支付宝支付');
        }
        if (request()->has('uid')) {
            Session::set('member_id', request()->query('uid'));
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data['payurl'] = $orderPay->getPayResult(PayFactory::PAY_APP_ALIPAY);
        $data['order_sn'] = \YunShop::app()->uniacid . '_' . $orderPay->pay_sn;
        $data['isnewalipay'] = \Setting::get('shop_app.pay.newalipay');
        return $this->successJson('成功', $data);
    }

    /**
     * 微信云支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function cloudWechatPay()
    {
        if (\Setting::get('plugin.cloud_pay_set') == false) {
            throw new AppException('商城未开启微信支付');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_CLOUD_WEACHAT);
        return $this->successJson('成功', $data);
    }

    /**
     * 芸支付
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function yunPayWechat()
    {
        if (\Setting::get('plugin.yun_pay_set') == false) {
            throw new AppException('商城未开启芸支付');
        }

        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_YUN_WEACHAT);
        return $this->successJson('成功', $data);
    }

    /**
     * 支付宝云支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function cloudAliPay()
    {
        if (\Setting::get('plugin.cloud_pay_set') == false) {
            throw new AppException('商城未开启支付宝支付');
        }

        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_CLOUD_ALIPAY, ['pay' => 'cloud_alipay']);
        return $this->successJson('成功', $data);
    }

    /**
     * 找人代付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function anotherPay()
    {
        if (\Setting::get('another_pay_set') == false) {
            throw new AppException('商城未开启支付宝支付');
        }

        return $this->successJson('成功', []);
    }


    /**
     * 支付宝—YZ
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function yunPayAlipay()
    {
        if (\Setting::get('plugin.yun_pay_set') == false) {
            throw new AppException('商城未开启芸支付');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_YUN_WEACHAT, ['pay' => 'alipay']);
        return $this->successJson('成功', $data);
    }

    /**
     * 货到付款
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function COD()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);
        if (\Setting::get('shop.pay.COD') == false) {
            throw new AppException('商城未开启货到付款');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */

        $trade = \Setting::get('shop.trade');
        $redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $redirect = $trade['redirect_url'];
        }


        if (app('plugins')->isEnabled('consumer-reward')) {
            if (request()->type == 2) {//小程序
                //跳转小程序 携带参数is_show_charity_fund_poster
                $redirect = '/packageH/consumerReward/consumerRewardPaySuccess/consumerRewardPaySuccess?pay_id=' . request()->input('order_pay_id');
            } else {//公众号
                $redirect = yzAppFullUrl('consumerRewardPaySuccess') . '&pay_id=' . request()->input('order_pay_id');
            }

        }


        if (!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('plugin_delivery_pay_function'))) {
            foreach ($event_arr as $v) {
                $class = array_get($v, 'class');
                $function = array_get($v, 'function');
                $res = $class::$function(request()->input('order_pay_id'));
                if (!$res['result']) {
                    throw new AppException($res['msg']);
                }
                if ($res['result'] && $res['data']['process'] == 'break') {
                    return $this->successJson('成功', ['redirect' => $redirect]);
                }
            }
        }


        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $orderPay->getPayResult(PayFactory::PAY_COD);
        $orderPay->pay();


        return $this->successJson('成功', ['redirect' => $redirect]);
    }

    /**
     * 货到付款
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function remittance()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);

        if (\Setting::get('shop.pay.remittance') == false) {
            throw new AppException('商城未开启转账付款');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayType::REMITTANCE);

        $orderPay->applyPay();

        $orderPay->save();
        $trade = \Setting::get('shop.trade');
        $redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $redirect = $trade['redirect_url'];
        }
        $data['redirect'] = $redirect;
        return $this->successJson('成功', $data);
    }

    /**
     * 环迅快捷支付
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function huanxunQuick()
    {
        if (\Setting::get('plugin.huanxun_set') == false) {
            throw new AppException('商城未开启快捷支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_Huanxun_Quick, ['pay' => 'quick']);

        return $this->successJson('成功', $data);
    }

    /**
     * 威富通公众号支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wftWechat()
    {

        if (\Setting::get('plugin.wft_pay') == false) {
            throw new AppException('商城未开启威富通公众号支付');
        }
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::WFT_PAY);

        return $this->successJson('成功', $data);
    }

    /**
     * 威富通支付宝支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wftAlipay()
    {

        if (\Setting::get('plugin.wft_alipay') == false) {
            throw new AppException('商城未开启威富通公众号支付');
        }
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::WFT_ALIPAY);

        return $this->successJson('成功', $data);
    }

    /**
     * 环迅微信支付
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function huanxunWx()
    {
        if (\Setting::get('plugin.dian_bang_scan_set') == false) {
            throw new AppException('商城未开启快捷支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_Huanxun_Wx, ['pay' => 'wx']);

        return $this->successJson('成功', $data);
    }

    /**
     * 店帮支付
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function dianBangScan()
    {
        if (\Setting::get('plugin.dian-bang-scan') == false) {
            throw new AppException('商城未开启店帮扫码支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_DIANBANG, ['pay' => 'scan']);

        return $this->successJson('成功', $data);
    }

    public function yopPay()
    {
        if (!app('plugins')->isEnabled('yop-pay')) {
            throw new AppException('商城未开启易宝支付未开启');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::YOP);

        return $this->successJson('成功', $data);
    }

    public function yopAlipay()
    {
        if (!app('plugins')->isEnabled('yop-pay')) {
            throw new AppException('商城未开启易宝支付未开启');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::YOP_ALIPAY, ['pay_type' => 'yop_alipay']);

        return $this->successJson('成功', $data);
    }

    //易宝专业版
    public function yopProWechat()
    {
        if (!app('plugins')->isEnabled('yop-pro')) {
            throw new AppException('商城未开启易宝支付未开启');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::YOP_PRO_WECHAT);

        return $this->successJson('成功', $data);
    }

    //易宝专业版
    public function yopProAlipay()
    {
        if (!app('plugins')->isEnabled('yop-pro')) {
            throw new AppException('商城未开启易宝支付未开启');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::YOP_PRO_ALIPAY, ['pay_type' => 'alipay']);

        return $this->successJson('成功', $data);
    }

    /**
     * Usdt支付
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function usdtPay()
    {
        if (\Setting::get('plugin.usdtpay_set') == false) {
            throw new AppException('商城未开启Usdt支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_Usdt);

        return $this->successJson('成功', $data);
    }

    /**
     * 微信支付-HJ
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatPayHj()
    {
        if (\Setting::get('plugin.convergePay_set.wechat') == false && !app('plugins')->isEnabled('converge_pay')) {
            throw new AppException('商城未开启微信支付-HJ');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_WECHAT_HJ);

        if ($data['msg'] == '成功') {

            $trade = \Setting::get('shop.trade');

            //跳转页面
            $orders = Order::whereIn('id', $orderPay->order_ids)->get();
            event($event = new AfterOrderPaidRedirectEvent($orders,$orderPay->id));
            $data['redirect_url'] = $event->getData()['redirect']?:$trade['redirect_url'];

            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }


    }

    /**
     * 支付宝支付-HJ
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function alipayPayHj()
    {
        if (\Setting::get('plugin.convergePay_set.alipay') == false && !app('plugins')->isEnabled('converge_pay')) {
            throw new AppException('商城未开启支付宝支付-HJ');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_ALIPAY_HJ);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 微信扫码支付-HJ
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatScanPayHj()
    {
        if (\Setting::get('plugin.convergePay_set.wechat') == false && !app('plugins')->isEnabled('converge_pay')) {
            throw new AppException('商城未开启微信支付-HJ');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_WECHAT_SCAN_HJ);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 支付宝扫码支付-HJ
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function alipayScanPayHj()
    {
        if (\Setting::get('plugin.convergePay_set.alipay') == false && !app('plugins')->isEnabled('converge_pay')) {
            throw new AppException('商城未开启支付宝支付-HJ');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_ALIPAY_SCAN_HJ);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 微信支付-juqi
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatPayJueqi()
    {
        if (\Setting::get('plugin.jueqi_pay_set.switch') == false && !app('plugins')->isEnabled('jueqi_pay')) {
            throw new AppException('商城未开启崛企支付');
        }
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_WECHAT_JUEQI);

        return $this->successJson('ok', $data);
    }

    /**
     * 为农 电子钱包-余额支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function lcgBalance()
    {
        if (!app('plugins')->isEnabled('dragon-deposit') && \Setting::get('plugin.dragon_deposit.lcgBalance') == '1') {
            throw new AppException('商城未开启钱包支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayFactory::LCG_BALANCE, ['pay_type' => 'lcgBalance']);

        return $this->successJson('ok', $data);
    }

    /**
     * 为农 电子钱包-绑定卡支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function lcgBankCard()
    {
        if (!app('plugins')->isEnabled('dragon-deposit') && \Setting::get('plugin.dragon_deposit.lcgBankCard') == '1') {
            throw new AppException('商城未开启钱包绑卡支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayFactory::LCG_BANK_CARD, ['pay_type' => 'lcgBankCard']);

        return $this->successJson('ok', $data);
    }

    /**
     * 微信扫码支付
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatScanPay()
    {
        //验证开启

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::WECHAT_SCAN_PAY);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 微信人脸支付
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatFacePay()
    {
        //验证开启

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::WECHAT_FACE_PAY);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 微信JSAPI支付
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function wechatJsapiPay()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);

        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        if (app('plugins')->isEnabled('store-cashier')) {
            $store_id = StoreOrder::where('order_id', $orderPay->orders->first()->id)->value('store_id');
            request()->offsetSet('store_id', $store_id);
        }
        $data = $orderPay->getPayResult(PayFactory::WECHAT_JSAPI_PAY);
//        $data['js'] = json_decode($data['js'], 1);

        $trade = \Setting::get('shop.trade');
        $redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $redirect = $trade['redirect_url'] . '&outtradeno=' . request()->input('order_pay_id');
        }

        $data['redirect'] = $redirect;

        return $this->successJson('成功', $data);
    }


    /**
     * 支付宝支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function alipayJsapiPay()
    {
        if (\Setting::get('shop.alipay_set') == false) {
            throw new AppException('商城未开启支付宝支付');
        }
        if (request()->has('uid')) {
            Session::set('member_id', request()->query('uid'));
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        if (app('plugins')->isEnabled('store-cashier')) {
            $store_id = StoreOrder::where('order_id', $orderPay->orders->first()->id)->value('store_id');
            request()->offsetSet('store_id', $store_id);
        }

        $data = $orderPay->getPayResult(PayFactory::ALIPAY_JSAPI_PAY);

        return $this->successJson('成功', $data);
    }

    public function wechatPayToutiao()
    {
        if (\Setting::get('shop.pay.weixin') == false) {
            throw new AppException('商城未开启微信支付');
        }
        if (\Setting::get('plugin.toutiao-mini.wx_switch') != 1 && !app('plugins')->isEnabled('toutiao-mini')) {
            throw new AppException('商城未开启微信支付(头条支付)');
        }
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_WECHAT_TOUTIAO);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }


    public function alipayToutiao()
    {
        if (\Setting::get('shop.pay.alipay') == false) {
            throw new AppException('商城未开启支付宝支付');
        }
        if (\Setting::get('plugin.toutiao-mini.alipay_switch') != 1 && !app('plugins')->isEnabled('toutiao-mini')) {
            throw new AppException('商城未开启支付宝支付(头条支付)');
        }
        if (request()->has('uid')) {
            Session::set('member_id', request()->query('uid'));
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::PAY_ALIPAY_TOUTIAO);
        return $this->successJson('成功', $data);
    }

    public function membercardpay()
    {
        if (\Setting::get('plugin.pet.is_open_pet') != 1) {
            throw new AppException('商城未开启会员卡支付(宠物医院会员卡支付)');
        }
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::MEMBER_CARD_PAY);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 微信香港扫码支付
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function hkScanPay()
    {
        //验证开启

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::HK_SCAN_PAY);

        return $this->successJson('成功', $data);

    }

    public function payPal()
    {

        if (!app('plugins')->isEnabled('pay-pal') && \Setting::get('plugin.pay_pal.is_open') == '1') {
            throw new AppException('商城未开启PayPal支付');
        }
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayFactory::PAY_PAL);

        if ($data['code'] == 500) {
            return $this->errorJson($data['msg'], $data['data']);
        }

        return $this->successJson('ok', $data['data']);

    }


    /**
     * 汇聚快捷支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function conbergeQuickPay()
    {
        if (!app('plugins')->isEnabled('converge_pay')) {
            throw new AppException('商城未开启汇聚支付插件');
        }

        $card_no = request()->input('card_no');
        if (empty($card_no)) {
            throw new AppException('请选择支付银行卡');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));


        $data = $orderPay->getPayResult(PayFactory::CONVERGE_QUICK_PAY, ['card_no' => $card_no]);

        return $this->successJson('ok', $data);
    }

    /**
     * 香港支付宝H5支付
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function hkScanAlipay()
    {
        //验证开启

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::HK_SCAN_ALIPAY);

        return $this->successJson('成功', $data);

    }


    /**
     * 确认支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function confirmPay()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);

        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $orderPay->getPayResult(PayFactory::CONFIRM_PAY);
        $orderPay->pay();
        $trade = \Setting::get('shop.trade');
        $redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $redirect = $trade['redirect_url'];
        }

        // 盲盒订单支付成功后跳转盲盒订单详情页
        if (app('plugins')->isEnabled('blind-box')) {
            $orders = Order::whereIn('id', $orderPay->order_ids)->get();
            // 只有一个订单
            if ($orders->count() == 1) {
                $order = $orders[0];
                // 是拼团的订单
                if ($order->plugin_id == 107) {
                    $redirect = Url::absoluteApp('member/orderdetail/' . $order->id . '/shop/', ['i' => \YunShop::app()->uniacid]);
                }
            }
        }

        return $this->successJson('成功', ['redirect' => $redirect]);
    }


    /**
     * 微信H5支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function wechatH5()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);
        if (\Setting::get('shop.pay.weixin') == false) {
            throw new AppException('商城未开启微信支付');
        }
        /**
         * @var $orderPay \app\frontend\models\OrderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayFactory::WECHAT_H5);


        return $this->successJson('成功', $data);
    }

    /**
     * 微信H5支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\ShopException
     */
    public function wechatNative()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);
        if (\Setting::get('shop.pay.weixin') == false) {
            throw new AppException('商城未开启微信支付');
        }
        /**
         * @var $orderPay \app\frontend\models\OrderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayFactory::WECHAT_NATIVE);


        return $this->successJson('成功', $data);
    }

    /**
     * Dcm扫码支付
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \Exception
     */
    public function dcmScanPay()
    {
        $this->validate([
            'order_pay_id' => 'required|integer'
        ]);

        if (\Setting::get('plugin.dcm-scan-pay.switch') == false) {
            throw new AppException('未开启该付款方式');
        }
        /**
         * @var \app\frontend\models\OrderPay $orderPay
         */
        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));

        $data = $orderPay->getPayResult(PayType::DCM_SCAN_PAY);


        $trade = \Setting::get('shop.trade');
        $redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $redirect = $trade['redirect_url'];
        }
        $data['redirect'] = $redirect;
        return $this->successJson('成功', $data);
    }

    /**
     * 商云客支付-支付宝
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function xfpayAlipay()
    {
        if (\Setting::get('plugin.xfpay_set.xfpay.pay_type.alipay.enabled') == false && !app('plugins')->isEnabled('xfpay')) {
            throw new AppException('商城未开启支付宝支付-商云客聚合支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::XFPAY_ALIPAY);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }

    /**
     * 商云客支付-微信
     *
     * @param \Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function xfpayWechat()
    {
        if (\Setting::get('plugin.xfpay_set.xfpay.pay_type.wechat.enabled') == false && !app('plugins')->isEnabled('xfpay')) {
            throw new AppException('商城未开启支付宝支付-商云客聚合支付');
        }

        $orderPay = \app\frontend\models\OrderPay::find(request()->input('order_pay_id'));
        $data = $orderPay->getPayResult(PayFactory::XFPAY_WECHAT);

        if ($data['msg'] == '成功') {
            return $this->successJson($data['msg'], $data);
        } else {
            return $this->errorJson($data['msg']);
        }
    }
}