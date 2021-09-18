<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/23
 * Time: 14:15
 */

namespace app\backend\modules\order\services\type;


abstract class OrderViewBase
{


    /**
     * 插件订单列表路由
     * @return string
     */
    abstract function getRoute();

    /**
     * 订单plugin_id
     * @return string
     */
    abstract function getPluginId();

    /**
     * 项目是否需要显示
     * @return string
     */
    abstract function needDisplay();

    /**
     * 注意格式，主键名使用小驼峰
     * 引入文件路径
     * @return string
     */
    abstract function getVueFilePath();

    /**
     * 注意格式首字母大写使用 - 横杆代替
     * Vue引入主键名称
     * @return string
     */
    abstract function getVuePrimaryName();

    /**
     * 项目显示名称
     * @return string
     */
    abstract function getName();

    /**
     * 类型唯一标识
     * @return string
     */
    abstract function getCode();
}