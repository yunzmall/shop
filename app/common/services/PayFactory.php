<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/17
 * Time: 下午1:34
 */

namespace app\common\services;


use app\common\exceptions\AppException;
use app\common\services\alipay\AlipayFacePayService;
use app\common\services\alipay\AlipayJsapiPayService;
use app\common\services\alipay\AlipayScanPayService;
use app\common\services\payment\WechatMinPay;
use app\common\services\wechat\HkScanPayService;
use app\common\services\wechat\WechatJsapiPayService;
use Yunshop\DianBangScan\services\DianBangScanService;
use app\common\services\wechat\WechatFacePayService;
use app\common\services\wechat\WechatScanPayService;
use Yunshop\HkPay\services\HkScanAliPayService;
use Yunshop\LoveSpeedPool\service\LSPPay;
use Yunshop\MinApp\Common\Services\WeChatAppletPay;
use Yunshop\StoreBalance\services\StoreBalancePay;
use Yunshop\AppSet\common\services\WeChatAppPay;

class PayFactory
{
    /**
     * 微信
     */
    const PAY_WEACHAT = 1;

    /**
     * 支付宝
     */
    const PAY_ALIPAY = 2;

    /**
     * 余额
     */
    const PAY_CREDIT = 3;

    /**
     * 后台付款
     */
    const PAY_BACKEND = 5;

    /**
     * 云收银-微信
     */
    const PAY_CLOUD_WEACHAT = 6;

    /**
     * 云收银-支付宝
     */
    const PAY_CLOUD_ALIPAY = 7;
    /**
     * 现金支付
     */
    const PAY_CASH = 8;
    /**
     * APP-微信
     */
    const PAY_APP_WEACHAT = 9;
    /**
     * APP-支付宝
     */
    const PAY_APP_ALIPAY = 10;
    /**
     * 门店支付
     */
    const PAY_STORE = 11;
    /**
     * 微信-YZ
     */
    const PAY_YUN_WEACHAT = 12;


    /**
     * 支付宝-YZ
     */
    const PAY_YUN_ALIPAY = 15;


    /**
     * 转账
     */
    const PAY_REMITTANCE = 16;

    /**
     * 货到付款
     */
    const PAY_COD = 17;

    /**
     * 环迅快捷支付
     */
    const PAY_Huanxun_Quick = 18;


    /**
     * EUP-支付
     */
    const PAY_EUP = 19;

    /**
     *威富通公众号支付
     */
    const WFT_PAY = 20;

    /**
     *威富通支付宝支付
     */
    const WFT_ALIPAY = 21;

    /**
     * 环迅微信支付
     */
    const PAY_Huanxun_Wx = 22;


    /**
     * PLD-支付  达人链
     */
    const PAY_PLD = 23;

    /**
     * DIANBANG-支付  店帮扫码支付
     */
    const PAY_DIANBANG = 24;


    /**
     *  店帮支付的分账
     */
    const PAY_SEPARATE = 25;

    /**
     *  易宝支付
     */
    const YOP = 26;

    /**
     *  Usdt支付
     */
    const PAY_Usdt = 27;

    /**
     *  微信支付-HJ(汇聚)
     */
    const PAY_WECHAT_HJ = 28;

    /**
     *  支付宝支付-HJ(汇聚)
     */
    const PAY_ALIPAY_HJ = 29;

    const PAY_WE_CHAT_APPLET = 30;

    /**
     *  团队奖励预存款支付
     */
    const PAY_TEAM_DEPOSIT = 31;

    /**
     *  易宝用户扫码支付宝支付
     */
    const YOP_ALIPAY = 32;

    /**
     *  崛企 微信端支付
     */
    const PAY_WECHAT_JUEQI = 33;

    /**
     *  微信扫码支付-HJ(汇聚)客户主扫
     */
    const PAY_WECHAT_SCAN_HJ = 34;

    /**
     *  支付宝扫码支付-HJ(汇聚)客户主扫
     */
    const PAY_ALIPAY_SCAN_HJ = 35;

    /**
     *  微信人脸支付-HJ(汇聚)
     */
    const PAY_WECHAT_FACE_HJ = 36;

    /**
     *  支付宝人脸支付-HJ(汇聚)
     */
    const PAY_ALIPAY_FACE_HJ = 37;

    /**
     *  微信扫码支付 客户主扫
     */
    const WECHAT_SCAN_PAY = 38;
    /**
     *  微信人脸支付
     */

    const WECHAT_FACE_PAY = 39;
    /**
     *  支付宝扫码支付 客户主扫
     */
    const ALIPAY_SCAN_PAY = 40;
    /**
     *  支付宝人脸支付
     */
    const ALIPAY_FACE_PAY = 41;

    /**
     *  为农 电子钱包-余额支付
     */
    const LCG_BALANCE = 42;
    /**
     *  为农 电子钱包-绑定卡支付
     */
    const LCG_BANK_CARD = 43;

    /**
     *  微信扫码支付-(易宝)客户主扫
     */
    const YOP_WECHAT_SCAN = 44;

    /**
     *  支付宝扫码支付-(易宝)客户主扫
     */
    const YOP_ALIPAY_SCAN = 45;

    /**
     *  微信人脸支付-HJ(易宝)
     */
    const YOP_WECHAT_FACE = 46;

    /**
     *  支付宝人脸支付-HJ(易宝)
     */
    const YOP_ALIPAY_FACE = 47;

    /**
     * 微信JSAPI支付（服务商）
     */
    const WECHAT_JSAPI_PAY = 48;
    /**
     * 支付宝JSAPI支付（服务商）
     */
    const ALIPAY_JSAPI_PAY = 49;

    /**
     *  微信H5支付
     */
    const WECHAT_H5 = 50;

    /**
     *  头条--微信支付
     */
    const PAY_WECHAT_TOUTIAO = 51;

    /**
     * 头条--支付宝支付
     */
    const PAY_ALIPAY_TOUTIAO = 52;

    /**
     * 会员卡--余额支付
     */
    const MEMBER_CARD_PAY = 53;

    /**
     * 确认支付（支付金额为0）
     */
    const CONFIRM_PAY = 54;

    /**
     * 微信小程序支付
     */
    const WECHAT_MIN_PAY = 55;

    /**
     * 港版支付（微信扫码支付）
     */
    const HK_SCAN_PAY = 56;


    /**
     *  微信扫码支付
     */
    const WECHAT_NATIVE = 57;

    /**
     *  PayPal 支付
     */
    const PAY_PAL = 58;

    /**
     * 汇聚快捷支付
     */
    const CONVERGE_QUICK_PAY = 59;

    /**
     *  易宝专业版-公众号支付
     */
    const YOP_PRO_WECHAT = 60;

    /**
     *  易宝专业版-支宝付扫码支付
     */
    const YOP_PRO_ALIPAY = 61;

