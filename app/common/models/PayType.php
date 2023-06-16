<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/28
 * Time: 上午11:32
 */

namespace app\common\models;

/**
 * Class PayType
 * @package app\common\models
 * @property string code
 * @property string setting_key
 * @property string name
 * @property int need_password
 * @property int id
 */
class PayType extends BaseModel
{
    public $table = 'yz_pay_type';
    const UNPaid = 0;//未支付
    const WECHAT_PAY = 1;//微信
    const ALIPAY = 2;//支付宝
    const CREDIT = 3;//余额支付
    const CASH = 4;//货到付款
    const BACKEND = 5;//后台支付
    const PAY_CLOUD_WEACHAT = 6;//云收银微信
    const PAY_CLOUD_ALIPAY = 7;//云收银支付宝
    const CASH_PAY = 8;//现金支付
    const WechatApp = 9;//微信App支付
    const AlipayApp = 10;//支付宝App支付
    const STORE_PAY = 11;//门店
    const PAY_YUN_WECHAT = 12;//微信-YZ
    const WANMI_Pay = 13;//快捷
    const ANOTHER_Pay = 14;//找人代付
    const PAY_YUN_ALIPAY = 15;//支付宝-YZ
    const REMITTANCE = 16;//转账
    const COD = 17;//货到付款
    const HXQUICK = 18;//环迅快捷支付
    const HXWECHAT = 22;//环迅微商支付
    const YOP = 26;//易宝支付
    const USDTPAY = 27;//USDT支付
    const WECHAT_HJ_PAY = 28;//微信支付-HJ(汇聚)
    const ALIPAY_HJ_PAY = 29;//支付宝支付-HJ(汇聚)
    const PAY_TEAM_DEPOSIT = 31;//预存款支付
    const WECHAT_JUEQI_PAY = 32;
    const HJ_WECHAT_SCAN_PAY = 34;//微信扫码支付(汇聚) 客户主扫
    const HJ_WECHAT_FACE_PAY = 35;//微信人脸支付(汇聚)
    const HJ_ALIPAY_SCAN_PAY= 36;//支付宝扫码支付(汇聚) 客户主扫
    const HJ_ALIPAY_FACE_PAY= 37;//支付宝人脸支付(汇聚)
    const WECHAT_SCAN_PAY = 38;//微信扫码支付 客户主扫
    const WECHAT_FACE_PAY = 39;//微信人脸支付
    const ALIPAY_SCAN_PAY= 40;//支付宝扫码支付 客户主扫
    const ALIPAY_FACE_PAY= 41;//支付宝人脸支付
    const LCG_BALANCE = 42;//为农 电子钱包-余额支付
    const LCG_BANK_CARD = 43;//为农 电子钱包-绑定卡支付
    const YOP_WECHAT_SCAN_PAY = 44;//微信扫码支付(易宝) 客户主扫
    const YOP_WECHAT_FACE_PAY = 45;//微信人脸支付(易宝)
    const YOP_ALIPAY_SCAN_PAY= 46;//支付宝扫码支付(易宝) 客户主扫
    const YOP_ALIPAY_FACE_PAY= 47;//支付宝人脸支付(易宝)
    const WECHAT_JSAPI_PAY= 48;//微信h5支付
    const ALIPAY_JSAPI_PAY= 49;//支付宝h5支付
    const WECHAT_H5 = 50; //微信H5支付
    const PAY_WECHAT_TOUTIAO = 51;//微信支付（头条）
    const PAY_ALIPAY_TOUTIAO = 52;//支付宝支付（头条）
    const MEMBER_CARD_PAY = 53;//会员卡余额支付(宠物医院)
    const CONFIRM_PAY = 54; // 确认支付（支付金额为0）
    const WECHAT_MIN_PAY = 55; //微信小程序支付
    const HK_SCAN_PAY = 56;//港版微信扫码支付
    const WECHAT_NATIVE = 57;//微信扫码支付
    const PAY_PAL = 58;//PayPal支付
    const CONVERGE_QUICK_PAY = 59;//汇聚快捷支付
    const YOP_PRO_WECHAT = 60;//易宝pro微信支付
    const YOP_PRO_ALIPAY = 61;//易宝pro支付宝支付
    const HK_SCAN_ALIPAY = 62;//港版支付宝H5
    const STORE_AGGREGATE_WECHAT = 63;//微信支付-聚合支付
    const STORE_AGGREGATE_ALIPAY = 64;//支付宝支付-聚合支付
    const STORE_AGGREGATE_SCAN = 65;//扫码支付-聚合支付
    const DCM_SCAN_PAY = 68;//Dcm扫码支付
    const WECHAT_CPS_APP_PAY = 71; //聚合CPS微信支付
    const XFPAY_WECHAT = 78; //商云客聚合支付-微信支付
    const XFPAY_ALIPAY = 79; //商云客聚合支付-支付宝
    const SANDPAY_ALIPAY = 81; //杉德支付宝
    const SANDPAY_WECHAT = 82; //杉德微信支付
    const LAKALA_WECHAT = 83; //拉卡拉微信支付
    const LAKALA_ALIPAY = 84; //拉卡拉支付宝
    const LESHUA_ALIPAY = 85; //乐刷支付宝
    const LESHUA_WECHAT = 86; //乐刷微信
    const LESHUA_POS = 87; //乐刷pos收银
    const LSP_PAY = 88; //加速池支付
    const CONVERGE_ALIPAY_H5_PAY = 89; //汇聚支付宝H5
    const CONVERGE_UNION_PAY = 91; //汇聚云闪付支付
    const WECHAT_TRADE_PAY = 92;//微信支付(视频号)

    const SILVER_POINT_ALIPAY = 96;// 支付宝-银典支付
    const SILVER_POINT_WECHAT = 97;// 微信-银典支付
    const SILVER_POINT_UNION = 98;// 银联快捷-银典支付

    const CODE_SCIENCE_PAY_YU = 99;// 豫章行代金券支付
    const EPLUS_ALI_PAY = 100;
    const EPLUS_WECHAT_PAY = 101;
    const EPLUS_MINI_PAY = 102;
    const LSP_WALLET_PAY = 103;//钱包支付
    const JINEPAY = 104;    //锦银E付

    const AUTH_PAY = 105;    //微信借权支付(本质是微信h5支付)
    const VIDEO_SHOP_PAY = 108; //视频号小店

    /**
     * 查询所有分类类型
     *
     * @return mixed
     */
    public static function get_pay_type_name($id)
    {
        return self::select('name')->where('id', $id)->value('name');
    }

    public static function fetchPayName()
    {
        return self::select('name')
            ->groupBy('name')
            ->get();
    }


    public static function updateDalance($name_id,$name)
    {
         return self::where('id',$name_id)
             ->update(['name'=>$name]);
    }
    public static function fetchPayType($name)
    {
        return self::where('name', $name)
            ->get();
    }

    public static function payTypeColl()
    {
        $coll = [];
        $pay_names = PayType::fetchPayName();

        if (!$pay_names->isEmpty()) {
            foreach ($pay_names as $item) {
                $pay_types = PayType::fetchPayType($item->name);

                if (!$pay_types->isEmpty()) {
                    foreach ($pay_types as $rows) {
                        $coll[$item->name][] = $rows->id;
                    }
                }
            }
        }

        return $coll;
    }
}