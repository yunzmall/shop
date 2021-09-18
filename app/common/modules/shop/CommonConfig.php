<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/2
 * Time: 10:25
 */

namespace app\common\modules\shop;


class CommonConfig
{
    /**
     * @var self
     */
    static $current;

    protected $items;

    /**
     *  constructor.
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