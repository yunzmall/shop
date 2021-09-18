<?php
namespace app\common\services\wechatNotice;

use app\Jobs\DispatchesJobs;

class WechatNoticeService
{
    private $uniacid;
    private $request;
    public static $current;

    public function __construct($uniacid = '')
    {
        if ($uniacid) {
            $this->uniacid = $uniacid;
        } else{
            $this->uniacid = \YunShop::app()->uniacid;
        }
    }

    public static function current($uniacid = '')
    {
        if (!isset(self::$current)) {
            return new static($uniacid);
        }
        return self::$current;
    }

    /**
     * @param $message_key  //消息唯一key值
     * @param $param        //替换参数集合
     * @param $openid       //发送人openid，可以是数组
     * @param int $type     //1：订阅消息，2客服消息
     * @param string $url   //消息跳转链接，有特殊跳转链接可传
     * @param array $miniprogram    //消息跳转小程序，有特殊跳转链接可传
     * @return bool
     */
    public function sendMessage($message_key,$param,$openid,$type = 1,$url = '',$miniprogram = [])
    {
        if (empty($openid) || empty($message_key)) {
            return true;
        }
        if (app('plugins')->isEnabled('wechat-notice')) {
            $this->setRequest(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2));
            if (is_array($openid)) {
                foreach ($openid as $item) {
                    $job = new \Yunshop\WechatNotice\job\SendMessageJob($message_key,$param,$item,$this->uniacid,$this->request,$type,$url,$miniprogram);

                    DispatchesJobs::dispatch($job, DispatchesJobs::LOW);
                }
            } else {
                $job = new \Yunshop\WechatNotice\job\SendMessageJob($message_key,$param,$openid,$this->uniacid,$this->request,$type,$url,$miniprogram);
                DispatchesJobs::dispatch($job, DispatchesJobs::LOW);
            }
        }
        return true;
    }

    protected function setRequest($request)
    {
        $this->request = 'file:'.$request[0]?$request[0]['file'].',function:'.$request[0]['function'].',line:'.$request[0]['line']:'';
    }
}