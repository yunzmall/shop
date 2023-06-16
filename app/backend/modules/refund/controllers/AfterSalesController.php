<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/10/19
 * Time: 17:11
 */

namespace app\backend\modules\refund\controllers;


use app\backend\modules\refund\models\OrderRefund;
use app\backend\modules\refund\services\AfterSalesExport;
use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;

class AfterSalesController extends BaseController
{


    /**
     * @return OrderRefund
     */
    protected function refundModel()
    {
        return new OrderRefund();
    }

    /**
     * @return OrderRefund
     */
    protected function orderRefund()
    {
        return $this->refundModel()->uniacid();
    }


    public function index()
    {

        return view('refund.after-sales.list', [
            'data' => json_encode($this->viewData())
        ])->render();
    }

    /**
     * @return array
     */
    protected function viewData():array
    {
        return [];
    }

    public function getList()
    {

        $search = request()->input('search');


        $orderRefundModel = $this->orderRefund()->backendSearch($search);

        $count['total_price'] = $orderRefundModel->sum('yz_order_refund.price');

        $page = $orderRefundModel->orderBy('yz_order_refund.id', 'desc')->paginate(15);

        $count['total'] = $page->total();

        $data['count'] = $count;


        $page->map(function ($refund) {
            $refund->order->setAppends(['status_name','pay_type_name','fixed_button']);
//            $refund->order->makeHidden(['backend_button_models','row_bottom']);
        });


        $data['list'] = $page->toArray();
        $data['extra_param'] = $this->mergeExtraParam() ?: [];

        return $this->successJson('list', $data);
    }

    public function detail()
    {

        $id = intval(request()->input('id'));

        if (empty($id)) {
            throw new AppException('参数为空');
        }

        $refund = $this->refundModel()::detail($id);

        if (!$refund) {
            throw new AppException('售后记录不存在');
        }

        $refund->order->dispatch = null;
        if(!$refund->order->expressmany->isEmpty() && $refund->order->status>1){
            $order = $refund->order;
            $dispatch = [];
            //兼容以前的 因为批量发货并不会把快递id赋值给订单商品
            if($order->is_all_send_goods==0){
                $express = $order->express->getExpress($order->express->express_code, $order->express->express_sn);
                $dispatch[0]['order_express_id'] = $order->expressmany[0]->id;
                $dispatch[0]['express_sn'] =  $order->expressmany[0]->express_sn;
                $dispatch[0]['company_name'] = $order->expressmany[0]->express_company_name;
                $dispatch[0]['data'] = $express['data'];
                $dispatch[0]['thumb'] = $order->hasManyOrderGoods[0]->thumb;
                $dispatch[0]['tel'] = '95533';
                $dispatch[0]['status_name'] = $express['status_name'];
                $dispatch[0]['count'] = count($order->hasManyOrderGoods);
                $dispatch[0]['goods'] = $order->hasManyOrderGoods;
            }else{
                $expressmany = $order->expressmany;
                foreach ($expressmany as $k=>$v){
                    $express = $order->express->getExpress($v->express_code, $v->express_sn);
                    $dispatch[$k]['order_express_id'] = $v->id;
                    $dispatch[$k]['express_sn'] = $v->express_sn;
                    $dispatch[$k]['company_name'] = $v->express_company_name;
                    $dispatch[$k]['data'] = $express['data'];
                    $dispatch[$k]['thumb'] = $v->ordergoods[0]->thumb;
                    $dispatch[$k]['tel'] = '95533';
                    $dispatch[$k]['status_name'] = $express['status_name'];
                    $dispatch[$k]['count'] = count($v['ordergoods']);
                    $dispatch[$k]['goods'] = $v['ordergoods'];
                }
            }
            $refund->order->dispatch = $dispatch;
        }

        $refund->order->setAppends(['status_name','pay_type_name','fixed_button']);



        //如当前售后不是订单正在进行中的就不显示售后操作
        $refund->backend_button_models = [];
        if ($refund->order->refund_id && $refund->order->refund_id == $refund->id) {
            $refund->backend_button_models = $refund->getBackendButtonModels();
        }

        $refund->refundSteps =  $refund->getBackendRefundSteps();

        $data['refund'] = $refund->toArray();


        return view('refund.after-sales.detail', [
            'data' => json_encode($this->detailViewData($data))
        ])->render();
    }

    /**
     * @param $data
     * @return array
     */
    public function detailViewData($data):array
    {

        return $data;
    }



    public function export()
    {
        $search = request()->input('search');

        $orderRefundModel = $this->orderRefund()->backendSearch($search);

        $list = $orderRefundModel->orderBy('yz_order_refund.id', 'desc')->get();


//        $list = $list->map(function ($refund) {
//            $refund->order->setAppends(['status_name','pay_type_name','fixed_button']);
//        });


        if ($list->isEmpty()) {
            throw new ShopException('没有可导出的售后记录');
        }

        foreach ($list as $key => $item) {

            $export_data[] = [
                'order_sn' => $item->order->order_sn,
                'refund_sn' => $item->refund_sn,
                'price' => $item->price,
                'uid' =>  $item->uid,
                'nickname' =>  $this->getNickname($item->hasOneMember->nickname),
                'order_type_name' => $item->order_type_name,
                'refund_type_name' => $item->refund_type_name,
                'status_name' =>  $item->status_name,
                'goods' => $item->refundOrderGoods->toArray(),
                'create_time' => $item->create_time->toDateTimeString(),
                'refund_time' => $item->refund_time ? $item->refund_time->toDateTimeString() : '',
                'reason' => $item->reason,
                'part_refund_name' => $item->part_refund_name,
            ];
        }

        $file_name = date('Ymdhis', time()) . '售后列表导出.xls';

        return \app\exports\ExcelService::customExport(new AfterSalesExport($export_data),$file_name);
    }

    protected function getNickname($nickname)
    {
        if (substr($nickname, 0, strlen('=')) === '=') {
            $nickname = '，' . $nickname;
        }


        //去除微信昵称中会带有emoji和特殊符号（颜文字等）否则执行到该值时，后续数据导致空白丢失
        $nickname = preg_replace_callback('/./u',function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $nickname);

        return $nickname;
    }

    protected function mergeExtraParam()
    {
        $extraParam = [
            'package_deliver' => app('plugins')->isEnabled('package-deliver'),
            'team_dividend' => app('plugins')->isEnabled('team-dividend'),
            'printer' => (app('plugins')->isEnabled('printer') || app('plugins')->isEnabled('more-printer'))
        ];

        return $extraParam;
    }


}
