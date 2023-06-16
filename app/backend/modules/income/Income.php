<?php

namespace app\backend\modules\income;

class Income
{
    /**
     * @var self
     */
    static $current;
    private $pageItems;
    private $items;

    /**
     * Income constructor.
     */
    public function __construct()
    {
        self::$current = $this;
    }

    static public function current()
    {
        if (!isset(self::$current)) {
            return new static();
        }
        return self::$current;
    }

    private function _getPageItems()
    {
        $result = [];
        $plugins = app('plugins')->getEnabledPlugins('*');
        foreach ($plugins as $plugin) {
            $result = array_merge((array)$result, (array)$plugin->app()->getIncomePageItems());//增加转义
        }
        return $result;
    }

    public function getPageItems()
    {
        if (!isset($this->pageItems)) {
            $this->pageItems = $this->_getPageItems();
        }
        return $this->pageItems;
    }

    private function _getItems()
    {
        $result = [
            'balance' => [
                'title' => '余额提现',
                'type' => 'balance',
                'class' => 'balance',
                'url' => 'finance.balance-withdraw.detail'
            ]
        ];
        $plugins = app('plugins')->getEnabledPlugins('*');
        foreach ($plugins as $plugin) {
            $plugin_income_items = $plugin->app()->getIncomeItems();
            if (!$plugin_income_items) {
                $plugin_income_items = [];
            }
            $result = array_merge($result, $plugin_income_items);
        }
        return $result;
    }

    public function getItems()
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
}