<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2019/4/24
 * Time: 下午3:10
 */

namespace app\payment\controllers;

use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\Order;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\framework\Http\Request;
use app\payment\PaymentController;
use Illuminate\Support\Facades\DB;
use Yunshop\ConvergeAllocFunds\events\SupplierSeparateEndEvent;
use Yunshop\ConvergeAllocFunds\models\OpenAccount;
use Yunshop\ConvergeAllocFunds\models\PayDetailed;
use Yunshop\ConvergeAllocFunds\models\PayRecord;
use Yunshop\ConvergeAllocFunds\models\SeparateRecode;
use Yunshop\ConvergeAllocFunds\services\JoinPayService;
use Yunshop\ConvergePay\models\WithdrawLog;
use Yunshop\ConvergePay\services\NotifyService;
use app\common\events\withdraw\WithdrawSuccessEvent;
use Yunshop\Supplier\common\models\SupplierOrder;

class ConvergeseparateController extends PaymentController
{
    private $attach = [];
    private $parameter = [];

    public function __construct()
    {
        parent::__construct();

        $this->parameter = request()->all();
    }


    /***
     * @param Request $request
     * 分账回调函数
     */
    public function separateNotify()
    {

        $data["resp_code"]="A1000";
        $data["resp_msg"]="success";

        \Log::debug("separateNotify分账回调数据",$this->parameter);
        \Log::debug("separateNotify分账回调数据1",\YunShop::request());

        \Log::debug("separateNotify分账回调数据3",request()->all());


        $pay_sn=$this->parameter["data"]["mch_order_no"];
        $alt_order_no=$this->parameter["data"]["alt_order_no"]; //订单号

        $alt_mch_no=$this->parameter["data"]["alt_info"][0]["allocate_mch_no"];
        $allocate_status=$this->parameter["data"]["alt_info"][0]["allocate_status"];
        $money=$this->parameter["data"]["alt_info"][0]["allocate_amount"];
        \Log::debug("separateNotify分账回调数据5",$alt_mch_no);




        if($this->parameter["data"]["biz_code"] == "B100000" && $this->parameter["data"]["alt_main_status"]=="P1000" && $allocate_status==100 ){

            \Log::debug("分账回调-分账成功");
            \Log::debug("分账回调支付单号",$pay_sn);
            \Log::debug("分账回调商户号",$alt_mch_no);
            \Log::debug("分账回调订单号",$alt_order_no);

            $pay_detailed=PayDetailed::where("pay_sn",$pay_sn)->where("alt_mch_no",$alt_mch_no)->first();
            $pay_id=$pay_detailed->order_pay_id;

            $pay_detailed->status=2;
            $pay_detailed->end_time=time();
            $pay_detailed->save();
            PayRecord::where("pay_sn",$pay_sn)->increment("separate_amount",$money);
            \Log::debug("分账回调返回数据ffff",$pay_id);
            event(new SupplierSeparateEndEvent($pay_id));   //处理完结事件

            \Log::debug("分账回调返回数据",json_encode($data));
            echo json_encode($data);
            exit;

        }




    }


    //开户完成后回调

    public function createNotify()
    {
        $data["resp_code"]="A1000";
        $data["resp_msg"]="success";
        \Log::debug("createNotify回调数据",$this->parameter);


        \Log::debug("createNotify回调数据3",request()->all());
        $alt_mch_no=$this->parameter["data"]["alt_mch_no"];
        if($this->parameter["resp_code"]=="A1000"){
            DB::connection()->enableQueryLog();     // 开启查询日志

            OpenAccount::where("alt_mch_no",$alt_mch_no)->update(["auth_status"=>$this->parameter["data"]["auth_status"],"biz_msg"=>$this->parameter["data"]["biz_msg"]]);
            $logs = DB::getQueryLog();
            \Log::debug("createNotify回调数据sql:",$logs);



        }
        echo json_encode($data);
        exit;

    }


