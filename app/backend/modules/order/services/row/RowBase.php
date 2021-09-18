<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/18
 * Time: 16:56
 */

namespace app\backend\modules\order\services\row;


abstract class RowBase
{
    protected $order;

    protected $sort;

    public function __construct($order, $sort = 0)
    {
        $this->order = $order;
        $this->sort = $sort;
    }

    /**
     * 排序
     * @return int
     */
    public function sort()
    {
        return $this->sort;
    }

    /**
     * 是否显示
     * @return bool
     */
    abstract function enable();


    /**
     * 显示内容
     * @return array|string
     */
    abstract function getContent();
}