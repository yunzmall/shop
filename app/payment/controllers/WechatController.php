<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/28
 * Time: 上午6:50
 */

namespace app\payment\controllers;

use app\common\facades\EasyWeChat;
use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\Order;
use app\common\models\OrderPay;
use app\common\modules\wechat\models\WechatPayOrder;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\payment\PaymentController;


class WechatController extends PaymentController
{
    private $pay_type = ['JSAPI' => '微信', 'APP' => '微信APP'];

    private $attach = [];

    public function preAction()
    {
        parent::preAction();

        if (empty(\YunShop::app()->uniacid)) {
            $post = $this->getResponseResult();
            if (\YunShop::request()->attach) {
                \Setting::$uniqueAccountId = \YunShop::app()->uniacid = \YunShop::request()->attach;
            } else {
                $this->attach = explode(':', $post['attach']);
                \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->attach[0];
            }
            \Log::debug('---------attach数组--------', \YunShop::app()->uniacid);
            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }
    }

    public function notifyUrl()
    {
        $post = $this->getResponseResult();
        $this->log($post);

        $verify_result = $this->getSignResult($post);

        if ($verify_result) {



            //区分公众号、小程序、app支付
            if ($post['trade_type'] == 'JSAPI') {
                $pay_type_id = (isset($this->attach[1]) && $this->attach[1] == 'wechat') ? PayFactory::WECHAT_MIN_PAY : PayFactory::PAY_WEACHAT;
            } else {
                if (isset($this->attach[2]) && $this->attach[2] == PayFactory::WECHAT_CPS_APP_PAY){
                    $pay_type_id = PayFactory::WECHAT_CPS_APP_PAY;
                }else{
                    $pay_type_id = PayFactory::PAY_APP_WEACHAT;
                }
            }


            $data = [
                'total_fee'    => $post['total_fee'] ,
                'out_trade_no' => $post['out_trade_no'],
                'trade_no'     => $post['transaction_id'],
                'unit'         => 'fen',
                'pay_type'     => $this->pay_type[$post['trade_type']],
                'pay_type_id'     => $pay_type_id,
            ];

            $this->payResutl($data);
            echo "success";
        } else {
            echo "fail";
        }
    }

    public function jsapiNotifyUrl()
    {
        $post = $this->getResponseResult();
        $this->log($post);

        //todo 做签名验证
        $verify_result = true;

        if ($verify_result) {
            $data = [
                'total_fee'    => $post['total_fee'] ,
                'out_trade_no' => $post['out_trade_no'],
                'trade_no'     => $post['transaction_id'],
                'unit'         => 'fen',
                'pay_type'     => $this->pay_type[$post['trade_type']],
                'pay_type_id'     => PayFactory::WECHAT_JSAPI_PAY
            ];

            $orderPay = OrderPay::where('pay_sn', $data['out_trade_no'])->orderBy('id', 'desc')->first();
            $order = $orderPay->orders->first();
            $attach = explode(':', $post['attach']);
            $WechatPayOrder = [
                'uniacid' => \Yunshop::app()->uniacid,
                'order_id' => $order->id,
                'member_id' => $order->uid,
                'account_id' => $attach[3],
                'pay_sn' => $post['out_trade_no'],
                'transaction_id' => $post['transaction_id'],
                'total_fee' => $post['total_fee'],
                'profit_sharing' => $attach[2] == 'Y' ? 1:0,
            ];
            WechatPayOrder::create($WechatPayOrder);
            $this->payResutl($data);
            echo "success";
        } else {
            echo "fail";
        }
    }

    /**
     * @param $post
     * @return mixed
     */
    public function verifyH5Sign($post)
    {

        $pay = \Setting::get('shop.pay');

        /** @var $app Application  */
        $payment = $this->getEasyWeChatApp($pay);

        try {
            $message = (new \EasyWeChat\Payment\Notify\Paid($payment))->getMessage();
            return $message;
        } catch (\Exception $exception) {

            \Log::debug('微信签名验证：'.$exception->getMessage());
            return false;
        }

    }


    //微信h5支付
    public function notifyH5()
    {
        $post = $this->getResponseResult();
        $this->log($post);

        $verify_result = $this->verifyH5Sign($post);

        \Log::debug('微信H5支付回调验证结果', $verify_result);

        if ($verify_result) {
            $data = [
                'total_fee'    => $post['total_fee'] ,
                'out_trade_no' => $post['out_trade_no'],
                'trade_no'     => $post['transaction_id'],
                'unit'         => 'fen',
                'pay_type'     => '微信H5',
                'pay_type_id'     => PayFactory::WECHAT_H5,
            ];

            $this->payResutl($data);
            echo "success";
        } else {
            echo "fail";
        }
    }

    //微信NATIVE支付
    public function notifyPc()
    {
        $post = $this->getResponseResult();
        $this->log($post);

        $verify_result = $this->verifyH5Sign($post);

        \Log::debug('微信扫码支付回调验证结果', $verify_result);

        if ($verify_result) {
            $data = [
                'total_fee'    => $post['total_fee'] ,
                'out_trade_no' => $post['out_trade_no'],
                'trade_no'     => $post['transaction_id'],
                'unit'         => 'fen',
                'pay_type'     => '微信扫码支付',
                'pay_type_id'     => PayFactory::WECHAT_NATIVE,
            ];

            $this->payResutl($data);
            echo "success";
        } else {
            echo "fail";
        }
    }


