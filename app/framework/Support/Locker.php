<?php


namespace app\framework\Support;


/**
 * 储物柜（跨作用域保存遍历）
 * ！！！只限于不需要扩展的二开项目中使用，禁止在标准产品中使用！！！
 * Class Locker
 * @package app\framework\Support
 */
class Locker
{
    private $grids = [];
    private $logs = [];
    /**
     * @var self
     */
    static $current;

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
    /**
     * 取走
     * @param $key
     * @return mixed
     */
    private function take($key)
    {
        $result = $this->grids[$key];
        unset($this->grids[$key]);
        return $result;
    }

    /**
     * 借用
     * @param $key
     * @return mixed
     */
    private function borrow($key)
    {
        return $this->grids[$key];
    }

    /**
     * 丢掉
     * @param $key
     * @return mixed
     */
    private function drop($key)
    {
        unset($this->grids[$key]);
    }

    /**
     * 存
     * @param $key
     * @param $value
     * @return mixed
     */
    private function store($key, $value)
    {
        return $this->grids[$key] = $value;
    }

    private function _log($key, $action, $path)
    {
        $this->logs[$key] = [$action, $path];
    }

    public function trace($key)
    {
        return $this->logs[$key];
    }

    public function __call($name, $arguments)
    {
        $this->_log($arguments[0], $name, debug_backtrace(0, 1));
        return $this->$name(...$arguments);
    }

}