    /***
     * 完结分账回调， 剩余金额全部结算到平台总账户
     */
    public function finishAllocateNotify()
    {

        $data["resp_code"]="A1000";
        $data["resp_msg"]="success";
        \Log::debug("完结分账回调数据",$this->parameter);

        \Log::debug("完结分账回调数据3",request()->all());

        if($this->parameter["data"]["alt_main_status"]=="P1000"){


            PayRecord::where("pay_sn",$this->parameter["data"]["mch_order_no"])
                ->update([
                    "platform_revenue"=>$this->parameter["data"]["alt_this_amount"],
                    "separate_status"=>2,
                    "poundage"=>$this->parameter["data"]["fee"],
                ]);  //完结成功后 更新支付单状态， 与记录平台收益


        }
        echo json_encode($data);
        exit;

    }


    /***
     * @param $pay_id
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 处理最终完结逻辑
     */
    public function findPayOrder($pay_id)
    {
        \Log::debug("分账回调findPayOrder-------pay_id:",$pay_id);
        (new JoinPayService())->findPayOrder($pay_id);

    }


    /***
     * @param Request $request
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 结算回调函数
     */
    public function manualClearNotify()
    {
//        dd(333);

        $data["resp_code"]="A1000";
        $data["resp_msg"]="success";
        \Log::debug("结算回调数据",$this->parameter);
        \Log::debug("结算回调数据1",\YunShop::request()->get());


        $alt_mch_no=$this->parameter["data"]["alt_mch_no"];
        $mch_order_no=$this->parameter["data"]["mch_order_no"];


        if($this->parameter["data"]["order_status"] == "P1000" ){
            $up_data["status"]=5;   //结算完成
            /*分账成功后操作*/
            $parameter=$this->parameter;
            DB::transaction(function ()use($parameter) {
                \Log::debug("结算完成开启事务",$parameter);
                $pay_order=PayDetailed::where("pay_sn",$parameter["data"]["mch_order_no"])->first()->toArray();
                \Log::debug("结算完成开启事务查询order信息",$pay_order);
                PayRecord::where("order_pay_id",$pay_order["order_pay_id"])->increment('separate_amount',$parameter["data"]["settle_amount"]);  //结算成功后增加结算金额
                SupplierOrder::where('order_id', $pay_order["order_id"])->update(['apply_status' => 1]);  //结算成功后 更新供应商订单状态

                $this->findPayOrder( $pay_order["order_pay_id"]); //查询当前支付单下的 订单是否都已经分过账， 如果都已经分账就走完结，给平台分账
            });



        }else{
            $up_data["status"]=4;  //结算失败
        }

        PayDetailed::where("pay_sn",$mch_order_no)->where("alt_mch_no",$alt_mch_no)->update($up_data);




        echo json_encode($data);
        exit;



    }

    /***
     * @param $settle_data
     * @param $money
     * 结算流程
     */
    public function settle($settle_data,$money)
    {
        \Log::debug("开始进入结算数据：",$settle_data);
        \Log::debug("结算金额：",$money);
        $settle_res= (new JoinPayService())->manualClearing($settle_data,$money);


        if($settle_res["data"]["biz_code"] == B100000){
            PayDetailed::where("pay_sn",$settle_data["mch_order_no"])->where("alt_mch_no",$settle_data["alt_mch_no"])->update(["status"=>3]);

            \Log::debug("结算请求成功",$settle_res);

        }else{

            \Log::debug("结算请求失败",$settle_res);
        }

    }


    public function alipayNotify()
    {


        if (empty(\YunShop::app()->uniacid)) {
            if (!$this->parameter['r5_Mp']) {
                \Log::debug('汇聚支付宝支付-分账回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }
            \Log::debug('汇聚支付宝支付-分账回调公众号为空  开始设置uniacid--->', $this->parameter);
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->parameter['r5_Mp'];

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));

        }

        \Log::debug("payNotify支付宝支付回调数据",$this->parameter);

        if($this->parameter["r6_Status"]==100 ){

            \Log::debug("payNotify支付宝支付回调成功");

            $data = [
                'total_fee'    => floatval($this->parameter["r3_Amount"]),
                'out_trade_no' => $this->parameter["r2_OrderNo"],
                'trade_no'     => $this->parameter["r7_TrxNo"],
                'unit'         => 'yuan',
                'pay_type'     => '汇聚支付宝支付-分账',
                'pay_type_id'  => PayFactory::PAY_ALI_SEPARATE_HJ,
            ];

            $pay_id=OrderPay::where("pay_sn",$this->parameter["r2_OrderNo"])->first()->id;
            $record_data["pay_sn"]=$this->parameter["r2_OrderNo"];
            $record_data["order_pay_id"]=$pay_id;
            $record_data["uniacid"]=\YunShop::app()->uniacid;
            \Log::debug('<---------汇聚分账支付宝支付回调--插入PayRecord数据--->', $record_data);
            PayRecord::create($record_data);

            \Log::debug('<---------汇聚分账支付宝支付回调--交易成功--->', request()->all());
            \Log::debug('<---------汇聚分账支付宝支付回调--请求数据--->', $data);
            $this->payResutl($data);


        }
        echo 'success'; exit();



    }


