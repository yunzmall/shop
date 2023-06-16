<?php

namespace app\frontend\widgets;

use app\common\helpers\Cache;

class WidgetsConfig
{
    private static $config;

    public static function getConfig(string $type) :array
    {
        if (!isset(static::$config)) {
            static::$config = Cache::remember('frontend_widget_config',86400,function () {
                $plugins = app('plugins')->getEnabledPlugins();
                static::$config = [];
                foreach ($plugins as $plugin) {
                    foreach ($plugin->app()->getFrontendWidgetConfig() as $key => $item) {
                        array_set(static::$config, $key, $item);
                    }
                }
                return static::$config;
            });
        }
        return static::$config[$type] ?? [];
    }
}