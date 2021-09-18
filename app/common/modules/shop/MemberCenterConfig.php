<?php
namespace app\common\modules\shop;


class MemberCenterConfig
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
        $result = [
            'plugin_data' => [
                [
                    'code'  => 'recommend_goods',
                    'name'  => '推荐商品',
                    'sort'  => 0,
                    'class' => '\app\frontend\modules\goods\services\MemberCenterRecommendGoods'
                ],
                [
                    'code'  => 'limitBuy_goods',
                    'name'  => '限时抢购',
                    'sort'  => 1,
                    'class' => '\app\frontend\modules\goods\services\MemberCenterLimitBuyGoods'
                ],
            ]
        ];
        $this->items = $result;    //设置了
        $plugins = app('plugins')->getEnabledPlugins('*');
        foreach ($plugins as $plugin) {
            if (method_exists($plugin->app(),'getMemberCenterConfig')) {
                $plugin->app()->getMemberCenterConfig(); //执行一下这个方法即可，需要设置的时候执行
            }
        }
    }

    protected function getItems()
    {
        if (!isset($this->items)) {
            $this->_getItems();
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