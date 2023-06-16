<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/8/2
 * Time: 10:25
 */

namespace app\common\modules\shop;


abstract class CommonConfig
{
    /**
     * @var self
     */
    public static $current;

    protected $items;

    abstract static public function current();

    protected function _getItems()
    {
        return [];
    }

    protected function getItems()
    {
        if (!isset($this->items)) {
            $this->items = $this->_getItems();
        }
        return $this->items;
    }

    public function getItem($key)
    {
        return array_get($this->getItems(), $key);
    }

    public function clear()
    {
        $this->items = null;
    }

    public function get($key = null)
    {
        if (empty($key)) {
            return $this->getItems();
        }
        return $this->getItem($key);
    }

    public function set($key, $value = null)
    {
        $items = $this->getItems();
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                array_set($items, $k, $v);
            }
        } else {
            array_set($items, $key, $value);
        }
        $this->items = $items;
    }

    public function push($key, $value)
    {
        $all = $this->getItems();
        $array = $this->getItem($key) ?: [];
        $array[] = $value;
        array_set($all, $key, $array);
        $this->items = $all;
    }
}