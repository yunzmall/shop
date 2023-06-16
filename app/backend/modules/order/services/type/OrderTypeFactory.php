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
use app\common\modules\refund\RefundOrderFactory;

abstract class OrderTypeFactory
{
    use BackendButtonTrait,OrderAfterSalesRefundTrait;

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

    //添加订单关联关系
    public function loadRelations()
    {

    }

    public function afterSales()
    {
        //暂时这样写，之后要改到 OrderAfterSalesRefundTrait 类里，售后按钮refundButton和售后显示refundStepItems
        $refundApply = $this->order->hasOneRefundApply;
        if ($refundApply) {
            $refundApply->backend_button_models = (new \app\backend\modules\refund\services\BackendRefundButtonService($refundApply))->getButtonModels();
            $refundApply->refundSteps =  (new \app\backend\modules\refund\services\steps\RefundStatusStepManager($refundApply))->getStepItems();
        }

        return $refundApply;
    }


    //订单操作按钮
    public function buttonModels()
    {
        return $this->getButtonModels();
    }

    //列表页里的跳转链接
    public function fixedLink()
    {
        return [
            'goods_edit_link' => yzWebFullUrl('goods.goods.edit'),
        ];
    }


    protected function refundedGoodsTotal()
    {

        $refunded_total = $this->order->hasManyOrderGoods->sum(function ($orderGoods) {
            return $orderGoods->after_sales['refunded_total'];
        });


        return $this->order->goods_total > $refunded_total;
    }

    protected function supportPartRefund()
    {
        return $this->order->status >= 1  && RefundOrderFactory::getInstance()->getRefundOrder($this->order, 'backend')->multipleRefund();
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
            'partRefund' => [
                'name' => '部分退款',
                'is_show' => $this->supportPartRefund() && $this->order->canPartRefund() && $this->refundedGoodsTotal(),
                'api' => yzWebFullUrl('order.vue-operation.partRefund'),
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
            'invoice' => [
                'name' => '发票',
                'is_show' => (app('plugins')->isEnabled('invoice') && \Setting::get('plugin.invoice.is_open') == 1) ? true : false,
                'api' => yzWebFullUrl('plugin.invoice.admin.invoicing-order.confirm-invoicing')
            ]
        ];

        return $array;
    }

    //顶部按钮
    public function headButton()
    {
        return [
            [
                'name' => '部分退款',
                'is_show' => $this->order->status > 1 && $this->order->canPartRefund() && in_array($this->order->plugin_id,[0,92,31,32]),
                'api' => yzWebFullUrl('order.vue-operation.partRefund'),
                'type' => 'btn',
            ],
            [
                'name' => '退款并关闭订单',
                'is_show' => $this->order->status > 0 && $this->order->canRefund(),
                'api' => yzWebFullUrl('order.vue-operation.manualRefund'),
                'type' => 'btn',
            ],
            [
                'name' => '关闭订单',
                'is_show' => $this->order->status == 0,
                'api' => yzWebFullUrl('order.vue-operation.close'),
                'type' => 'btn',
            ]
        ];
    }

    //底部按钮
    public function footButton()
    {
        return [
            ['name' => '修改价格', 'is_show' =>  true, 'api' => '', 'type' => 'btn'],
            [
                'name' => '发票',
                'is_show' => (app('plugins')->isEnabled('invoice') && \Setting::get('plugin.invoice.is_open') == 1) ? true : false,
                'api' => yzWebFullUrl('plugin.invoice.admin.invoicing-order.confirm-invoicing'),
                'type' => 'btn',
            ],
            [
                'name' => '订单详情',
                'is_show' => true,
                'api' => yzWebFullUrl('order.detail.vue-index'),
                'type' => 'link',
            ],
        ];
    }

    //顶部显示
    protected function rowTop()
    {
        return [];
    }


    //底部显示
    public function rowBottom()
    {
        $array = [];
        //地址
        if ($this->order->address) {
            $array[] = [
                'text'=> "{$this->order->address->realname}　{$this->order->address->mobile}　{$this->order->address->address}",
            ];
        }

        if ($this->order->note) {
            $array[] = [
                'style' => 'color: red;width: 200px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;',
                'text'=> $this->order->note,
            ];
        }

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
        $top_row = collect([$this->getName()]);

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