    /**
     * 港版支付（支付宝扫码支付）
     */
    const HK_SCAN_ALIPAY = 62;

    /**
     * 微信支付-聚合支付（门店）
     */
    const  STORE_AGGREGATE_WECHAT = 63;

    /**
     * 支付宝支付-聚合支付（门店）
     */
    const  STORE_AGGREGATE_ALIPAY = 64;
    /**
     * 扫码支付-聚合支付（门店）
     */
    const  STORE_AGGREGATE_SCAN = 65;


    const PAY_WE_CHAT_APP = 66;

    /**
     *  易宝代付
     */
    const YEE_PAY = 67;

    /**
     * DCM扫码支付
     */
    const  DCM_SCAN_PAY = 68;

    /**
     *  高灯
     */
    const HIGH_LIGHT = 69;


    /**
     * 微信APP支付
     */
    const WECHAT_CPS_APP_PAY = 71;


    /**
     * 微信付款码支付
     */
    const WECHAT_MICRO_PAY = 73;


    /**
     * 汇聚分账支付
     */
    const PAY_SEPARATE_HJ = 75;   //微信

    const PAY_ALI_SEPARATE_HJ = 76; //支付宝


    /**
     * 商云客聚合支付
     */
    const  XFPAY_WECHAT = 78;    // 商云客微信
    const  XFPAY_ALIPAY = 79;    // 商云客支付宝

    /**
     * 门店余额支付
     */
    const STORE_BALANCE_PAY = 80;

    /**
     * 杉德支付
     */
    const  SANDPAY_ALIPAY = 81;    // 杉德支付宝
    const  SANDPAY_WECHAT = 82;    // 杉德微信支付

    /**
     * 拉卡拉支付
     */
    const  LAKALA_WECHAT = 83;    // 拉卡拉微信支付
    const  LAKALA_ALIPAY = 84;    // 拉卡拉支付宝支付

    /**
     * 乐刷支付
     */
    const  LESHUA_ALIPAY = 85;    // 乐刷聚合支付-支付宝
    const  LESHUA_WECHAT = 86;    // 乐刷聚合支付-微信
    const  LESHUA_POS = 87;    // 乐刷聚合支付-扫码支付
    const  LSP_PAY = 88;    // 加速池通证支付

    const CONVERGE_ALIPAY_H5_PAY = 89; //汇聚支付宝H5支付

    const WORK_WITHDRAW_PAY = 90; //好灵工

    const CONVERGE_UNION_PAY = 91; //汇聚云闪付支付

    const WECHAT_TRADE_PAY = 92; //微信支付（视频号）

    const CONVERGE_WECHAT_CARD_PAY = 94; //汇聚支付-微信付款码

    const CONVERGE_ALIPAY_CARD_PAY = 95; //汇聚支付-支付宝付款码

    /**
     * 银典支付
     */
    const  SILVER_POINT_ALIPAY = 96;    // 支付宝-银典支付
    const  SILVER_POINT_WECHAT = 97;    // 微信-银典支付
    const  SILVER_POINT_UNION = 98;     // 银联快捷-银典支付

    /**
     * 码科支付
     */
    const  CODE_SCIENCE_PAY_YU = 99;    // 豫章行代金券支付
    const EPLUS_ALI_PAY = 100;  //智E+支付宝支付
    const EPLUS_WECHAT_PAY = 101;  //智E+微信公众号支付
    const EPLUS_MINI_PAY = 102;  //智E+微信小程序支付
    const LSP_WALLET_PAY = 103;  //加速池爱心值-钱包支付

    const JINEPAY = 104;  //锦银E付

    const AUTH_PAY = 105;  //借权支付

    const SILVER_POINT_PAYMENT = 106;  //银典支付：代付

   const THIRD_PARTY_MINI_PAY = 107;//第三方小程序支付（由第三方小程序发起小程序支付，第三方通知我们支付结果）
   const VIDEO_SHOP_PAY = 108; //视频号小店支付

    const PAY_ALIPAY_PERIOD_DEDUCT = 109;//支付宝周期扣款

    const PAY_ALIPAY_PERIOD_DEDUCT_TRADE = 110;//支付宝周期免密扣款

    const JIANZHIMAO_BANK = 112; //兼职猫银行卡

    const TAX_WITHDRAW_BANK = 113; //税惠添薪

