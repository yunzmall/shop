<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 24/03/2017
 * Time: 18:10
 */

namespace app\common\events;


class WechatMessage extends Event
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