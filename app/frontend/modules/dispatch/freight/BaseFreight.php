<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/18
 * Time: 10:01
 */

namespace app\frontend\modules\dispatch\freight;

use app\frontend\modules\order\models\PreOrder;

abstract class BaseFreight
{
    /**
     * @var PreOrder
     */
    protected $order;
    /**
     * 运费名称
     * @var string
     */
    protected $name;
    /**
     * 运费码
     * @var
     */
    protected $code;

    /**
     * 金额
     * @var float
     */
    protected $freightAmount;


    /*
     * 排序：数值越低权重越大
     */
    protected $weight;



    /*
     * 按权重最大项获取运费计算方式
     * @param third_party 第三方运费计算方式
     * @param shop 默认商城运费计算方式
     *
     */
    protected $priority = 'shop';

    /**
     * BaseFreight constructor.
     * @param PreOrder $order
     * @param $weight
     */
    public function __construct(PreOrder $order, $weight)
    {
        $this->order = $order;

        $this->weight = $weight;
    }

    /**
     * 名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 标识
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /*
     * 排序
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return float
     */
    public function getGroup()
    {
        return $this->priority;
    }

    /*
     * 返回运费金额
     */
    public function getAmount()
    {
        if (!isset($this->freightAmount)) {
            $this->freightAmount =  $this->_getAmount();
        }
        return $this->freightAmount;
    }

    /*
     * 计算运费金额
     */
    abstract protected function _getAmount();

    /*
     * 是否需要计算运费
     * @return bool true 需要 false 不需要
     */
    abstract public function needDispatch();

}