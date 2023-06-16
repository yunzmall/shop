<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/20
 * Time: 下午5:03
 */

namespace app\frontend\modules\order\services\behavior;

use app\common\exceptions\AppException;
use app\common\models\Order;
use app\common\models\order\OrderChangePriceLog;
use app\common\models\order\OrderGoodsChangePriceLog;
use app\common\models\OrderGoods;
use app\frontend\modules\goods\services\models\CreatedOrderGoodsModel;
use app\frontend\modules\order\services\models\CreatedOrderModel;
use function foo\func;

class OrderChangePrice extends OrderOperation
{
    protected $statusBeforeChange = [ORDER::WAIT_PAY];
    protected $name = '改价';
    protected $past_tense_class_name = 'OrderChangedPrice';

    /**
     * 更新数据库表
     * @return bool
     */
    protected function updateTable()
    {
        $this->push();
        return true;
    }

    /**
     * 订单改价操作
     * @return bool|void
     * @throws \app\common\exceptions\AppException
     */
    public function handle()
    {
        parent::handle();
        $this->changeOrderGoodsPrice();
        $this->calculateOrderChangePriceLog();
        $this->changePrice();
        $this->updateTable();
    }

    /**
     * 设置订单改价记录
     */
    public function setOrderChangePriceLog()
    {
        $orderChangePriceLog = new OrderChangePriceLog();

        $this->setRelation('orderChangePriceLog', $orderChangePriceLog);
    }

    /**
     * 设置核销员ID
     */
    public function setClerkId($clerk_id)
    {
        return $this->orderChangePriceLog->clerk_id = $clerk_id;
    }

    /**
     * 设置核销员来源
     */
    public function setClerkType($clerk_type)
    {
        return $this->orderChangePriceLog->clerk_type = $clerk_type;
    }


    /**
     * 订单改价记录
     */
    private function calculateOrderChangePriceLog()
    {
        $orderChangePriceLog = $this->orderChangePriceLog;
        $orderChangePriceLog->change_price = $this->hasManyOrderGoods->sum(function ($orderGoods) {
            return $orderGoods->orderGoodsChangePriceLog->change_price;
        });
        $orderChangePriceLog->old_price = $this->price;
        $orderChangePriceLog->new_price = max($this->price + $orderChangePriceLog->change_price + $this->getChangeDispatchPrice(),0);

        $orderChangePriceLog->username = \Yunshop::app()->username ? : '';
        $orderChangePriceLog->clerk_type = $orderChangePriceLog->clerk_type ? : '';
        $orderChangePriceLog->clerk_id = $orderChangePriceLog->clerk_id ? : 0;
        $orderChangePriceLog->order_id = $this->id;

        //return $orderChangePriceLog;

    }

    /**
     * 设置运费改价金额
     * @param $dispatch_price
     * @return mixed
     */
    public function setDispatchChangePrice($dispatch_price)
    {

        $dispatch_price = $dispatch_price > 0 ?$dispatch_price:0;

        return $this->orderChangePriceLog->change_dispatch_price = $dispatch_price - $this->dispatch_price;
    }

    /**
     * 获取运费改价金额
     * @return mixed
     */
    private function getDispatchChangePrice()
    {

        return $this->orderChangePriceLog->change_dispatch_price;
    }

    /**
     * 获取运费价格
     * @return mixed
     */
    private function getDispatchPrice()
    {
        return max($this->dispatch_price + $this->getDispatchChangePrice(),0);

    }

    /**
     * 获取订单改价金额
     * @return mixed
     */
    private function getChangePrice()
    {

        return $this->orderChangePriceLog->change_price;
    }

    /**
     * 获取订单商品小计
     * @return mixed
     */
    private function getOrderGoodsPrice()
    {
        return $this->order_goods_price + $this->getChangePrice();
    }

    /**
     * 获取订单价格
     * @return mixed
     */
    private function getPrice()
    {

        return max($this->price + $this->getChangePrice() + $this->getDispatchChangePrice(),0);
    }

    private function getChangeDispatchPrice()
    {
        return $this->orderChangePriceLog->change_dispatch_price;

    }

    /**
     * 更新订单价格
     */
    public function changePrice()
    {
        $this->price = $this->getPrice();
        $this->dispatch_price = $this->getDispatchPrice();
        $this->order_goods_price = $this->getOrderGoodsPrice();
        $this->change_price += $this->getChangePrice();
        $this->change_dispatch_price += $this->getChangeDispatchPrice();
    }

    /**
     * 设置订单商品改价记录
     * @param $orderGoodsChangePriceLogs
     */
    public function setOrderGoodsChangePriceLogs($orderGoodsChangePriceLogs)
    {
        $this->hasManyOrderGoods->map(function ($orderGoods) use ($orderGoodsChangePriceLogs) {
            $orderGoodsChangePriceLog = $orderGoodsChangePriceLogs->where('order_goods_id', $orderGoods->id)->first();
            //实例化改价记录
            $orderGoodsChangePriceLog->old_price = $orderGoods->payment_amount;
            if (($orderGoods->payment_amount + $orderGoodsChangePriceLog->change_price) < 0) {
                $msg = $orderGoods->goods_option_title? '('.$orderGoods->goods_option_title.')':'';
                throw new AppException(''.$orderGoods->title.$msg.'：改价金额大于实付金额');
            }
            $orderGoodsChangePriceLog->new_price = max($orderGoods->payment_amount + $orderGoodsChangePriceLog->change_price, 0);
            $orderGoods->setRelation('orderGoodsChangePriceLog', $orderGoodsChangePriceLog);

        });

    }

    /**
     * 更新订单商品model
     */
    private function changeOrderGoodsPrice()
    {
        $this->hasManyOrderGoods->map(function ($orderGoods) {

            //更新商品信息
//            $orderGoods->price = $orderGoods->orderGoodsChangePriceLog->new_price;
            $orderGoods->payment_amount = $orderGoods->orderGoodsChangePriceLog->new_price; //改价修改商品实付金额
            $orderGoods->change_price += $orderGoods->orderGoodsChangePriceLog->change_price;
        });
    }

}