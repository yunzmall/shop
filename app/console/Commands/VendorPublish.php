<?php

namespace app\console\Commands;

use app\common\providers\PluginServiceProvider;
use Illuminate\Foundation\Console\VendorPublishCommand;

class VendorPublish extends VendorPublishCommand
{
    public function handle()
    {
        $plugins = app('plugins')->getPlugins();
        foreach ($plugins as $plugin) {
            $plugin->app()->toPublishes();
        }
        parent::handle();
    }
}