    public function returnUrl()
    {

        if (\YunShop::request()->outtradeno) {
            $orderPay = OrderPay::where('pay_sn', \YunShop::request()->outtradeno)->first();
            if (is_null($orderPay)) {
                redirect(Url::absoluteApp('home'))->send();
            }


            //商品免单抽奖
            if (app('plugins')->isEnabled('free-lottery')) {
                $lotteryOrderCount = \Yunshop\FreeLottery\services\LotteryDrawService::isLotteryOrder($orderPay->order_ids);
                if ($lotteryOrderCount > 0) {
                    $redirect = yzAppFullUrl('FreeLottery',['i' => \YunShop::app()->uniacid,'order_ids'=>implode(",",$orderPay->order_ids)]);
                    redirect($redirect)->send();
                }
            }
            //优惠卷分享页
            $share_bool = \app\frontend\modules\coupon\services\ShareCouponService::showIndex($orderPay->order_ids, $orderPay->uid);
            if ($share_bool) {
                $ids = rtrim(implode('_', $orderPay->order_ids), '_');
                redirect(Url::absoluteApp('coupon/share/'.$ids, ['i' => \YunShop::app()->uniacid, 'mid'=> $orderPay->uid]))->send();
            }

            //预约商品订单支付成功后跳转预约插件设置的页面
            if (app('plugins')->isEnabled('appointment')) {
                \Log::debug('pay appointment order outtradeno：'.\YunShop::request()->outtradeno);
                $orders = Order::whereIn('id', $orderPay->order_ids)->get();
                // 只有一个订单
                \Log::debug('pay appointment order $orders：', $orders);
                if ($orders->count() == 1) {
                    $order = $orders[0];
                    // 是预约商品的订单
                    if ($order->plugin_id == 101) {
                        \Log::debug('pay appointment order $order->plugin_id：', $order->plugin_id);
                        $appointment_redirect = \Yunshop\Appointment\common\service\SetService::getPayReturnUrl();
                        \Log::debug('pay appointment order $appointment_redirect：', $appointment_redirect);
                        if ($appointment_redirect) {
                            redirect($appointment_redirect)->send();
                        }
                    }
                }
            }
        }

        $trade = \Setting::get('shop.trade');
        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
	        $redirect = $trade['redirect_url'];
	        preg_match("/^(http:\/\/)?([^\/]+)/i", $trade['redirect_url'], $matches);
	        $host = $matches[2];
	        // 从主机名中取得后面两段
	        preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
	        if ($matches){//判断域名是否一致
		        $redirect = $trade['redirect_url'].'&outtradeno='.\YunShop::request()->outtradeno;
	        }
	        redirect($redirect)->send();
        }

        redirect(Url::absoluteApp('home'))->send();
    }

    /**
     * 签名验证
     *
     * @return bool
     */
    public function getSignResult($post)
    {
        switch ($post['trade_type']) {
            case 'JSAPI':
                $pay = \Setting::get('shop.pay');

                if (isset($this->attach[1]) && $this->attach[1] == 'wechat') {
                    $min_set = \Setting::get('plugin.min_app');

                    $pay = [
                        'weixin_appid' => $min_set['key'],
                        'weixin_secret' => $min_set['secret'],
                        'weixin_mchid' => $min_set['mchid'],
                        'weixin_apisecret' => $min_set['api_secret'],
                        'weixin_cert'   => '',
                        'weixin_key'    => ''
                    ];
                }

                break;
            case 'APP' :

                if (isset($this->attach[2]) && $this->attach[2] == PayFactory::WECHAT_CPS_APP_PAY){
                    $pay = \Setting::get('plugin.aggregation-cps.pay_info');
                }else{
                    $pay = \Setting::get('shop_app.pay');
                }
                break;
        }


        $payment = $this->getEasyWeChatApp($pay);

        try {
            $message = (new \EasyWeChat\Payment\Notify\Paid($payment))->getMessage();
            return $message;
        } catch (\Exception $exception) {

            \Log::debug('微信签名验证：'.$exception->getMessage());
            return false;
        }

    }

    /**
     * 创建支付对象
     *
     * @param $pay
     * @return \EasyWeChat\Payment\Payment
     */
    public function getEasyWeChatApp($pay)
    {
        $options = [
            'app_id' => $pay['weixin_appid'],
            'secret' => $pay['weixin_secret'],
            'mch_id' => $pay['weixin_mchid'],
            'key' => $pay['weixin_apisecret'],
            'cert_path' => $pay['weixin_cert'],
            'key_path' => $pay['weixin_key']
        ];

        $app = EasyWeChat::payment($options);
        return $app;
    }

    /**
     * 获取回调结果
     *
     * @return array|mixed|\stdClass
     */
    public function getResponseResult()
    {
        $input = file_get_contents('php://input');
        if (!empty($input) && empty($_POST['out_trade_no'])) {
            //禁止引用外部xml实体
            $disableEntities = libxml_disable_entity_loader(true);

            $data = json_decode(json_encode(simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

            libxml_disable_entity_loader($disableEntities);

            if (empty($data)) {
                exit('fail');
            }
            if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
                exit('fail');
            }
            $post = $data;
        } else {
            $post = $_POST;
        }

        return $post;
    }

    /**
     * 支付日志
     *
     * @param $post
     */
    public function log($post)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($post['out_trade_no'], '微信支付', json_encode($post));
    }
}