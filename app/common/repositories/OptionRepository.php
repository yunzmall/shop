<?php

namespace app\common\repositories;

use app\common\helpers\Cache;
use app\common\models\Option;
use app\common\models\UniAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\QueryException;

class OptionRepository extends Repository
{
    public $group = [];

    /**
     * Create a new option repository.
     *
     * @return void
     */
    public function __construct()
    {
        try {
            if ($load_option = Cache::get('load_option')) {
                $this->group = $load_option['group'];
                $this->items = $load_option['items'];
                return;
            }
            if (\YunShop::app()->uniacid) {
                $options = Option::whereIn('uniacid', [0, \YunShop::app()->uniacid])->get();
            } else {
                $options = \app\common\modules\option\OptionRepository::all();
            }

            // 至少有一个公众号开启的插件列表
            foreach ($options as $option) {
                if ($this->items[$option['option_name']]['enabled'] == 1) {
                    continue;
                }
                $this->items[$option['option_name']] = $option;
            }
            // 每个公众号开启的插件列表
            foreach ($options as $option) {
                $this->group[$option['uniacid']][$option['option_name']] = $option;
            }
            $this->group[0] = $this->group[0] ?: [];
            foreach ($this->group as $uniacid => &$uniacItems) {
                if (!empty($uniacid)) {
                    // 合并全局和单公众号设置，单公众号设置优先
                    $uniacItems = array_merge($this->group[0], $uniacItems);
                }
            }
            Cache::put('load_option', ['group' => $this->group, 'items' => $this->items], 60);
        } catch (QueryException $e) {
            $this->items = [];
        }
    }

    public function uniacid($uniacid)
    {
        if (empty($uniacid)) {
            return $this->all();
        }
        return $this->group[$uniacid] ?: $this->group[0];
    }

    /**
     * Set a given option value.
     *
     * @param array|string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            // If given key is an array
            foreach ($key as $innerKey => $innerValue) {
                Arr::set($this->items, $innerKey, $innerValue);
                $this->doSetOption($innerKey, $innerValue);
            }
        } else {
            Arr::set($this->items, $key, $value);
            $this->doSetOption($key, $value);
        }
    }

    /**
     * Do really save modified options to database.
     *
     * @return void
     */
    protected function doSetOption($key, $value)
    {
        try {
            if (!DB::table('yz_options')->where('option_name', $key)->first()) {
                $uniAccount = UniAccount::get();
                $pluginData = [];
                foreach ($uniAccount as $u) {
                    $pluginData[] = [
                        'uniacid' => $u->uniacid,
                        'option_name' => $key,
                        'option_value' => $value
                    ];
                }

                DB::table('yz_options')
                    ->insert($pluginData);
            } else {
                DB::table('yz_options')
                    ->where('option_name', $key)
                    ->update(['option_value' => $value]);
            }
            \app\common\modules\option\OptionRepository::flush();

        } catch (QueryException $e) {
            return;
        }
    }
    /**
     * Do really save modified options to database.
     *
     * @return void
     * @deprecated
     */
    public function save()
    {
        $this->itemsModified = array_unique($this->itemsModified);

        try {
            foreach ($this->itemsModified as $key) {
                if (!DB::table('yz_options')->where('option_name', $key)->first()) {
                    DB::table('yz_options')
                        ->insert(['option_name' => $key, 'option_value' => $this[$key]]);
                } else {
                    DB::table('yz_options')
                        ->where('option_name', $key)
                        ->update(['option_value' => $this[$key]]);
                }
                \app\common\modules\option\OptionRepository::flush();

            }

            // clear the list
            $this->itemsModified = [];
        } catch (QueryException $e) {
            return;
        }
    }

    /**
     * Prepend a value onto an array option value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Return the options with key in the given array.
     *
     * @param array $array
     * @return array
     */
    public function only(array $array)
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            if (in_array($key, $array)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Save all modified options into database
     */
    public function __destruct()
    {
        $this->save();
    }

    public function editDisable($id)
    {
        $result = DB::table('yz_options')->where('id', $id)->delete();
        \app\common\modules\option\OptionRepository::flush();
        app('supervisor')->restart();

        return $result;
    }

    public function editEnabledById($id, $enabled)
    {
        $result = DB::table('yz_options')->where('id', $id)->update(['enabled' => $enabled]);
        \app\common\modules\option\OptionRepository::flush();
        return $result;
    }

    public function editTopShowById($id, $enabled)
    {
        $result = DB::table('yz_options')->where('uniacid', \YunShop::app()->uniacid)->where('id', $id)->update(['top_show' => $enabled]);
        \app\common\modules\option\OptionRepository::flush();
        return $result;
    }

    public function insertPlugin($pluginData)
    {
        $result = DB::table('yz_options')->insert($pluginData);
        \app\common\modules\option\OptionRepository::flush();
        app('supervisor')->restart();

        return $result;
    }


}
