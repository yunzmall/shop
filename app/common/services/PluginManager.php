<?php

namespace app\common\services;

use app\common\events;
use app\common\helpers\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Events\Dispatcher;
use app\common\repositories\OptionRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;

class PluginManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var OptionRepository
     */
    protected $option;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Collection|null
     */
    protected $plugins;

    public function __construct(
        Application $app,
        OptionRepository $option,
        Dispatcher $dispatcher,
        Filesystem $filesystem
    )
    {
        $this->app = $app;
        $this->option = $option;
        $this->dispatcher = $dispatcher;
        $this->filesystem = $filesystem;
    }

    /**
     * @return Collection
     */
    public function getPlugins()
    {
        if (is_null($this->plugins)) {
			$this->plugins = Cache::get('plugins_list');
			if (!is_null($this->plugins)) {
				return $this->plugins;
			}
			$plugins = new Collection();

            $pluginDirs = $this->filesystem->directories(base_path('plugins'));

            foreach ($pluginDirs as $pluginDir) {
                if (file_exists($pluginDir . "/package.json")) {
                    // Instantiates an Plugin object using the package path and package.json file.
                    $plugin = new Plugin($pluginDir);

                    // Per default all plugins are installed if they are registered in composer.
                    $plugin->setInstalled(true);
                    $plugin->setEnabled($this->isOptionEnable($plugin->name));

                    $plugins->put($plugin->name, $plugin);

                }
            }

            $this->plugins = $plugins->sortBy(function ($plugin, $name) {
                return $plugin->name;
            });
            Cache::put('plugins_list',$this->plugins,5);
        }
        return $this->plugins;
    }

    /**
     * Loads an Plugin with all information.
     *
     * @param string $name
     * @return Plugin|null
     */
    public function getPlugin($name)
    {
        return $this->getPlugins()->get($name);
    }

	public function getEnablePlugin($name)
	{
		return $this->getEnabledPlugins()->get($name);
	}

    public function getPluginId($name)
    {
        $pluginIdConfig = array_first(\app\common\modules\shop\ShopConfig::current()->get('plugin'), function ($item) use ($name) {
            return $item['name'] == $name;
        }, []);
        return $pluginIdConfig['id'];
    }

    public function findPlugin($id)
    {
        return $this->getPlugins()->first(function (Plugin $plugin) use ($id) {
            if ('' === $plugin->getId()) {
                return false;
            }
            return $plugin->getId() == $id;
        });
    }

    /**
     * Enables the plugin.
     *
     * @param string $name
     */
    public function enable($name)
    {
        if (!$this->isOptionEnable($name)) {
            DB::transaction(function () use ($name) {
                $plugin = $this->getPlugin($name);
                $enabled = $this->getEnabled();
//                $enabled[] = $name;
                $this->setEnabled($enabled[$name]['id'], 1, $name);
                $plugin->app()->init();
                $this->dispatcher->fire(new events\PluginWasEnabled($plugin));
            });
        }

    }

    /**
     * Disables an plugin.
     *
     * @param string $name
     */
    public function disable($name)
    {

        $enabled = $this->getEnabled();

        $plugin = $this->getPlugin($name);

        $this->option->editDisable($enabled[$name]['id']);

        $this->dispatcher->fire(new events\PluginWasEnabled($plugin));
    }

    /**
     * Uninstalls an plugin.
     *
     * @param string $name
     */
    public function uninstall($name)
    {
        $plugin = $this->getPlugin($name);

        $this->disable($name);

        // fire event before deleeting plugin files
       // $this->dispatcher->fire(new events\PluginWasDeleted($plugin));

        $find = storage_path().'\logs\plugin_uninstall.log';

        if(!file_exists($find)){
            fopen($find,'a');
        }

        file_put_contents($find, date('Y-m-d H:i:s'). '会员ID：'.\Yunshop::app()->getMemberId().'卸载了插件'.$name.PHP_EOL, FILE_APPEND);

        $this->filesystem->deleteDirectory($plugin->getPath());

        // refresh plugin list
        $this->plugins = null;
    }

    /**
     * Get only enabled plugins.
     *
     * @return Collection
     */
    public function getEnabledPlugins($uniacid = null)
    {
        $only = [];
        foreach ($this->getEnabled($uniacid) as $key => $plugin) {
            if ($plugin['enabled']) {
                $only[] = $key;
            }
        }
        return $this->getPlugins()->only($only);
    }

    /**
     * The id's of the enabled plugins.
     *
     * @return array
     */
    public function getEnabled($uniacid = null)
    {
        if ($uniacid == '*') {
            return (array)$this->option->all();
        }
        $uniacid = $uniacid ?: \YunShop::app()->uniacid;
        return (array)$this->option->uniacid($uniacid);
    }

    /**
     * Persist the currently enabled plugins.
     *
     * @param array $enabled
     */
    protected function setEnabled($id, $enabled, $name = null)
    {
        $pluginData = [
            'uniacid' => \YunShop::app()->uniacid,
            'option_name' => $name,
            'option_value' => 'true',
            'enabled' => $enabled,
        ];
        return $this->option->insertPlugin($pluginData);
    }

    /**
     * Whether the plugin is enabled.
     *
     * @param $plugin
     * @return bool
     */
    public function isEnabled($pluginName)
    {
        $plugin = $this->getPlugin($pluginName);
        if (!$plugin) {
            return false;
        }
        return $this->isOptionEnable($pluginName);

    }

    private function isOptionEnable($plugin)
    {
        $plugins = $this->getEnabled();

        return $plugins[$plugin]['enabled'] ?: false;
//        return in_array($plugin, $this->getEnabled());
    }

    /**
     * The plugins path.
     *
     * @return string
     */
    protected function getPluginsDir()
    {
        return $this->app->basePath() . '/plugins';
    }

    public function enTopShow($name, $enable)
    {
        if (!$this->getPlugin($name)) {
            $name = str_replace("_", "-", $name);
        }
        $enabled = $this->getEnabled();

        $this->setTopShow($enabled[$name]['id'], $enable);
    }

    public function setTopShow($id, $enabled, $name = null)
    {
        if ($id) {
            return $this->option->editTopShowById($id, $enabled);
        } else {
            $pluginData = [
                'uniacid' => \YunShop::app()->uniacid,
                'option_name' => $name,
                'option_value' => 'true',
                'top_show' => $enabled
            ];
            return $this->option->insertPlugin($pluginData);
        }
    }

    public function isTopShow($name)
    {
        $plugins = (array)$this->option->uniacid(\YunShop::app()->uniacid);
        if (!$this->getPlugin($name)) {
            $name = str_replace("_", "-", $name);
        }
        return $plugins = $plugins[$name]['top_show'];
    }
}
