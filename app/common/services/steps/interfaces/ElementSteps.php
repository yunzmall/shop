<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 13:57
 */

namespace app\common\services\steps\interfaces;


interface ElementSteps
{
    /**
     * 标题
     * @return string
     */
    function getTitle();

    /**
     * 描述性文字
     * @return string
     */
    function getDescription();

    /**
     * 图标
     * @return string
     */
    function getIcon();

    /**
     * 设置当前步骤的状态，不设置则根据 steps 确定状态
     * @return string  wait|process|finish|error|success
     */
    function getStatus();

    /**
     * 是否显示
     * @return boolean
     */
    function isShow();


    /**
     * 值、标识
     * @return mixed
     */
    function getValue();

    /**
     * 排序
     * @return int
     */
    function sort();



}