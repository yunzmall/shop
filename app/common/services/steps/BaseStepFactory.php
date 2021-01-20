<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/14
 * Time: 17:01
 */

namespace app\common\services\steps;


use app\common\services\steps\interfaces\ElementSteps;

abstract class BaseStepFactory implements ElementSteps
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
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


    /**
     * 是否显示
     * @return bool
     */
    public function isShow()
    {
        return true;
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
     * 值、标识
     * @return int
     */
    public function getValue()
    {
        return $this->sort();
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