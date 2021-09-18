<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/23
 * Time: 14:00
 */

namespace app\backend\modules\order\services\type;


class ShopOrderView extends OrderViewBase
{

    public function getRoute()
    {

        return 'order.shop-order-list.index';

        return yzWebFullUrl('order.order-list.index');
    }

    public function getPluginId()
    {
        return 0;
    }

    public function needDisplay()
    {
        return true;
    }

    /**
     * 引入文件路径
     * @return string
     */
    public function getVueFilePath()
    {
        return 'public.admin.orderOperation';
    }

    public function getVuePrimaryName()
    {
        return 'order-operation';
    }

    /**
     * 项目显示名称
     * @return string
     */
    public function getName()
    {
        return '平台自营';
    }

    /**
     * 类型唯一标识
     * @return string
     */
    public function getCode()
    {
        return 'shop';
    }
}