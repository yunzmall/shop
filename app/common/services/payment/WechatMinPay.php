<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/27
 * Time: 11:08
 */

namespace app\common\services\payment;


use app\common\exceptions\AppException;
use app\common\facades\EasyWeChat;
use app\common\helpers\Url;
use app\common\models\Member;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\models\WechatMinAppPayOrder;
use app\common\services\Pay;
use app\common\services\Utils;
use app\common\services\WechatPay;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use app\common\models\PayType;

class WechatMinPay extends Pay
{

    public $minSet;

    /**
     * WechatMinPay constructor.
     * @throws AppException
     */
    public function __construct()
    {
        $this->minSet = \Setting::get('plugin.min_app');

        if (!$this->minSet['secret'] || !$this->minSet['key'] || !$this->minSet['mchid']) {
            throw new AppException('小程序支付配置未设置');
        }

    }

    //发送获取token请求,获取token(有效期2小时)
    public function getToken()
    {

        // todo 不能做缓存如果其他地方调用了重新生成access_token，则这个旧的就没用了
//        $cacheKey = $this->minSet['key'].'_token_'.\YunShop::app()->uniacid;
//
//        $cache_access_token = Redis::get($cacheKey);
//        if ($cache_access_token) {
//            return $cache_access_token;
//        }

        $paramMap = [
            'grant_type' => 'client_credential',
            'appid' => $this->minSet['key'],
            'secret' => $this->minSet['secret'],
        ];
        //获取token的url参数拼接
        $strQuery="";
        foreach ($paramMap as $k=>$v){
            $strQuery .= strlen($strQuery) == 0 ? "" : "&";
            $strQuery.=$k."=".urlencode($v);
        }

        $getTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?". $strQuery; //获取token的url

        $client = new Client;
        $res = $client->request('GET', $getTokenUrl);

        $data = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        if ($data['errcode']) {
            \Log::debug('===小程序支付管理获取token失败====='. self::class, $data);
            return false;
        }

//        if ($data['access_token']) {
//            Redis::setex($cacheKey, 600, $data['access_token']);
//        }

        return $data['access_token'];
    }


    /**
     * 请求接口-查询订单详情
     * @param $trade_no
     * @return mixed
     * @throws AppException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function selectOrder($trade_no)
    {

        $access_token = $this->getToken();

        if (!$access_token) {
            throw new AppException('小程序获取access_token失败');
        }

        $url = 'https://api.weixin.qq.com/shop/pay/getorder?access_token='.$access_token;

        $client = new Client();
        $res = $client->request('POST', $url, ['json'=>['trade_no' => $trade_no]]);

        $result = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        if ($result['errcode']) {
            \Log::debug('小程序支付管理支付查询订单失败', $result);
            throw new AppException($result['errmsg']);
        }

        return $result['order'];
    }

    public function divideAccount($payOrder, $cacheToken = false)
    {

        //缓存 token
        if ($cacheToken) {
            $cacheKey = $this->minSet['key'].'_token_'.\YunShop::app()->uniacid;
            $cache_access_token = Redis::get($cacheKey);
            if (!$cache_access_token) {
                $access_token = $this->getToken();
                Redis::setex($cacheKey, 600, $access_token);
            } else {
                $access_token = $cache_access_token;
            }
        } else {
            $access_token = $this->getToken();
        }

        if (!$access_token) {
            return [ "errcode" => 99999, "errmsg" => "小程序获取access_token失败"];
        }


        //默认用支付单号做分账单号
        if (!$payOrder->divide_no) {
            $payOrder->divide_no = $payOrder->trade_no;
        }

        $parameter = [
            'openid' => $payOrder->openid,
            'mchid' => $this->minSet['mchid'],
            'trade_no' => $payOrder->trade_no,
            'transaction_id' => $payOrder->transaction_id,
            'profit_sharing_no' => $payOrder->divide_no,
        ];

        $url = 'https://api.weixin.qq.com/shop/pay/profitsharingorder?access_token='.$access_token;

        $result = $this->curlRequest($url, json_encode($parameter, JSON_UNESCAPED_UNICODE));
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }

        return $result;
    }

    public function newPay($data)
    {
        $text = $data['extra']['type'] == 1 ? '支付' : '充值';
        $op = '微信小程序' . $text . ' 订单号：' . $data['order_no'];

        $pay_order_model = $this->log($data['extra']['type'], '微信小程序new', $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

        if (empty(\YunShop::app()->getMemberId())) {
            throw new AppException('无法获取用户ID');
        }

        $strName = preg_replace("/[^\x{4e00}-\x{9fa5}a-zA-Z0-9\.]/u",'',$data['subject']);

        
        $parameter = [
            'openid' => $this->getMinOpenId(\YunShop::app()->getMemberId()),
//            'combine_trade_no' => \app\common\services\CreateRandomNumber::randomNumber('CXC'),
            'combine_trade_no' => 'CXC'.substr($data['order_no'], 2,17),
            'expire_time' => time() + (1 * 60 * 60),
            'sub_orders' => [
                [
                    'mchid' => $this->minSet['mchid'],
                    'amount' => $data['amount'] * 100, // 单位：分
                    'trade_no' => $data['order_no'],
                    'description' => $this->cutStr($strName,50),
                ],
            ],
        ];

        //请求数据日志
        self::payRequestDataLog($data['order_no'], $pay_order_model->type,
            $pay_order_model->third_type, json_encode($parameter));


        $access_token = $this->getToken();

        if (!$access_token) {
            throw new AppException('小程序获取access_token失败');
        }


        $url = 'https://api.weixin.qq.com/shop/pay/createorder?access_token='.$access_token;


        $result = $this->curlRequest($url, json_encode($parameter, JSON_UNESCAPED_UNICODE));
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }

//        $client = new Client();
//        $res = $client->request('POST', $url, ['json'=>$parameter]);
//
//        $result = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        if ($result['errcode']) {
            \Log::debug('小程序支付管理支付请求失败', $result);
            \Log::debug('小程序支付管理支付请求失败', $parameter);
            throw new AppException($result['errmsg']);
        }


        $this->savePayOrder($data['order_no'], $parameter['openid']);

        $config = $result['payment_params'];
        $config['appId'] = $this->minSet['key'];
        $config['timestamp'] = $config['timeStamp'];
        $config['is_pay_manager'] = 1;
//        \Log::debug('小程序支付管理支付', $config);
        return $config;
    }

    /**
     * 垃圾微信，需要在这里先保存用户的openid因为分账时需要openid
     * @param $trade_no
     * @param $openid
     */
    public function savePayOrder($trade_no,$openid)
    {
        $payRecord = WechatMinAppPayOrder::existOrNew($trade_no);

        $payRecord->fill(['trade_no' => $trade_no, 'openid' => $openid]);

        return $payRecord->save();
    }

