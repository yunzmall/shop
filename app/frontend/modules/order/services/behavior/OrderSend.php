<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 下午3:43
 */

namespace app\frontend\modules\order\services\behavior;

use app\backend\modules\order\services\OrderPackageService;
use app\common\events\order\AfterOrderPackageSentEvent;
use app\common\models\DispatchType;
use app\common\models\Order;
use app\common\models\order\OrderPackage;
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
            if ($express_company_name == "其他快递" && !empty($data['express_company_name'])) $express_company_name = $data['express_company_name'];

            $db_express_model->express_company_name = $express_company_name;

            // $db_express_model->express_sn = request()->input('express_sn','');
            $db_express_model->express_sn = $data['express_sn'] ?: '';

            // dd($db_express_model->express_sn);
            $db_express_model->save();
            //修改所有这个订单的商品 快递信息为这个
            if (empty($data['order_goods_ids'])) {
                // 获取剩余未发货的商品
                $where[] = ['order_id','=',$data['order_id']];
                $order_goods = \app\frontend\models\OrderGoods::uniacid()->where($where)->whereNull('order_express_id')->get()->makeVisible('order_id');
                $order_package = OrderPackage::getOrderPackage($data['order_id'])->where('order_express_id','!=',false);
                $new_order_goods = OrderPackageService::filterGoods($order_goods,$order_package);

                // 单包裹发货
                OrderPackageService::saveOneOrderPackage((int)$data['order_id'],(int)$db_express_model->id,$new_order_goods);
            } else {
                // 新做的参数，可能有些地方没改到
                if(empty($data['order_package'])){// 没有此参数，则将order_goods_ids里未发货的商品全部发货
                    // 获取剩余未发货的商品
                    $order_goods = \app\frontend\models\OrderGoods::uniacid()
                        ->where('order_id',$data['order_id'])
                        ->whereIn('id',$data['order_goods_ids'] ?: [])
                        ->whereNull('order_express_id')
                        ->get()
                        ->makeVisible('order_id');
                    $order_package = OrderPackage::getOrderPackage($data['order_id'])->where('order_express_id','!=',false);
                    $new_order_goods = OrderPackageService::filterGoods($order_goods,$order_package);

                    // 单包裹发货
                    OrderPackageService::saveOneOrderPackage((int)$data['order_id'],(int)$db_express_model->id,$new_order_goods);
                }else{// order_package数据结构[['order_goods_id' => int,'total' => int],['order_goods_id' => int,'total' => int]...]
                    // 获取剩余未发货的商品
                    $where[] = ['order_id','=',$data['order_id']];
                    $order_goods = OrderGoods::uniacid()->where($where)->whereNull('order_express_id')->get()->makeVisible('order_id');
                    $order_package = OrderPackage::getOrderPackage($data['order_id'])->where('order_express_id','!=',false);
                    $new_order_goods = OrderPackageService::filterGoods($order_goods,$order_package);

                    // 校验包裹商品
                    $new_order_package = collect($data['order_package']);
                    OrderPackageService::checkGoodsPackage($new_order_package,$new_order_goods);

                    // 单包裹发货
                    OrderPackageService::saveOneOrderPackage((int)$data['order_id'],(int)$db_express_model->id,$new_order_package);
                    event(new AfterOrderPackageSentEvent(Order::find($data['order_id'])));
                }

                // 全部发货则改订单状态
                $order_goods = \app\frontend\models\OrderGoods::uniacid()->where('order_id',$data['order_id'])->whereNull('order_express_id')->get()->makeVisible('order_id');
                $order_package = OrderPackage::getOrderPackage($data['order_id'])->where('order_express_id','!=',false);
                $new_order_goods = OrderPackageService::filterGoods($order_goods,$order_package);
                if($new_order_goods->isEmpty()){
                    $this->is_all_send_goods = 2;
                }else{
                    $this->is_all_send_goods = 1;
                }
            }
        }
        parent::updateTable();
    }

}