    public static function create($type = null)
    {
        $className = null;
        switch ($type) {
            case self::PAY_APP_WEACHAT:
            case self::WECHAT_CPS_APP_PAY:
            case self::PAY_WEACHAT:
                $className = new WechatPay();
                break;
            case self::WECHAT_MIN_PAY:
                $className = new WechatMinPay();
                break;
            case self::PAY_APP_ALIPAY:
            case self::PAY_ALIPAY:
                $className = new AliPay();
                break;
            case self::PAY_CREDIT:
                $className = new CreditPay();
                break;

            case self::PAY_BACKEND:
                $className = new BackendPay();
                break;
            case self::PAY_CASH:
                $className = new CashPay();
                break;
            case self::PAY_CLOUD_WEACHAT:
            case self::PAY_CLOUD_ALIPAY:
                if (!app('plugins')->isEnabled('cloud-pay')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\CloudPay\services\CloudPayService();
                break;
            case self::PAY_STORE:
                $className = new StorePay();
                break;
            case self::PAY_YUN_WEACHAT:
            case self::PAY_YUN_ALIPAY:
                if (!app('plugins')->isEnabled('yun-pay')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\YunPay\services\YunPayService();
                break;
            case self::PAY_Huanxun_Quick:
            case self::PAY_Huanxun_Wx:
                if (!app('plugins')->isEnabled('huanxun')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\Huanxun\services\HuanxunPayService();
                break;
            case self::PAY_EUP:
                if (!app('plugins')->isEnabled('eup-pay')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\EupPay\services\EupWithdrawService();
                break;
            case self::PAY_REMITTANCE:
                $className = new RemittancePay();
                break;
            case self::PAY_COD:
                $className = new CODPay();
                break;
            case self::WFT_PAY:
                if (!app('plugins')->isEnabled('wft-pay')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\WftPay\services\WftPayService();
                break;
            case self::WFT_ALIPAY:
                if (!app('plugins')->isEnabled('wft-alipay')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\WftAlipay\services\WftAlipayService();
                break;
            case self::PAY_WE_CHAT_APP:
                if (!app('plugins')->isEnabled('app-set')) {
                    throw new AppException('APP设置插件未开启');
                }
                $className = new WeChatAppPay();
                break;
            case self::PAY_PLD:
                if (!app('plugins')->isEnabled('pld-pay')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\PLdPay\services\PldWithdrawService();
                break;
            case self::PAY_SEPARATE:

                \Log::debug('--------payFactory---------');
                if (!app('plugins')->isEnabled('separate')) {
                    throw new AppException('插件未开启');
                }
                $className = new \Yunshop\Separate\Common\Services\SeparateAccountService();

                break;
            case self::PAY_DIANBANG:
                if (!app('plugins')->isEnabled('dian-bang-scan')) {
                    throw new AppException('插件未开启');
                }

                $className = new \Yunshop\DianBangScan\services\DianBangScanService();
                break;
            case self::YOP_PRO_WECHAT:
            case self::YOP_PRO_ALIPAY:
                if (!app('plugins')->isEnabled('yop-pro')) {
                    throw new AppException('易宝专业版插件未开启');
                }
                $className = new \Yunshop\YopPro\services\YopProPayService();
                break;
            case self::YOP:
            case self::YOP_ALIPAY:
                if (!app('plugins')->isEnabled('yop-pay')) {
                    throw new AppException('易宝插件未开启');
                }
                $className = new \Yunshop\YopPay\services\YopPayService();
                break;
            case self::PAY_Usdt:
                if (!app('plugins')->isEnabled('usdtpay')) {
                    throw new AppException('Usdt插件未开启');
                }

                $className = new \Yunshop\Usdtpay\services\UsdtpayService();
                break;
            case self::PAY_WE_CHAT_APPLET:
                if (!app('plugins')->isEnabled('min-app')) {
                    throw new AppException('小程序插件未开启');
                }
                $className = new WeChatAppletPay();
                break;
            case self::PAY_TEAM_DEPOSIT:
                if (!app('plugins')->isEnabled('team-rewards') && \Setting::get('team-rewards.is_open') != 1) {
                    throw new AppException('插件未开启');
                }
                $className = new \Yunshop\TeamRewards\common\services\DepositPayService();
                break;
            case self:: PAY_WECHAT_JUEQI:
                if (!app('plugins')->isEnabled('jueqi-pay') && \Setting::get('plugin.jueqi_pay_set.switch') != 1) {
                    throw new AppException('插件未开启');
                }
                $className = new \Yunshop\JueqiPay\services\JueqiPayService();
                break;
            case self::LCG_BALANCE:
            case self::LCG_BANK_CARD:
                if (!app('plugins')->isEnabled('dragon-deposit')) {
                    throw new AppException('龙存管插件未开启');
                }
                $className = new \Yunshop\DragonDeposit\services\LcgPayService();
                break;
            //汇聚支付微信-分账
            case self::PAY_ALI_SEPARATE_HJ:
            case self::PAY_SEPARATE_HJ:
                if (!app('plugins')->isEnabled('converge-alloc-funds') && \Setting::get(
                        'plugin.ConvergeAllocFunds_set'
                    ) == false) {
                    throw new AppException('商城未开启汇聚分账支付');
                }
                $className = new \Yunshop\ConvergeAllocFunds\services\JoinPayService();
                break;
            //汇聚支付支付宝-分账

            case self::PAY_WECHAT_HJ:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.wechat'
                    ) == false) {
                    throw new AppException('商城未开启汇聚支付插件中微信支付');
                }
                $className = new \Yunshop\ConvergePay\services\WechatService();
                break;

            case self::PAY_ALIPAY_HJ:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.alipay'
                    ) == false) {
                    throw new AppException('商城未开启汇聚支付插件中微信支付');
                }
                $className = new \Yunshop\ConvergePay\services\AlipayService();
                break;
            case self::PAY_ALIPAY_FACE_HJ:
            case self::PAY_ALIPAY_SCAN_HJ:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.wechat'
                    ) == false) {
                    throw new AppException('商城未开启汇聚支付插件中支付宝支付');
                }

                $className = new \Yunshop\ConvergePay\services\AlipayScanService();
                break;
            case self::PAY_WECHAT_FACE_HJ:
            case self::PAY_WECHAT_SCAN_HJ:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.wechat'
                    ) == false) {
                    throw new AppException('商城未开启汇聚支付插件中微信支付');
                }

                $className = new \Yunshop\ConvergePay\services\WechatScanService();
                break;

            //微信扫码支付官方
            case self:: WECHAT_FACE_PAY:
            case self:: WECHAT_SCAN_PAY:
                $className = new WechatScanPayService();
                break;
            case self:: WECHAT_JSAPI_PAY:
                $className = new WechatJsapiPayService();
                break;
            case self:: ALIPAY_FACE_PAY:
            case self:: ALIPAY_SCAN_PAY:
                $className = new AlipayScanPayService();
                break;
            case self::ALIPAY_JSAPI_PAY:
                $className = new AlipayJsapiPayService();
                break;

            case self::YOP_ALIPAY_SCAN:
            case self::YOP_WECHAT_FACE:
            case self::YOP_ALIPAY_FACE:
            case self::YOP_WECHAT_SCAN:
                if (!app('plugins')->isEnabled('yop-pay')) {
                    throw new AppException('易宝插件未开启');
                }
                $className = new \Yunshop\YopPay\services\YopScanPayService();
                break;
            case self::PAY_WECHAT_TOUTIAO:
                if (!app('plugins')->isEnabled('toutiao-mini') && \Setting::get('plugin.toutiao-mini.wx_switch') != 1) {
                    throw new AppException('商城未开启头条支付中微信支付');
                }
                $className = new \Yunshop\ToutiaoMini\services\WechatService();
                break;
            case self::PAY_ALIPAY_TOUTIAO:
                if (!app('plugins')->isEnabled('toutiao-mini') && \Setting::get(
                        'plugin.toutiao-mini.alipay_switch'
                    ) != 1) {
                    throw new AppException('商城未开启头条支付中支付宝支付');
                }
                $className = new \Yunshop\ToutiaoMini\services\AlipayService();
                break;
            case self::MEMBER_CARD_PAY:
                if (!app('plugins')->isEnabled('pet') && \Setting::get('plugin.pet.is_open_pet') != 1) {
                    throw new AppException('商城未开启宠物医院支付中会员卡余额支付');
                }
                $className = new \YunShop\Pet\services\MemberCardPayService();
                break;
            case self::HK_SCAN_PAY:
                if (!app('plugins')->isEnabled('hk-pay')) {
                    throw new AppException('商城未开启港版支付插件');
                }
                $className = new \Yunshop\HkPay\services\HkScanPayService();
                break;
            case self::PAY_PAL:
                if (!app('plugins')->isEnabled('pay-pal')) {
                    throw new AppException('商城未开启PayPal插件');
                }
                $className = new \Yunshop\PayPal\services\PayPalService();
                break;
            case self::CONVERGE_QUICK_PAY:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.quick_pay.is_open'
                    ) != 1) {
                    throw new AppException('商城未开启汇聚支付插件中快捷支付');
                }
                $className = new \Yunshop\ConvergePay\services\QuickPayService();
                break;
            case self::HK_SCAN_ALIPAY:
                if (!app('plugins')->isEnabled('hk-pay')) {
                    throw new AppException('商城未开启港版支付插件');
                }
                $className = new HkScanAliPayService();
                break;
            case self::CONFIRM_PAY:
                $className = new \app\common\services\ConfirmPay();
                break;
            case self::WECHAT_H5:
                $className = new \app\common\services\payment\WechatH5Pay();
                break;
            case self::STORE_AGGREGATE_WECHAT:
                $className = new \Yunshop\StoreAggregatePay\services\WechatPayService();
                break;
            case self::STORE_AGGREGATE_ALIPAY:
                $className = new \Yunshop\StoreAggregatePay\services\AlipayPayService();
                break;
            case self::STORE_AGGREGATE_SCAN:
                $className = new \Yunshop\StoreAggregatePay\services\ScanPayService();
                break;
            case self::WECHAT_NATIVE:
                $className = new \app\common\services\payment\WechatNativePay();
                break;
            case self::DCM_SCAN_PAY:
                $className = new \Yunshop\DcmScanPay\services\ScanPayService();
                break;
            case self::YEE_PAY:
                if (!app('plugins')->isEnabled('yee-pay')) {
                    throw new AppException('商城未开启易宝代付插件');
                }
                $className = new \Yunshop\YeePay\services\YeePayService();
                break;
            case self::HIGH_LIGHT:
                if (!app('plugins')->isEnabled('high-light') || !\Yunshop\HighLight\services\SetService::getStatus()) {
                    throw new AppException('商城未开启高灯提现插件');
                }
                $className = new \Yunshop\HighLight\services\HighLightService();
                break;
            case self::STORE_BALANCE_PAY:
                if (!app('plugins')->isEnabled('store-balance') || \Setting::get('plugin.store_balance.is_open') != 1) {
                    throw new AppException('商城未开启门店余额插件');
                }
                $className = new StoreBalancePay();
                break;
            case self::WECHAT_MICRO_PAY:
                $className = new \app\common\services\payment\WechatPayCodePay();
                break;
            case self::XFPAY_ALIPAY:
                if (!app('plugins')->isEnabled('xfpay') && \Setting::get(
                        'plugin.xfpay_set.xfpay.pay_type.alipay.enabled'
                    ) == 0) {
                    throw new AppException('商城未开启商云客聚合支付 或 插件中支付宝支付');
                }
                $className = new \Yunshop\Xfpay\services\AlipayService;
                break;
            case self::XFPAY_WECHAT:
                if (!app('plugins')->isEnabled('xfpay') && \Setting::get(
                        'plugin.xfpay_set.xfpay.pay_type.wechat.enabled'
                    ) == 0) {
                    throw new AppException('商城未开启商云客聚合支付 或 插件中微信支付');
                }
                $className = new \Yunshop\Xfpay\services\WechatService;
                break;
            case self::SANDPAY_ALIPAY:
                if (!app('plugins')->isEnabled('sandpay') && \Setting::get(
                        'sandpay.set.plugin_enable'
                    ) && \Setting::get('sandpay.set.alipay.enable') == 0) {
                    throw new AppException('杉德支付插件未开启 或 插件中微信支付未开启');
                }
                $className = new \Yunshop\Sandpay\services\Alipay;
                break;
            case self::SANDPAY_WECHAT:
                if (!app('plugins')->isEnabled('sandpay') && \Setting::get(
                        'sandpay.set.plugin_enable'
                    ) && \Setting::get('sandpay.set.wechat.enable') == 0) {
                    throw new AppException('杉德支付插件未开启 或 插件中支付宝支付未开启');
                }
                $className = new \Yunshop\Sandpay\services\WechatPay;
                break;
            case self::LAKALA_ALIPAY:
                if (!app('plugins')->isEnabled('lakala_pay') && \Setting::get(
                        'lakala_pay.set.plugin_enable'
                    ) && \Setting::get('lakala_pay.set.alipay_enable') == 0) {
                    throw new AppException('拉卡拉支付插件未开启 或 插件中支付宝支付未开启');
                }
                $className = new \Yunshop\LaKaLaPay\services\Alipay;
                break;
            case self::LAKALA_WECHAT:
                if (!app('plugins')->isEnabled('lakala_pay') && \Setting::get(
                        'lakala_pay.set.plugin_enable'
                    ) && \Setting::get('lakala_pay.set.wechat_enable') == 0) {
                    throw new AppException('拉卡拉支付插件未开启 或 插件中微信支付未开启');
                }
                $className = new \Yunshop\LaKaLaPay\services\WechatPay;
                break;
            case self::LESHUA_ALIPAY:
                if (!app('plugins')->isEnabled('leshua-pay') && \Setting::get(
                        'leshua-pay.set.plugin_enable'
                    ) && \Setting::get('leshua-pay.set.alipay_enable') == 0) {
                    throw new AppException('乐刷聚合支付插件未开启 或 插件中支付宝支付未开启');
                }
                $className = new \Yunshop\LeshuaPay\services\Alipay;
                break;
            case self::LESHUA_WECHAT:
                if (!app('plugins')->isEnabled('leshua-pay') && \Setting::get(
                        'leshua-pay.set.plugin_enable'
                    ) && \Setting::get('leshua-pay.set.wechat_enable') == 0) {
                    throw new AppException('乐刷聚合支付插件未开启 或 插件中微信支付未开启');
                }
                $className = new \Yunshop\LeshuaPay\services\WechatPay;
                break;
            case self::LESHUA_POS:
                if (!app('plugins')->isEnabled('leshua-pay') && \Setting::get(
                        'leshua-pay.set.plugin_enable'
                    ) && \Setting::get('leshua-pay.set.pos_enable') == 0) {
                    throw new AppException('乐刷聚合支付插件未开启 或 插件中商户扫码收款未开启');
                }
                $className = new \Yunshop\LeshuaPay\services\PosPay;
                break;
            case self::WECHAT_TRADE_PAY:
                $className = new \app\common\services\WechatTradePay;
                break;
            case self::LSP_PAY:
                if (!app('plugins')->isEnabled('love-speed-pool')) {
                    throw new AppException('商城未开启加速池插件');
                }
                $className = new LSPPay();
                break;
            case self::WORK_WITHDRAW_PAY:
                if (!app('plugins')->isEnabled(
                        'worker-withdraw'
                    ) || !\Yunshop\WorkerWithdraw\services\SettingService::usable()) {
                    throw new AppException('商城未开启好灵工插件');
                }
                $className = new \Yunshop\WorkerWithdraw\services\WorkWithdrawService();
                break;
            case self::CONVERGE_UNION_PAY:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.converge_union_pay'
                    ) != 1) {
                    throw new AppException('商城未开启汇聚支付插件中汇聚云闪付支付');
                }
                $className = new \Yunshop\ConvergePay\services\UnionPayService();
                break;
            case self::CONVERGE_WECHAT_CARD_PAY:
                if (!app('plugins')->isEnabled('converge_pay') || !\Setting::get(
                        'plugin.convergePay_set.converge_pay_status'
                    ) || !\Setting::get('plugin.convergePay_set.wechat.wechat_status') || !\Setting::get(
                        'plugin.convergePay_set.wechat.wechat_card_status'
                    )) {
                    throw new AppException('商城未开启汇聚支付插件中微信支付');
                }
                $className = new \Yunshop\ConvergePay\services\WechatService();
                break;
            case self::CONVERGE_ALIPAY_CARD_PAY:
                if (!app('plugins')->isEnabled('converge_pay') || !\Setting::get(
                        'plugin.convergePay_set.converge_pay_status'
                    ) || !\Setting::get('plugin.convergePay_set.alipay.alipay_status') || !\Setting::get(
                        'plugin.convergePay_set.alipay.alipay_card_status'
                    )) {
                    throw new AppException('商城未开启汇聚支付插件中支付宝支付');
                }
                $className = new \Yunshop\ConvergePay\services\AlipayService();
                break;
            case self::CONVERGE_ALIPAY_H5_PAY:
                if (!app('plugins')->isEnabled('converge_pay') && \Setting::get(
                        'plugin.convergePay_set.converge_pay_status'
                    ) != 1) {
                    throw new AppException('商城未开启汇聚支付插件中汇聚云闪付支付');
                }
                $className = new \Yunshop\ConvergePay\services\AlipayH5Service();
                break;
            case self::SILVER_POINT_ALIPAY:
                if (!app('plugins')->isEnabled('silver-point-pay') && \Setting::get(
                        'silver-point-pay.set.plugin_enable'
                    ) && \Setting::get('silver-point-pay.set.alipay_enable') == 0) {
                    throw new AppException('银典支付插件未开启 或 插件中支付宝支付未开启');
                }
                $className = new \Yunshop\SilverPointPay\services\Alipay();
                break;
            case self::SILVER_POINT_WECHAT:
                if (!app('plugins')->isEnabled('silver-point-pay') && \Setting::get(
                        'silver-point-pay.set.plugin_enable'
                    ) && \Setting::get('silver-point-pay.set.wechat_enable') == 0) {
                    throw new AppException('银典支付插件未开启 或 插件中微信支付未开启');
                }
                $className = new \Yunshop\SilverPointPay\services\WechatPay();
                break;
            case self::SILVER_POINT_UNION:
                if (!app('plugins')->isEnabled('silver-point-pay') && \Setting::get(
                        'silver-point-pay.set.plugin_enable'
                    ) && \Setting::get('silver-point-pay.set.union_enable') == 0) {
                    throw new AppException('银典支付插件未开启 或 插件中银联快捷支付未开启');
                }
                $className = new \Yunshop\SilverPointPay\services\UnionPay();
                break;
            case self::CODE_SCIENCE_PAY_YU:
                if (!app('plugins')->isEnabled('code-science-pay') && \Setting::get(
                        'code-science-pay.set.plugin_enable'
                    )) {
                    throw new AppException('豫章行代金券支付插件未开启');
                }
                $className = new \Yunshop\CodeSciencePay\services\YzxApp();
                break;
            case self::EPLUS_WECHAT_PAY:
                if (!app('plugins')->isEnabled('eplus-pay') || !\Yunshop\EplusPay\services\SettingService::usable(
                        'wechat'
                    )) {
                    throw new AppException('智E+支付插件未开启');
                }
                $className = new \Yunshop\EplusPay\services\EplusPay();
                break;
            case self::EPLUS_MINI_PAY:
                if (!app('plugins')->isEnabled('eplus-pay') || !\Yunshop\EplusPay\services\SettingService::usable(
                        'mini'
                    )) {
                    throw new AppException('智E+支付插件未开启');
                }
                $className = new \Yunshop\EplusPay\services\EplusPay();
                break;
            case self::EPLUS_ALI_PAY:
                if (!app('plugins')->isEnabled('eplus-pay') || !\Yunshop\EplusPay\services\SettingService::usable(
                        'alipay'
                    )) {
                    throw new AppException('智E+支付插件未开启');
                }
                $className = new \Yunshop\EplusPay\services\EplusPay();
                break;
            case self::LSP_WALLET_PAY:
                if (!app('plugins')->isEnabled('love-speed-pool')) {
                    throw new AppException('加速池未开启插件');
                }
                $className = new \Yunshop\LoveSpeedPool\service\LSPWalletPay();
                break;

            case self::JINEPAY:
                if (!app('plugins')->isEnabled('jinepay')) {
                    throw new AppException('锦银E付未开启');
                }
                $className = new \Yunshop\Jinepay\services\MobilePay();
                break;
            case self::SILVER_POINT_PAYMENT:
                if (!app('plugins')->isEnabled('silver-point-pay')) {
                    throw new AppException('银典支付未开启');
                }
                $className = new \Yunshop\SilverPointPay\services\SilverPointPayment();
                break;
            case self::AUTH_PAY:
                if (!app('plugins')->isEnabled('sub-auth-payment') || !\Setting::get(
                        'sub-auth-payment.set.plugin_enable'
                    )) {
                    throw new AppException('微信借权支付未开启');
                }
                $className = new \Yunshop\SubAuthPayment\services\AuthPay();
                break;
            case self::THIRD_PARTY_MINI_PAY:
                if (!app('plugins')->isEnabled('freelogin') || !\Setting::get(
                        'plugin.freelogin_set.status'
                    )) {
                    throw new AppException('APP免登录未开启');
                }
                $className = new \Yunshop\Freelogin\common\service\ThirdPartyWechatPay();
                break;

             
          case self::VIDEO_SHOP_PAY:
                if (!app('plugins')->isEnabled('video-shop')) {
                    throw new AppException('视频号小店插件未开启');
                }
                $className = new \Yunshop\VideoShop\services\VideoShopPay();
                break;
            case self::PAY_ALIPAY_PERIOD_DEDUCT:
                if (!app('plugins')->isEnabled('alipay-period-deduct') || !\Yunshop\AlipayPeriodDeduct\services\SettingService::pluginSwitch()) {
                    throw new AppException('支付宝周期扣款未开启');
                }
                $className = new \Yunshop\AlipayPeriodDeduct\services\AlipayPeriodDeductService();
                break;
            case self::PAY_ALIPAY_PERIOD_DEDUCT_TRADE:
                if (!app('plugins')->isEnabled('alipay-period-deduct') || !\Yunshop\AlipayPeriodDeduct\services\SettingService::pluginSwitch()) {
                    throw new AppException('支付宝周期扣款未开启');
                }
                $className = new \Yunshop\AlipayPeriodDeduct\services\AlipayPeriodDeductTradeService();
                break;
            case self::JIANZHIMAO_BANK:
                if (!app('plugins')->isEnabled('jianzhimao-withdraw') || !\Setting::get('jianzhimao-withdraw.set.plugin_enable')) {
                    throw new AppException('兼职猫提现未开启');
                }
                $className = new \Yunshop\JianzhimaoWithdraw\services\JianZhiMaoBank();
                break;
            case self::TAX_WITHDRAW_BANK:
                if (!app('plugins')->isEnabled('tax-withdraw')) {
                    throw new AppException('税筹提现未开启');
                }

                if (!\Setting::get('tax-withdraw.set.plugin_enable')) {
                    throw new AppException(TAX_DIY_NAME . '未开启');
                }

                $className = new \Yunshop\TaxWithdraw\services\TaxWithdrawBank();
                break;
            default:
                $className = null;
        }

        \Log::debug('--------payFactory---------$className', print_r(get_class($className), 1));
        return $className;
    }

    public static function pay($type, $data)
    {
        $pay = self::create($type);

        if ($type == self::PAY_CLOUD_ALIPAY) {
            $data['extra']['pay'] = 'cloud_alipay';
        }

        $result = $pay->doPay($data, $type);

        switch ($type) {
            case self::PAY_WEACHAT:
            case self::PAY_CREDIT:
                if (is_bool($result)) {
                    $result = (array)$result;
                }

                $trade = \Setting::get('shop.trade');
                $redirect = '';

                if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
                    $redirect = $trade['redirect_url'];
                }

                $result['redirect'] = $redirect;
        }

        return $result;
    }

    public static function getPayType()
    {
        $pay_type = [
            ['name' => '微信（App）', 'value' => self::PAY_APP_WEACHAT],
            ['name' => 'CPS微信支付', 'value' => self::WECHAT_CPS_APP_PAY],
            ['name' => '微信', 'value' => self::PAY_WEACHAT],
            ['name' => '微信小程序支付', 'value' => self::WECHAT_MIN_PAY],
            ['name' => '支付宝（App）', 'value' => self::PAY_APP_ALIPAY],
            ['name' => '支付宝', 'value' => self::PAY_ALIPAY],
            ['name' => '余额', 'value' => self::PAY_CREDIT],
            ['name' => '后台付款', 'value' => self::PAY_BACKEND],
            ['name' => '现金支付', 'value' => self::PAY_CASH],
            ['name' => '云收银-微信', 'value' => self::PAY_CLOUD_WEACHAT],
            ['name' => '微信-YZ', 'value' => self::PAY_YUN_WEACHAT],
            ['name' => '环迅快捷支付', 'value' => self::PAY_Huanxun_Quick],
            ['name' => '转账', 'value' => self::PAY_REMITTANCE],
            ['name' => '货到付款', 'value' => self::PAY_COD],
            ['name' => '微信人脸支付', 'value' => self::WECHAT_FACE_PAY],
            ['name' => '微信扫码支付（客户主扫）', 'value' => self::WECHAT_SCAN_PAY],
            ['name' => '微信JSAPI支付（服务商）', 'value' => self::WECHAT_JSAPI_PAY],
            ['name' => '支付宝人脸支付', 'value' => self::ALIPAY_FACE_PAY],
            ['name' => '微信JSAPI支付（服务商）', 'value' => self::WECHAT_JSAPI_PAY],
            ['name' => '支付宝扫码支付（客户主扫）', 'value' => self::ALIPAY_SCAN_PAY],
            ['name' => '支付宝JSAPI支付（服务商）', 'value' => self::ALIPAY_JSAPI_PAY],
            ['name' => '确认支付（支付金额为0）', 'value' => self::CONFIRM_PAY],
            ['name' => '微信H5支付', 'value' => self::WECHAT_H5],
            ['name' => '微信支付-聚合支付（门店）', 'value' => self::STORE_AGGREGATE_WECHAT],
            ['name' => '支付宝支付-聚合支付（门店）', 'value' => self::STORE_AGGREGATE_ALIPAY],
            ['name' => '扫码支付-聚合支付（门店）', 'value' => self::STORE_AGGREGATE_SCAN],
            ['name' => '微信扫码支付', 'value' => self::WECHAT_NATIVE],
            ['name' => 'DCM扫码支付', 'value' => self::DCM_SCAN_PAY],
            ['name' => '微信付款码支付', 'value' => self::WECHAT_MICRO_PAY],
            ['name' => '微信支付（视频号）', 'value' => self::WECHAT_TRADE_PAY],
        ];

        if (app('plugins')->isEnabled('cloud-pay')) {
            $pay_type[] = ['name' => '云收银-支付宝', 'value' => self::PAY_CLOUD_ALIPAY];
        }

        if (app('plugins')->isEnabled('yun-pay')) {
            $pay_type[] = ['name' => '支付宝-YZ', 'value' => self::PAY_YUN_ALIPAY];
        }
        if (app('plugins')->isEnabled('huanxun')) {
            $pay_type[] = ['name' => '环迅微信支付', 'value' => self::PAY_Huanxun_Wx];
        }

        if (app('plugins')->isEnabled('eup-pay')) {
            $pay_type[] = ['name' => 'EUP-支付', 'value' => self::PAY_YUN_ALIPAY];
        }

        if (app('plugins')->isEnabled('wft-pay')) {
            $pay_type[] = ['name' => '威富通公众号支付', 'value' => self::WFT_PAY];
        }

        if (app('plugins')->isEnabled('wft-alipay')) {
            $pay_type[] = ['name' => '威富通支付宝支付', 'value' => self::WFT_ALIPAY];
        }


        if (app('plugins')->isEnabled('pld-pay')) {
            $pay_type[] = ['name' => 'PLD-支付', 'value' => self::PAY_PLD];
        }

        if (app('plugins')->isEnabled('separate')) {
            $pay_type[] = ['name' => '店帮支付的分账', 'value' => self::PAY_SEPARATE];
        }

        if (app('plugins')->isEnabled('dian-bang-scan')) {
            $pay_type[] = ['name' => '店帮扫码', 'value' => self::PAY_DIANBANG];
        }

        if (app('plugins')->isEnabled('yop-pro')) {
            $pay_type[] = ['name' => '易宝专业-微信', 'value' => self::YOP_PRO_WECHAT];
            $pay_type[] = ['name' => '易宝专业-支付宝', 'value' => self::YOP_PRO_ALIPAY];
        }

        if (app('plugins')->isEnabled('yop-pay')) {
            $pay_type[] = ['name' => '微信-yop', 'value' => self::YOP];
            $pay_type[] = ['name' => '支付宝-yop', 'value' => self::YOP_ALIPAY];
        }

        if (app('plugins')->isEnabled('usdtpay')) {
            $pay_type[] = ['name' => 'Usdt支付', 'value' => self::PAY_Usdt];
        }

        if (app('plugins')->isEnabled('team-rewards') && \Setting::get('team-rewards.is_open') == 1) {
            $pay_type[] = ['name' => '预存款支付', 'value' => self::PAY_TEAM_DEPOSIT];
        }


        if (app('plugins')->isEnabled('jueqi-pay') && \Setting::get('plugin.jueqi_pay_set.switch') == 1) {
            $pay_type[] = ['name' => '崛企一码付', 'value' => self::PAY_WECHAT_JUEQI];
        }

        if (!app('plugins')->isEnabled('dragon-deposit')) {
            $pay_type[] = ['name' => '钱包支付', 'value' => self::LCG_BALANCE];
            $pay_type[] = ['name' => '钱包绑卡支付', 'value' => self::LCG_BANK_CARD];
        }

        //汇聚支付-分账
        if (app('plugins')->isEnabled('converge-alloc-funds') && \Setting::get(
                'plugin.ConvergeAllocFunds_set'
            ) == false) {
            $pay_type[] = ['name' => '汇聚微信支付-分账', 'value' => self::PAY_ALI_SEPARATE_HJ];
            $pay_type[] = ['name' => '汇聚支付宝支付-分账', 'value' => self::PAY_SEPARATE_HJ];
        }

        if (app('plugins')->isEnabled('converge_pay') && \Setting::get(
                'plugin.convergePay_set.wechat'
            ) == false) {
            $pay_type[] = ['name' => '微信支付-汇聚', 'value' => self::PAY_WECHAT_HJ];
            $pay_type[] = ['name' => '支付宝支付-汇聚', 'value' => self::PAY_ALIPAY_HJ];
            $pay_type[] = ['name' => '支付宝人脸支付-汇聚', 'value' => self::PAY_ALIPAY_FACE_HJ];
            $pay_type[] = ['name' => '支付宝扫码支付-汇聚', 'value' => self::PAY_ALIPAY_SCAN_HJ];
            $pay_type[] = ['name' => '支付宝扫码支付-汇聚', 'value' => self::PAY_WECHAT_FACE_HJ];
            $pay_type[] = ['name' => '微信扫码支付-汇聚', 'value' => self::PAY_WECHAT_SCAN_HJ];
        }

        if (app('plugins')->isEnabled('yop-pay')) {
            $pay_type[] = ['name' => '微信扫码支付-(易宝)', 'value' => self::YOP_WECHAT_SCAN];
        }

        if (app('plugins')->isEnabled('toutiao-mini') && \Setting::get('plugin.toutiao-mini.wx_switch') == 1) {
            $pay_type[] = ['name' => '头条--微信支付', 'value' => self::PAY_WECHAT_TOUTIAO];
            $pay_type[] = ['name' => '头条--支付宝支付', 'value' => self::PAY_ALIPAY_TOUTIAO];
        }

        if (app('plugins')->isEnabled('pet') && \Setting::get('plugin.pet.is_open_pet') == 1) {
            $pay_type[] = ['name' => '会员卡--余额支付', 'value' => self::MEMBER_CARD_PAY];
        }

        if (app('plugins')->isEnabled('hk-pay')) {
            $pay_type[] = ['name' => '港版支付（微信扫码支付）', 'value' => self::HK_SCAN_PAY];
        }

        if (app('plugins')->isEnabled('pay-pal')) {
            $pay_type[] = ['name' => 'PayPal 支付', 'value' => self::PAY_PAL];
        }

        if (app('plugins')->isEnabled('converge_pay') && \Setting::get(
                'plugin.convergePay_set.quick_pay.is_open'
            ) == 1) {
            $pay_type[] = ['name' => '汇聚快捷支付', 'value' => self::CONVERGE_QUICK_PAY];
        }

        if (app('plugins')->isEnabled('hk-pay')) {
            $pay_type[] = ['name' => '港版支付（支付宝扫码支付）', 'value' => self::HK_SCAN_ALIPAY];
        }

        if (app('plugins')->isEnabled('yee-pay')) {
            $pay_type[] = ['name' => '易宝代付', 'value' => self::YEE_PAY];
        }

        if (app('plugins')->isEnabled('high-light') && \Yunshop\HighLight\services\SetService::getStatus()) {
            $pay_type[] = ['name' => '高灯', 'value' => self::HIGH_LIGHT];
        }

        if (app('plugins')->isEnabled('store-balance') && \Setting::get('plugin.store_balance.is_open') == 1) {
            $pay_type[] = ['name' => '门店余额支付', 'value' => self::STORE_BALANCE_PAY];
        }

        if (app('plugins')->isEnabled('xfpay') && \Setting::get(
                'plugin.xfpay_set.xfpay.pay_type.alipay.enabled'
            ) != 0) {
            $pay_type[] = ['name' => '商云客支付宝', 'value' => self::XFPAY_ALIPAY];
        }

        if (app('plugins')->isEnabled('xfpay') && \Setting::get(
                'plugin.xfpay_set.xfpay.pay_type.wechat.enabled'
            ) != 0) {
            $pay_type[] = ['name' => '商云客微信', 'value' => self::XFPAY_WECHAT];
        }

        if (app('plugins')->isEnabled('sandpay') && \Setting::get(
                'sandpay.set.plugin_enable'
            ) && \Setting::get('sandpay.set.alipay.enable') != 0) {
            $pay_type[] = ['name' => '杉德支付宝', 'value' => self::SANDPAY_ALIPAY];
        }

        if (app('plugins')->isEnabled('sandpay') && \Setting::get(
                'sandpay.set.plugin_enable'
            ) && \Setting::get('sandpay.set.wechat.enable') != 0) {
            $pay_type[] = ['name' => '杉德微信支付', 'value' => self::SANDPAY_WECHAT];
        }

        if (app('plugins')->isEnabled('lakala_pay') && \Setting::get(
                'lakala_pay.set.plugin_enable'
            ) && \Setting::get('lakala_pay.set.alipay_enable') != 0) {
            $pay_type[] = ['name' => '拉卡拉支付宝支付', 'value' => self::LAKALA_ALIPAY];
        }


        if (app('plugins')->isEnabled('lakala_pay') && \Setting::get(
                'lakala_pay.set.plugin_enable'
            ) && \Setting::get('lakala_pay.set.wechat_enable') != 0) {
            $pay_type[] = ['name' => '拉卡拉支付微信支付', 'value' => self::LAKALA_WECHAT];
        }

        if (app('plugins')->isEnabled('leshua-pay') && \Setting::get(
                'leshua-pay.set.plugin_enable'
            ) && \Setting::get('leshua-pay.set.alipay_enable') != 0) {
            $pay_type[] = ['name' => '乐刷聚合支付-支付宝', 'value' => self::LESHUA_ALIPAY];
        }

        if (app('plugins')->isEnabled('leshua-pay') && \Setting::get(
                'leshua-pay.set.plugin_enable'
            ) && \Setting::get('leshua-pay.set.wechat_enable') != 0) {
            $pay_type[] = ['name' => '乐刷聚合支付-微信', 'value' => self::LESHUA_WECHAT];
        }

        if (app('plugins')->isEnabled('leshua-pay') && \Setting::get(
                'leshua-pay.set.plugin_enable'
            ) && \Setting::get('leshua-pay.set.pos_enable') != 0) {
            $pay_type[] = ['name' => '乐刷聚合支付-扫码支付', 'value' => self::LESHUA_POS];
        }

        if (app('plugins')->isEnabled('love-speed-pool')) {
            $pay_type[] = ['name' => '加速池通证支付', 'value' => self::LSP_PAY];
        }

        if (app('plugins')->isEnabled('worker-withdraw') && \Yunshop\WorkerWithdraw\services\SettingService::usable()) {
            $pay_type[] = ['name' => '好灵工', 'value' => self::WORK_WITHDRAW_PAY];
        }

        if (app('plugins')->isEnabled('converge_pay') && \Setting::get(
                'plugin.convergePay_set.converge_union_pay'
            ) == 1) {
            $pay_type[] = ['name' => '汇聚云闪付支付', 'value' => self::CONVERGE_UNION_PAY];
        }

        if (app('plugins')->isEnabled('converge_pay') && \Setting::get(
                'plugin.convergePay_set.converge_pay_status'
            ) && \Setting::get('plugin.convergePay_set.wechat.wechat_status') && \Setting::get(
                'plugin.convergePay_set.wechat.wechat_card_status'
            )) {
            $pay_type[] = ['name' => '汇聚支付-微信付款码', 'value' => self::CONVERGE_WECHAT_CARD_PAY];
        }

        if (app('plugins')->isEnabled('converge_pay') && \Setting::get(
                'plugin.convergePay_set.converge_pay_status'
            ) && \Setting::get('plugin.convergePay_set.alipay.alipay_status') && \Setting::get(
                'plugin.convergePay_set.alipay.alipay_card_status'
            )) {
            $pay_type[] = ['name' => '汇聚支付-支付宝付款码', 'value' => self::CONVERGE_ALIPAY_CARD_PAY];
        }

        if (app('plugins')->isEnabled('converge_pay') && \Setting::get(
                'plugin.convergePay_set.converge_pay_status'
            ) == 1) {
            $pay_type[] = ['name' => '汇聚支付宝H5支付', 'value' => self::CONVERGE_ALIPAY_H5_PAY];
        }

        if (app('plugins')->isEnabled('silver-point-pay') && \Setting::get(
                'silver-point-pay.set.plugin_enable'
            ) && \Setting::get('silver-point-pay.set.alipay_enable') != 0) {
            $pay_type[] = ['name' => '支付宝-银典支付', 'value' => self::SILVER_POINT_ALIPAY];
        }

        if (app('plugins')->isEnabled('silver-point-pay') && \Setting::get(
                'silver-point-pay.set.plugin_enable'
            ) && \Setting::get('silver-point-pay.set.wechat_enable') != 0) {
            $pay_type[] = ['name' => '微信-银典支付', 'value' => self::SILVER_POINT_WECHAT];
        }

        if (app('plugins')->isEnabled('silver-point-pay') && \Setting::get(
                'silver-point-pay.set.plugin_enable'
            ) && \Setting::get('silver-point-pay.set.union_enable') != 0) {
            $pay_type[] = ['name' => '银联快捷-银典支付', 'value' => self::SILVER_POINT_UNION];
        }

        if (app('plugins')->isEnabled('code-science-pay') && \Setting::get(
                'code-science-pay.set.plugin_enable'
            )) {
            $pay_type[] = ['name' => '豫章行代金券支付', 'value' => self::CODE_SCIENCE_PAY_YU];
        }

        if (app('plugins')->isEnabled('eplus-pay') && \Yunshop\EplusPay\services\SettingService::usable(
                'wechat'
            )) {
            $pay_type[] = ['name' => '智E+微信公众号支付', 'value' => self::EPLUS_WECHAT_PAY];
        }

        if (app('plugins')->isEnabled('eplus-pay') && \Yunshop\EplusPay\services\SettingService::usable(
                'mini'
            )) {
            $pay_type[] = ['name' => '智E+微信小程序支付', 'value' => self::EPLUS_MINI_PAY];
        }

        if (app('plugins')->isEnabled('eplus-pay') && \Yunshop\EplusPay\services\SettingService::usable(
                'alipay'
            )) {
            $pay_type[] = ['name' => '智E+支付宝支付', 'value' => self::EPLUS_ALI_PAY];
        }

        if (app('plugins')->isEnabled('love-speed-pool')) {
            $pay_type[] = ['name' => '加速池爱心值-钱包支付', 'value' => self::LSP_WALLET_PAY];
        }

        if (app('plugins')->isEnabled('jinepay')) {
            $pay_type[] = ['name' => '锦银E付', 'value' => self::JINEPAY];
        }

        if (app('plugins')->isEnabled('silver-point-pay')) {
            $pay_type[] = ['name' => '银典支付：代付', 'value' => self::SILVER_POINT_PAYMENT];
        }

        if (app('plugins')->isEnabled('sub-auth-payment') && \Setting::get(
                'sub-auth-payment.set.plugin_enable'
            )) {
            $pay_type[] = ['name' => '借权支付', 'value' => self::AUTH_PAY];
        }

        if (app('plugins')->isEnabled('freelogin') && \Setting::get(
                'plugin.freelogin_set.status'
            )) {
            $pay_type[] = ['name' => '第三方小程序支付', 'value' => self::THIRD_PARTY_MINI_PAY];
        }

        if (app('plugins')->isEnabled('video-shop')) {
            $pay_type[] = ['name' => '视频号小店支付', 'value' => self::VIDEO_SHOP_PAY];
        }

        if (app('plugins')->isEnabled('alipay-period-deduct') && \Yunshop\AlipayPeriodDeduct\services\SettingService::pluginSwitch()) {
            $pay_type[] = ['name' => '支付宝周期扣款', 'value' => self::PAY_ALIPAY_PERIOD_DEDUCT];
        }

        if (app('plugins')->isEnabled('alipay-period-deduct') && \Yunshop\AlipayPeriodDeduct\services\SettingService::pluginSwitch()) {
            $pay_type[] = ['name' => '支付宝周期免密扣款', 'value' => self::PAY_ALIPAY_PERIOD_DEDUCT_TRADE];
        }

        return $pay_type;
    }
}