<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/28
 * Time: 上午6:50
 */

namespace app\payment\controllers;

use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\payment\PaymentController;
use Yunshop\Freelogin\common\service\FreeLoginSign;


class ThirdPartyWechatController extends PaymentController
{
    private $appSecret;

    private $expires = 120;

    private $post;

    public function preAction()
    {
        parent::preAction();
        if (empty(\YunShop::app()->uniacid)) {
            if (request()->i) {
                Setting::$uniqueAccountId = \YunShop::app()->uniacid = request()->i;
            } else {
                \Log::debug('---------i--error------', [request()->all()]);
                die('i error');
            }
            $this->post = $_POST;
            $this->post['i'] = \YunShop::app()->uniacid;
            \Log::debug('---------i--------', [\YunShop::app()->uniacid,$this->post]);
        }
    }

    private function verifySign()
    {
        if (!$this->post['appid']) {
            throw new \Exception('appid error');
        }
        if (!$this->post['timestamp']) {
            throw new \Exception('timestamp error');
        }
        if (!$this->post['sign']) {
            throw new \Exception('sign error');
        }
        if (!$this->post['out_trade_no']) {
            throw new \Exception('out_trade_no error');
        }

        $this->getAppData();
        $hfSign = new FreeLoginSign();
        $hfSign->setKey($this->appSecret);
        if (!$hfSign->payNotifyVerify($this->post)) {
            throw new \Exception('sign verify error');
        }
        return true;
    }

    public function notifyUrl()
    {
        try {
            $this->verifySign();
            $this->log(request()->all());

            if (request()->status == 'SUCCESS') {
                if (!$this->post['transaction_id']) {
                    throw new \Exception('transaction_id error');
                }
                if (!isset($this->post['total_fee'])) {
                    throw new \Exception('total_fee error');
                }
                $pay_type_id = PayFactory::THIRD_PARTY_MINI_PAY;

                $data = [
                    'total_fee'    => $this->post['total_fee'] ? : 0 ,
                    'out_trade_no' => $this->post['out_trade_no'],
                    'trade_no'     => $this->post['transaction_id'],
                    'unit'         => 'yuan',
                    'pay_type'     => '第三方微信小程序',
                    'pay_type_id'     => $pay_type_id,
                ];

                $this->payResutl($data);
            }
            die("SUCCESS");
        } catch (\Exception $e) {
            \Log::debug('-----第三方小程序支付-----'.$e->getMessage(),[request()->all()]);
            die($e->getMessage());
        }
    }

    public function notifyH5Url()
    {
        $this->notifyUrl();
    }

    private function getAppData()
    {
        $appData = Setting::get('plugin.freelogin_set');

        if (is_null($appData) || 0 == $appData['status']) {
            throw new \Exception('应用未启用');
        }

        if (empty($appData['app_id']) || empty($appData['app_secret'])) {
            throw new \Exception('应用参数错误');
        }

        if ($appData['app_id'] != request()->input('appid')) {
            throw new \Exception('访问身份异常');
        }

        if (time() - request()->input('timestamp') > $this->expires) {
            throw new ShopException('访问超时');
        }
        $this->appSecret = $appData['app_secret'];
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
        Pay::payResponseDataLog($post['out_trade_no'], '第三方微信小程序支付', json_encode($post));
    }
}