    /**
     * @param $data
     * @return array|mixed
     * @throws AppException
     */
    public function doPay($data)
    {

        //小程序没有被灰度的走微信统一下单接口，
        if (!$this->minSet['is_pay_manager']) {
            $config = $this->oldPay($data);
        } else {
            $config = $this->newPay($data);

        }
        //视频号场景下的直接走原来的微信支付方法
        if (app('plugins')->isEnabled('wechat-trade') && isset(request()->wechat_trade_order_type)) {
            \Log::debug('------小程序视频号支付-------');
            $config = $this->wechatTradePay($config);
        }

        return ['config'=>$config];
    }

    //小程序自定义版交易组件及开放接口
    //文档 https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/ministore/minishopopencomponent2/API/order/add_order_new.html
    //暂时写到这里，因为不知道需要什么参数
    //优化需要把这个写到插件里
    public function wechatTradePay($config)
    {
        $order_pay = OrderPay::find(request()->order_pay_id);
        $trade_data = [
            'pay_type' => 0,
            'prepay_time' => $config['timestamp'],
            'prepay_id' => $config['prepayId'],
            'pay_sn' => $order_pay->pay_sn,
            'scene' => request()->senceKey,
            'order_type' => request()->wechat_trade_order_type,
        ];
        $wechat_trade_order_info = (new \Yunshop\WechatTrade\services\AddOrderService($trade_data))->handle();
        if (!$wechat_trade_order_info['result']) {
            throw new AppException($wechat_trade_order_info['msg']);
        }
        if (request()->wechat_trade_order_type == 1) {
            foreach ($wechat_trade_order_info['data']['payment_params'] as $k => $v) {
                $config[$k] = $v;
            }
        } else {
            $config['order_info'] = $wechat_trade_order_info['data']['data'];
        }

        return $config;
    }

