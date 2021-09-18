<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/3
 * Time: 下午3:43
 */

namespace app\frontend\modules\order\services\behavior;

use app\common\models\DispatchType;
use app\common\models\Order;
use app\common\models\OrderGoods;
use app\common\models\order\Express;
use app\common\repositories\ExpressCompany;

class OrderSend extends ChangeStatusOperation
{
    protected $statusBeforeChange = [ORDER::WAIT_SEND];
    protected $statusAfterChanged = ORDER::WAIT_RECEIVE;
    protected $name = '发货';
    protected $time_field = 'send_time';
    public $params = [];
    protected $past_tense_class_name = 'OrderSent';

    protected function _fireEvent()
    {
        $this->fireSentEvent();
    }

    /**
     * @return bool|void
     */
    protected function updateTable()
    {
        $data = $this->params ? $this->params : request()->input();


        if (isset($data['express_code'])) {

            //实体订单
            // $order_id = request()->input('order_id');   
            $order_id = $data['order_id'];

            $db_express_model = Express::where('order_id', $order_id)->first();

            !$db_express_model && $db_express_model = new Express();

            $db_express_model->order_id = $order_id;
            // $db_express_model->express_code = request()->input('express_code','');
            $db_express_model->express_code = $data['express_code'] ?: '';

            // $db_express_model->express_company_name = request()->input('express_company_name', function (){
            //     return array_get(ExpressCompany::create()->where('value',request()->input('express_code',''))->first(),'name','');
            // });
            //当code获取不到物流，并且 有传过来物流名称则使用传过来的（主要针对供应链）
            $express_company_name = array_get(ExpressCompany::create()->where('value', $data['express_code'])->first(), 'name', '其他快递');
            if (empty($express_company_name) && !empty($data['express_company_name'])) $express_company_name = $data['express_company_name'];

            $db_express_model->express_company_name = $express_company_name;

            // $db_express_model->express_sn = request()->input('express_sn','');
            $db_express_model->express_sn = $data['express_sn'] ?: '';

            // dd($db_express_model->express_sn);
            $db_express_model->save();
            //修改所有这个订单的商品 快递信息为这个
            if (empty($data['order_goods_ids'])) {
                OrderGoods::where('order_id', $order_id)->update(['order_express_id' => $db_express_model->id]);
                //修改订单表是否全部发货 为全部发货
//                Order::where('id',$order_id)->update(['is_all_send_goods'=>0]);
            } else {
                OrderGoods::where('order_id', $order_id)->whereIn('id', $data['order_goods_ids'])->update(['order_express_id' => $db_express_model->id]);
                $where[] = ['order_id', '=', $order_id];
                $where[] = ['order_express_id', '=', null];
                $is_all_send = OrderGoods::where($where)->first();
                //判断是否有还未发货的，如果没有状态变更为已全部发货
                if (!empty($is_all_send)) {
                    //修改订单表是否全部发货 为全部发货
                    Order::where('id', $order_id)->update(['is_all_send_goods' => 1]);
                }
            }
        }
        parent::updateTable();
    }

}