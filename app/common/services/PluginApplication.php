<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/27
 * Time: 3:23 PM
 */

namespace app\common\services;


use app\common\providers\PluginServiceProvider;
use Illuminate\Container\Container;

class PluginApplication extends Container
{
    /**
     * @var Plugin
     */
    private $plugin;


    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;


    }

    public function init()
    {
        $this->register();
        $this->loadConfig();
        $this->boot();
    }

    public function register()
    {

    }

    public function boot()
    {

    }

    public function publishes(array $path)
    {
        app()->getProvider(PluginServiceProvider::class)->toPublishes($path,$this->plugin->name);
    }

    public function toPublishes()
    {

    }

    protected function setConfig()
    {

    }

    protected function setMenuConfig()
    {

    }

    public function getWidgetItems()
    {
        return [];
    }


    /**
     * 前端插件挂件配置
     * @return array
     */
    public function getFrontendWidgetConfig() :array
    {
        return [];
    }

    public function loadMenuConfig()
    {
        return $this->setMenuConfig();
    }

    public function getIncomePageItems()
    {
        return [];
    }

    public function getIncomeItems()
    {
        return [];
    }

    public function getTemplateItems()
    {
        return [];
    }

    public function getNoticeTemplateItems()
    {
        return [];
    }

    public function getShopConfigItems()
    {
        return [];
    }

    public function getPluginConfigItems()
    {
        return [];
    }

    protected function loadConfig()
    {
        $this->setConfig();
    }
}