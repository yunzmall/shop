<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/2
 * Time: 14:38
 */

namespace app\backend\modules\order\services\type;


use app\backend\modules\order\services\row\RowBase;
use app\common\models\Order;

abstract class OrderTypeFactory
{
    use BackendButtonTrait;

    //订单类型名称
    protected $name;

    //订单类型标识
    protected $code;

    //注入订单模型
    protected $order;


    //订单所属类型
    abstract public function isBelongTo();


    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    //订单操作
    public function buttonModels()
    {
        return $this->getButtonModels();
    }

    //订单固定操作
    public function fixedButton()
    {

        $array = [
            'detail' => [
                'name' => '订单详情',
                'is_show' => true,
                'api' => yzWebFullUrl('order.detail.vue-index'),
            ],
            'manualRefund' => [
                'name' => '退款并关闭订单',
                'is_show' => $this->order->status > 0 && $this->order->canRefund(),
                'api' => yzWebFullUrl('order.vue-operation.manualRefund'),
            ],
            'close' => [
                'name' => '关闭订单',
                'is_show' => $this->order->status == 0,
                'api' => yzWebFullUrl('order.vue-operation.close'),
            ],
            'goods_detail' => [
                'name' => '商品详情链接',
                'is_show' => true,
                'api' => yzWebFullUrl('goods.goods.edit'),
            ],
        ];

        return $array;
    }


    /**
     * 类型默认显示内容
     * @return array
     */
    public function topShow()
    {
        return [];
    }

    /**
     * 订单列表行内顶部动态数据
     * @return array
     */
    public function rowHeadShow()
    {
        //订单列表顶部显示
        $top_row = collect($this->getName());

        $plugin_content = $this->extraRowContentShow();

        $contents = $top_row->merge($this->topShow())->merge($plugin_content)->flatten()->filter()->values()->all();

        return $contents;
    }

    /**
     * 只显示订单信息的插件
     * @return mixed
     */
    protected function extraRowContentShow()
    {
        // \app\common\modules\shop\ShopConfig::current()->push('shop-foundation.order-list.top-row',
        //     [
        //         'sort' => 100,
        //         'class' => function (\app\common\models\Order $order, $sort) {
        //             return new \app\backend\modules\order\common\row\RowTemplate($order,$sort);
        //         }]);

        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-list.top-row');
        $topRowModels = collect($configs)->map(function ($configItem) {
            return call_user_func($configItem['class'], $this->order, $configItem['sort']);
        });
        $plugin_content = $topRowModels->filter(function(RowBase $top) {
            return $top->enable();//可以显示
        })->sortBy(function (RowBase $top) {
            return $top->sort();//排序
        })->map(function (RowBase $top) {
            return $top->getContent();//显示内容
        });

        return $plugin_content;
    }


    /**
     * 获取订单模型
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }


    /**
     * 订单类型名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 订单类型唯一标识
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }


}