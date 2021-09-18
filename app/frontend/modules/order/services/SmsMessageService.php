<?php


namespace app\frontend\modules\order\services;


use app\common\services\txyunsms\SmsSingleSender;

class SmsMessageService
{
    private $orderModel;

    function __construct($orderModel, $formId = '', $type = 1)
    {
        $this->orderModel = $orderModel;
    }

    public function sent()
    {
        \Log::debug('订单发货短信通知');
        try {
            $this->sendMsg();
            return true;
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
            return false;
        }
    }

    public function sendMsg()
    {
        //查询手机号
        $mobile = \app\common\models\Member::find($this->orderModel->uid)->mobile;
        $data = Array(  // 短信模板中字段的值
            "shop" => \Setting::get('shop.shop')['name'],
        );
        app('sms')->sendGoods($mobile, $data);
        return true;
    }


}