    public function oldPay($data)
    {

        if (empty(\YunShop::app()->getMemberId())) {
            throw new AppException('无法获取用户ID');
        }


        $text = $data['extra']['type'] == 1 ? '支付' : '充值';
        $op = '微信小程序' . $text . ' 订单号：' . $data['order_no'];

        $pay_order_model = $this->log($data['extra']['type'], '微信小程序old', $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());


        $openId = $this->getMinOpenId(\YunShop::app()->getMemberId());



        $attributes = [
            'trade_type'       => 'JSAPI', //小程序微信支付
            'body'             => mb_substr($data['subject'], 0, 40),
            'out_trade_no'     => $data['order_no'],
            'total_fee'        => $data['amount'] * 100, // 单位：分
            'nonce_str'        => \app\common\helpers\Client::random(8) . "",
            'device_info'      => 'yun_shop',
            'attach'           => \YunShop::app()->uniacid . ':wechat:2',
            'spbill_create_ip' => self::getClientIP(),
            'openid'           => $openId,
        ];

        //请求数据日志
        self::payRequestDataLog($attributes['out_trade_no'], $pay_order_model->type,
            $pay_order_model->third_type, json_encode($attributes));



        $notify_url = Url::shopSchemeUrl('payment/wechat/notifyUrl.php');
        $payment = $this->getEasyWeChatApp($notify_url);

        $result = $payment->order->unify($attributes);

        \Log::debug('--微信支付小程序预下单--', $result);
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {

            $prepayId = $result['prepay_id'];
            $pay_order_model->fill(['status'=> Pay::ORDER_STATUS_WAITPAY])->save();

        } elseif ($result['return_code'] == 'SUCCESS') {

            throw new AppException($result['err_code_des']);
        } else {

            throw new AppException($result['return_msg']);
        }
        $config = $payment->jssdk->sdkConfig($prepayId);

        $config['prepayId'] = $prepayId;

        return $config;
    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {

        if (!$this->minSet['is_pay_manager']) {
            return $this->oldRefund($out_trade_no, $totalmoney, $refundmoney);
        }

        
        //退款单号不能超过20位
        $unique = substr(uniqid(), strlen(\YunShop::app()->uniacid) + 3);
        $out_refund_no =  date('Ymd', time()) . \YunShop::app()->uniacid . $unique;

//        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);

        $op = '微信退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款金额：' . $refundmoney;

        if (empty($out_trade_no)) {
            throw new AppException('参数错误');
        }

        $pay_order_model = $this->refundlog(Pay::PAY_TYPE_REFUND, '微信小程序支付管理退款', $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);

        $member_id = OrderPay::select('uid')->where('pay_sn', $out_trade_no)->value('uid');

        $transaction_id = PayOrder::select('trade_no')
            ->where('out_order_no', $out_trade_no)
            ->where('trade_no', '!=', '0')
            ->value('trade_no');

        $parameter = [
            'openid' => $this->getMinOpenId($member_id),
            'mchid' => $this->minSet['mchid'],
            'trade_no' => $out_trade_no,
            'transaction_id' => $transaction_id,
            'total_amount' => intval(bcmul($totalmoney, 100,0)), //这里必须是数字number类型，不能是字符串类型
            'refund_amount' => intval(bcmul($refundmoney, 100,0)),
            'refund_no' => $out_refund_no,
        ];

        $access_token = $this->getToken();

        if (!$access_token) {
            throw new AppException('小程序获取access_token失败');
        }

        $url = 'https://api.weixin.qq.com/shop/pay/refundorder?access_token='.$access_token;

        $client = new Client();
        $res = $client->request('POST', $url, ['json'=>$parameter]);
        $result = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);



        $this->payResponseDataLog($out_trade_no, '微信小程序支付管理退款', json_encode(array_merge($parameter,$result)));

        if ($result['errcode']) {
            \Log::debug('---小程序支付管理退款申请失败---', $result);

            throw new AppException($result['errmsg']);
        }

        $pay_order_model->fill([
            'status'=> Pay::ORDER_STATUS_COMPLETE,
            'trade_no' => $result['transaction_id']?:'',
        ])->save();


        return true;
    }

    /**
     * 微信退款
     *
     * @param 订单号 $out_trade_no
     * @param 订单总金额 $totalmoney
     * @param 退款金额 $refundmoney
     * @return array|mixed
     */
    public function oldRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
        $op = '微信小程序退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款总金额：' . $totalmoney;
        if (empty($out_trade_no)) {
            throw new AppException('参数错误');
        }
        $pay_type_id = OrderPay::get_paysn_by_pay_type_id($out_trade_no);
        $pay_type_name = PayType::get_pay_type_name($pay_type_id);
        $pay_order_model = $this->refundlog(Pay::PAY_TYPE_REFUND, $pay_type_name, $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);



        if (empty($this->minSet['apiclient_cert']) || empty($this->minSet['apiclient_key'])) {
            throw new AppException('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!');
        }


        $notify_url = '';
        $payment = $this->getEasyWeChatApp($notify_url);

        try {
            $totalmoney = bcmul($totalmoney, 100,0);
            $refundmoney = bcmul($refundmoney, 100,0);
            $result = $payment->refund->byOutTradeNumber($out_trade_no, $out_refund_no, $totalmoney, $refundmoney);
        } catch (\Exception $e) {
            \Log::debug('---微信退款接口请求错误---', $e->getMessage());

            throw new AppException('微信接口错误:' . $e->getMessage());
        }

        $this->payResponseDataLog($out_trade_no, '微信小程序退款', json_encode($result));

        //微信申请退款失败
        if (isset($result['result_code']) && strtoupper($result['result_code']) == 'FAIL') {

            \Log::debug('---微信退款申请错误---', $result);
            throw new AppException('微信退款申请错误:'.$result['err_code'] . '-' . $result['err_code_des']);
        }