    public function payNotify()
    {


        if (empty(\YunShop::app()->uniacid)) {
            if (!$this->parameter['r5_Mp']) {
                \Log::debug('汇聚微信支付-分账回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }
            \Log::debug('汇聚微信支付-分账回调公众号为空  开始设置uniacid--->', $this->parameter);
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->parameter['r5_Mp'];

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));

        }

        \Log::debug("payNotify支付回调数据",$this->parameter);

        if($this->parameter["r6_Status"]==100 ){

            \Log::debug("payNotify支付回调成功");

            $data = [
                'total_fee'    => floatval($this->parameter["r3_Amount"]),
                'out_trade_no' => $this->parameter["r2_OrderNo"],
                'trade_no'     => $this->parameter["r7_TrxNo"],
                'unit'         => 'yuan',
                'pay_type'     => '汇聚微信支付-分账',
                'pay_type_id'  => PayFactory::PAY_SEPARATE_HJ,
            ];

            $pay_id=OrderPay::where("pay_sn",$this->parameter["r2_OrderNo"])->first()->id;
            $record_data["pay_sn"]=$this->parameter["r2_OrderNo"];
            $record_data["order_pay_id"]=$pay_id;
            $record_data["uniacid"]=\YunShop::app()->uniacid;
            \Log::debug('<---------汇聚分账支付回调--插入PayRecord数据--->', $record_data);
            PayRecord::create($record_data);

            \Log::debug('<---------汇聚分账支付回调--交易成功--->', request()->all());
            \Log::debug('<---------汇聚分账支付回调--请求数据--->', $data);
            $this->payResutl($data);


        }
        echo 'success'; exit();



    }

    public function refundNotify()
    {
        \Log::debug('<---------汇聚分账支付退款回调--数据--->', request()->all());

    }

