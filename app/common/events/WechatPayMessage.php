<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/7/15
 * Time: 16:34
 */

namespace app\common\events;


/**
 * 打算做微信支付的事件通知类
 * Class WechatPayMessage
 * @package app\common\events
 */
class WechatPayMessage extends Event
{
    protected $wechatApp;
    protected $server;
    protected $message;
    protected $plugin;

    /**
     * WechatMessage constructor.
     * @param \app\common\modules\wechat\WechatApplication $wechatApp
     * @param \EasyWeChat\Server\Guard $server
     * @param array $message
     */
    public function __construct($wechatApp, $server, $message, $plugin)
    {
        $this->wechatApp = $wechatApp;
        $this->server = $server;
        $this->message = $message;
        $this->plugin = $plugin;
    }

    public function getWechatApp()
    {
        return $this->wechatApp;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}