        $status = $this->queryRefund($payment, $out_trade_no);
        \Log::debug('---退款状态---', [$status]);
        if ($status == 'PROCESSING' || $status == 'SUCCESS' || ($status == 'fail' && $result->refund_id)){

            $pay_order_model->fill([
                'status'=> Pay::ORDER_STATUS_COMPLETE,
                'trade_no' => $result['transaction_id']?:'',
            ])->save();

            return true;
        } else {
            \Log::debug('---微信退款接口返回错误---', $result);

            throw new AppException('微信接口错误:'.$result['return_msg'] . '-' . $result['err_code_des'] . '/' . $status);
        }
    }

    /**
     * 订单退款查询
     *
     * @param $payment
     * @param $out_trade_no
     * @return mixed
     */
    public function queryRefund($payment, $out_trade_no)
    {
        $result = $payment->refund->queryByOutTradeNumber($out_trade_no);

        foreach ($result as $key => $value) {
            if (preg_match('/refund_status_\d+/', $key)) {
                return $value;
            }
        }

        return 'fail';
    }

    /**
     * 创建支付对象
     * @param $notify_url
     * @return \EasyWeChat\Payment\Payment
     */
    public function getEasyWeChatApp($notify_url)
    {

        $options = [
            'app_id'             =>  $this->minSet['key'],
            'secret'             =>  $this->minSet['secret'],
            'mch_id'             =>  $this->minSet['mchid'],
            'key'                =>  $this->minSet['api_secret'],
            'cert_path'          =>  $this->minSet['apiclient_cert'],
            'key_path'           =>  $this->minSet['apiclient_key'],
            'notify_url'         => $notify_url,
        ];

        $app = EasyWeChat::payment($options);

        return $app;
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {
        // TODO: Implement doWithdraw() method.
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }


    /**
     * @param $member_id
     * @return mixed|string
     * @throws AppException
     */
    public function getMinOpenId($member_id)
    {
        $openid = Member::getOpenIdForType($member_id, 2);
        if (empty($openid)) {
            throw new AppException('小程序用户openid不存在');
        }

        return $openid;

    }

    /**
     * @param $url
     * @param $post
     * @param int $timeout
     * @param bool $json
     * @return mixed|string
     */
    public function curlRequest($url, $post, $timeout = 60, $json = false)
    {


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $headers[] = "Content-type:application/json;charset=UTF-8";//设置请求体类型
//        $headers[] = "Content-Type:application/x-www-form-urlencoded; charset=UTF-8";

        $headers[] = 'Content-Length: '.strlen($post);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


        //设置打印请求头信息
        //curl_setopt($curl, CURLINFO_HEADER_OUT, true);


        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($post)?http_build_query($post):$post);
        }


        $TLS = substr($url, 0, 8) == "https://" ? true : false;

        if ($TLS) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($curl);

        //设置打印请求头信息
        //$request_headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);


        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);


        if ($json) {
            return json_decode($data, true);
        }
        return $data;

    }

    /**
     * @param $sourcestr string 字符串
     * @param $cutlength integer 保留长度
     * @return string
     */
    public function cutStr($sourcestr, $cutlength) {
        $returnstr = '';
        $i = 0;
        $n = 0;
        $str_length = strlen ($sourcestr); //字符串的字节数
        while ( ($i < $cutlength) and ($i <= $str_length) ) {
            $temp_str = substr ( $sourcestr, $i, 1);
            $ascnum = Ord ($temp_str); //得到字符串中第$i位字符的ascii码
            if ($ascnum >= 224) {
                //如果ASCII位高与224
                $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i = $i + 3; //实际Byte计为3
                $n ++; //字串长度计1
            } elseif ($ascnum >= 192) {
                //如果ASCII位高与192，
                $returnstr = $returnstr . substr ( $sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i = $i + 2; //实际Byte计为2
                $n ++; //字串长度计1
            } elseif ($ascnum >= 65 && $ascnum <= 90) {
                //如果是大写字母，
                $returnstr = $returnstr . substr ( $sourcestr, $i, 1);
                $i = $i + 1; //实际的Byte数仍计1个
                $n ++; //但考虑整体美观，大写字母计成一个高位字符
            } else {
                //其他情况下，包括小写字母和半角标点符号，
                $returnstr = $returnstr . substr ( $sourcestr, $i, 1);
                $i = $i + 1; //实际的Byte数计1个
                $n = $n + 0.5; //小写字母和半角标点等与半个高位字符宽...
            }
        }

        //小程序支付商品详情不能有 ...
//        if ($str_length > $cutlength) {
//            $returnstr = $returnstr . "..."; //超过长度时在尾处加上省略号
//        }
        return $returnstr;
    }
}