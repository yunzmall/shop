<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 14:03
 */

namespace app\backend\modules\order\steps;


use app\common\models\Order;
use app\common\services\steps\interfaces\ElementSteps;

abstract class OrderStepFactory implements ElementSteps
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * 标题
     * @return string
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * 描述性文字
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * 图标
     * @return string
     */
    public function getIcon()
    {
        return '';
    }

    /**
     * 指定返回  wait|process|finish|error|success
     * @return string
     */
    public function getStatus()
    {
        if ($this->finishStatus()) {
            return 'finish';
        } elseif ($this->processStatus()) {
            return 'process';
        } elseif ($this->waitStatus()) {
            return 'wait';
        }

        return 'error';
    }


    public function isShow()
    {
        return true;
    }

    public function getValue()
    {
        return $this->sort();
    }


    /**
     * 排序
     * @return int
     */
    public function sort()
    {
        return 0;
    }



    /**
     * 等待状态
     * @return boolean
     */
    abstract function waitStatus();

    /**
     * 处理中状态
     * @return boolean
     */
    abstract function processStatus();

    /**
     * 完成状态
     * @return boolean
     */
    abstract function finishStatus();
}