    public function notifyUrlWechat()
    {
        if (empty(\YunShop::app()->uniacid)) {

            if (!$this->getResponse('r5_Mp')) {
                \Log::debug('汇聚支付回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid =$this->getResponse('r5_Mp');

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        $this->log($this->parameter, '微信支付-HJ');

        if ($this->getSignResult()) {
            if ($_GET['r6_Status'] == '100') {
                \Log::debug('------微信支付-HJ 验证成功-----');

                $data = $this->data('微信支付-HJ', '28');

                $this->payResutl($data);
                \Log::debug('----微信支付-HJ 结束----');

                echo 'success';
            } else {
                //其他错误
                \Log::debug('------微信支付-HJ 其他错误-----');
                echo 'fail';
            }
        } else {
            //签名验证失败
            \Log::debug('------微信支付-HJ 签名验证失败-----');
            echo 'fail1';
        }
    }

    public function returnUrlWechat()
    {
        $trade = \Setting::get('shop.trade');

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            return redirect($trade['redirect_url'])->send();
        }

        if (0 == $_GET['state'] && $_GET['errorDetail'] == '成功') {
            redirect(Url::absoluteApp('member/payYes', ['i' => $this->getResponse('r5_Mp')]))->send();
        } else {
            redirect(Url::absoluteApp('member/payErr', ['i' => $this->getResponse('r5_Mp')]))->send();
        }
    }

    public function notifyUrlAlipay()
    {
        if (empty(\YunShop::app()->uniacid)) {
            if (!$this->getResponse('r5_Mp')) {
                \Log::debug('汇聚支付回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->getResponse('r5_Mp');

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        $this->log($this->parameter, '支付宝支付-HJ');

        if ($this->getSignResult()) {
            if ($_GET['r6_Status'] == '100') {
                \Log::debug('------支付宝支付-HJ 验证成功-----');

                $data = $this->data('支付宝支付', '29');
                $this->payResutl($data);

                \Log::debug('----支付宝支付-HJ 结束----');
                echo 'success';
            } else {
                //其他错误
                \Log::debug('------支付宝支付-HJ 其他错误-----');
                echo 'fail';
            }
        } else {
            //签名验证失败
            \Log::debug('------支付宝支付-HJ 签名验证失败-----');
            echo 'fail1';
        }
    }

    public function returnUrlAlipay()
    {
        $trade = \Setting::get('shop.trade');

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            return redirect($trade['redirect_url'])->send();
        }

        if (0 == $_GET['state'] && $_GET['errorDetail'] == '成功') {
            redirect(Url::absoluteApp('member/payYes', ['i' => $this->getResponse('r5_Mp')]))->send();
        } else {
            redirect(Url::absoluteApp('member/payErr', ['i' => $this->getResponse('r5_Mp')]))->send();
        }
    }

    /**
     * 签名验证
     *
     * @return bool
     */
    public function getSignResult()
    {
        $pay = \Setting::get('plugin.convergePay_set');

        $notify = new NotifyService();
        $notify->setKey($pay['hmacVal']);

        return $notify->verifySign();
    }

    /**
     * 支付日志
     *
     * @param $data
     * @param $sign
     */
    public function log($data, $sign)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($this->getResponse('r2_OrderNo'), $sign, json_encode($data));
    }

    /**
     * 支付回调参数
     *
     * @param $pay_type_id
     * @return array
     */
    public function data($pay_type, $pay_type_id)
    {
        $data = [
            'total_fee' => floatval($this->parameter['r3_Amount']),
            'out_trade_no' => $this->getResponse('r2_OrderNo'),
            'trade_no' => $this->parameter['r7_TrxNo'],
            'unit' => 'yuan',
            'pay_type' => $pay_type,
            'pay_type_id' => $pay_type_id
        ];

        return $data;
    }

    /**
     * 提现回调
     *
     */
    public function notifyUrlWithdraw()
    {
        $parameter = request();
        \Log::debug('汇聚提现回调参数--', $parameter->input());


        //查询提现记录
        $withdrawLog = WithdrawLog::where('merchantOrderNo',$parameter->merchantOrderNo)->first();

        if (!$withdrawLog) {
            echo json_encode([
                'statusCode' => 2002,
                'message' => "汇聚代付记录不存在,单号：{$parameter->merchantOrderNo}",
                'errorCode' => '',
                'errorDesc' => ''
            ]);exit();
        }

        //已提现成功的记录无需再处理
        if ($withdrawLog->status == 1) {
            echo json_encode([
                'statusCode' => 2001,
                'message' => "成功"
            ]);exit();
        }

        //设置公众号i
        if (empty(\YunShop::app()->uniacid)) {

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $withdrawLog->uniacid;

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($withdrawLog->withdraw_sn, '汇聚提现回调', $parameter->input());

        if ($this->checkWithdrawHmac($parameter)) {
            if ($parameter->status == '205') {
                \Log::debug('------汇聚打款 开始-----'.$withdrawLog->withdraw_type);

                if ($withdrawLog->withdraw_type == 1) {
                    //兼容供应商提现
                    if ( app('plugins')->isEnabled('supplier')) {
                        $supplierWithdraw = \Yunshop\Supplier\common\models\SupplierWithdraw::where('apply_sn', $withdrawLog->withdraw_sn)->where('status', 4)->first();
                        if ($supplierWithdraw) {
                            $supplierWithdraw->status = 3;
                            $supplierWithdraw->pay_time = time();
                            $supplierWithdraw->save();
                        }
                    }

                } else {
                    event(new WithdrawSuccessEvent($withdrawLog->withdraw_sn));
                }

                \Log::debug('----汇聚打款 结束----');

                $withdrawLog->status = 1;
                $withdrawLog->response_data = $parameter->input();
                $withdrawLog->save();

                echo json_encode([
                    'statusCode' => 2001,
                    'message' => "成功"
                ]);exit();
            }
            \Log::debug('------汇聚打款失败---- ', $parameter->input());
            if ( in_array($parameter->input('status'), ['204', '208','214'])) {
                $withdrawLog->status = -1;
                $withdrawLog->desc = $parameter->input('errorCodeDesc');
                $withdrawLog->response_data = $parameter->input();
                $withdrawLog->save();

                echo json_encode([
                    'statusCode' => 2002,
                    'message' => "受理失败",
                    'errorCode' => $parameter->errorCode,
                    'errorDesc' => $parameter->errorCodeDesc
                ]);exit();
            }


        } else {
            //签名验证失败
            \Log::debug('------汇聚打款 签名验签失败-----');
            echo json_encode([
                'statusCode' => 2002,
                'message' => "签名验签失败",
                'errorCode' => '300002017',
                'errorDesc' => '签名验签失败'
            ]);exit();
        }
    }

    /**
     * 验证提现签名
     *
     * @param $parameter
     * @return bool
     */
    public function checkWithdrawHmac($parameter)
    {
        $setting = \Setting::get('plugin.convergePay_set');

        $verify = $parameter->hmac == md5($parameter->status . $parameter->errorCode . $parameter->errorCodeDesc . $parameter->userNo
                . $parameter->merchantOrderNo . $parameter->platformSerialNo . $parameter->receiverAccountNoEnc
                . $parameter->receiverNameEnc . sprintf("%.2f", $parameter->paidAmount) . sprintf("%.2f", $parameter->fee) . $setting['hmacVal']);

        \Log::debug('---汇聚打款签名验证--->', [$verify]);

        return $verify;
    }

    /**
     * 微信或支付宝退款
     */
    public function refundUrlWechat()
    {
        $this->logRefund($this->parameter, '微信或支付宝退款-HJ');

        if ($this->getSignWechatResult()) {
            if ($this->parameter['ra_Status'] == '100') {
                \Log::debug('------微信或支付宝退款-HJ 验证成功-----');

                \Log::debug('----微信或支付宝退款-HJ 结束----');
            } else {
                //其他错误
                \Log::debug('------微信或支付宝退款-HJ 其他错误-----');
            }
        } else {
            //签名验证失败
            \Log::debug('------微信或支付宝退款-HJ 签名验证失败-----');
        }

        echo 'success';
    }

    /**
     * 汇聚-微信或支付宝退款 签名验证
     *
     * @return bool
     */
    public function getSignWechatResult()
    {
        $pay = \Setting::get('plugin.convergePay_set');

        \Log::debug('--汇聚-微信或支付宝退款签名验证参数--' . $this->parameter['r1_MerchantNo'] . $this->parameter['r2_OrderNo']
            . $this->parameter['r3_RefundOrderNo'] . $this->parameter['r4_RefundAmount_str'] . $this->parameter['r5_RefundTrxNo']
            . $this->parameter['ra_Status'] . $pay['hmacVal']);

        return $this->parameter['hmac'] == md5($this->parameter['r1_MerchantNo'] . $this->parameter['r2_OrderNo']
                . $this->parameter['r3_RefundOrderNo'] . $this->parameter['r4_RefundAmount_str'] . $this->parameter['r5_RefundTrxNo']
                . $this->parameter['ra_Status'] . $pay['hmacVal']);
    }

    /**
     * 支付日志
     *
     * @param $data
     * @param $sign
     */
    public function logRefund($data, $sign)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($data['r2_OrderNo'], $sign, json_encode($data));
    }

    /**
     * 获取参数值
     * @param string $key
     * @return string
     */
    public function getResponse($key)
    {

        //todo 兼容以前判断
        if ($key == 'r2_OrderNo' && strpos($this->parameter['r2_OrderNo'], ':') !== false) {
            $attach = explode(':', $_GET['r2_OrderNo']);
            return $attach[0];
        }

        return array_get($this->parameter, $key, '');
    }
}