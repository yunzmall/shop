<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/17
 * Time: 下午12:00
 */

namespace app\common\services;

use app\common\events\payment\ChargeComplatedEvent;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\facades\EasyWeChat;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\McMappingFans;
use app\common\models\Member;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\common\services\finance\Withdraw;
use app\frontend\modules\member\services\factory\MemberFactory;
use app\frontend\modules\order\services\OrderPaySuccessService;
use app\frontend\modules\order\services\OrderService;

//这个类之前继承app\common\services\WechatPay的，但是没有什么鬼用啊都把方法全复写了还不如直接继承 Pay

class WechatTradePay extends Pay
{
    private $pay_type;
    private static $attach_type = 'account';

    public function __construct()
    {
//        parent::__construct();
        $this->pay_type = config('app.pay_type');
    }


    public function doPay($data = [], $payType = 1)
    {

    }

    /**
     * 微信退款
     *
     * @param 订单号 $out_trade_no
     * @param 订单总金额 $totalmoney
     * @param 退款金额 $refundmoney
     * @return array
     */
    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        if (request()->is_wechat_trade) {
            return true;
        }
        return false;
    }

    /**
     * 微信提现
     *
     * @param int 提现者用户ID  $member_id
     * @param int 提现金额 $money
     * @param string $desc
     * @param int $type
     */
    public function doWithdraw($member_id, $out_trade_no, $money, $desc = '', $type = 1)
    {

    }


    /**
     * 构造签名
     *
     * @var void
     */
    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }


}