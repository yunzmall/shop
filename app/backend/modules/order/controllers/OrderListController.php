<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/1
 * Time: 9:56
 */

namespace app\backend\modules\order\controllers;

use app\backend\modules\goods\models\GoodsOption;
use app\backend\modules\member\models\MemberParent;
use app\backend\modules\order\models\VueOrder;
use app\backend\modules\order\models\OrderGoods;
use app\backend\modules\order\services\OrderViewService;
use app\common\components\BaseController;

use app\common\helpers\PaginationHelper;
use app\common\services\ExportService;
use app\common\services\member\level\LevelUpgradeService;
use app\common\services\OrderExportService;
use Illuminate\Support\Facades\DB;
use Yunshop\Diyform\models\DiyformDataModel;
use Yunshop\Diyform\models\DiyformTypeModel;
use Yunshop\Diyform\models\OrderGoodsDiyForm;
use Yunshop\TeamDividend\models\TeamDividendLevelModel;
use Yunshop\Exhelper\common\models\ExhelperPanel;
use Yunshop\Exhelper\common\models\ExhelperSys;
use Yunshop\Exhelper\common\models\SendUser;


class OrderListController  extends BaseController
{
    /**
     * 页码
     */
    const PAGE_SIZE = 10;


    protected $exportRoute = 'order.order-list.index';

    /**
     * @var VueOrder
     */
    protected $orderModel;

    public function preAction()
    {
        parent::preAction();

    }

    protected function getOrder()
    {

        $model = VueOrder::uniacid();


//        //todo 暂时的，只显示有设置config配置的订单
//        foreach ((new OrderViewService())->getOrderType() as $orderType) {
//            $pluginIds[] = $orderType['plugin_id'];
//        }
//        if ($pluginIds) {
//            $model->whereIn('plugin_id', $pluginIds);
//        }

        return $model;
            //->where('plugin_id', 40);
    }

    protected function setOrderModel()
    {
        $search = request()->input('search');
        $code = request()->input('code');
        return $this->getOrder()->statusCode($code)->orders($search);
    }

    protected function orderModel()
    {
        if(isset($this->orderModel)) {
            return $this->orderModel;
        }

        return  $this->orderModel = $this->setOrderModel();
    }


    protected function getData($code = '')
    {
        $data = [
            'code' => $code,
            'listUrl' => '', //订单查询路由
            'commonPartUrl' => '', //订单查询路由
            'exportUrl' => '', //订单导出路由
            'detailUrl' => '', //订单详情
        ];

        if ($extraData = $this->mergeExtraData()) {
            $data = array_merge($data, $extraData);
        }

        //插件参数
        $extraParam = $this->mergeExtraParam();
        $data['extraParam'] = $extraParam?:[];


        $data['expressCompanies'] = \app\common\repositories\ExpressCompany::create()->all();

        return ['data'=> json_encode($data)];
    }

    protected function mergeExtraData()
    {

    }

    protected function mergeExtraParam()
    {
        $extraParam = [
            'package_deliver' => app('plugins')->isEnabled('package-deliver'),
            'team_dividend' => app('plugins')->isEnabled('team-dividend'),
        ];

        return $extraParam;
    }


    //此方法多余了
    public function commonPart()
    {
        //插件参数
        $extraParam = $this->mergeExtraParam();

        $info['extraParam'] = $extraParam?:[];

        $data['expressCompanies'] = \app\common\repositories\ExpressCompany::create()->all();

        return $this->successJson('commonPart', $info);
    }

    public function getList()
    {

        $sort = request()->search['sort'];


        if ($sort == 1) {
            $condition['order_by'][] = [$this->orderModel()->getModel()->getTable() . '.uid', 'desc'];
            $condition['order_by'][] = [$this->orderModel()->getModel()->getTable() . '.id', 'desc'];
        }


        $orderModel = $this->orderModel();

        $count['total_price'] = $orderModel->sum('yz_order.price');
        $count['dispatch_price'] = $orderModel->sum('yz_order.dispatch_price');
        $build = $orderModel;

        if ($sort == 1) {
            foreach ($condition['order_by'] as $item) {
                $build->orderBy(...$item);
            }
        } else {
            $build->orderBy($this->orderModel()->getModel()->getTable() . '.id', 'desc');
        }

        $page = $build->paginate(self::PAGE_SIZE);

        /**
         * todo 为了不在模型的 $appends 属性加动态显示
         */
        foreach ($page as $item){
            $item->fixed_button = $item->fixed_button;
            $item->top_row = $item->top_row;
        }


        $count['total'] = $page->total();


        $list = $page->toArray();


        foreach ($list['data'] as &$value) {
            $total = 0;
            foreach ($value['has_many_order_goods'] as $v) {
                $total += $v['total'];
            }
            
            $value['order_goods_total'] = $total;
        }

        $data = [
            'list' => $list,
            'count' => $count,
        ];


        return $this->successJson('list', $data);
    }
    
    public function getSynchro()
    {
	    //判断是否开启了同步运单号
	    $synchro = 0;
	    if (app('plugins')->isEnabled('exhelper')){
		    $set = ExhelperSys::uniacid()->first();
		    if ($set){//判断是否填了快递助手信息
			    $send = SendUser::uniacid()->where('isdefault', 1)->first();
			    $panel = ExhelperPanel::uniacid()->where('isdefault', 1)->first();
			
			    if ($send && $panel){//判断是否有默认发货人和默认模板
				    $synchro = 1;//开启同步运单号
			    }else{
				    $synchro = 0;
			    }
		    }else{
			    $synchro = 0;
		    }
	    }
	    return $this->successJson('ok', $synchro);
    }
    
    
    /**
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        //$a = (new \app\backend\modules\order\services\OrderViewService())->importVue();

        //(new \app\backend\modules\order\services\OrderViewService())->topRowShow();

        return view('order.vue-list', $this->getData())->render();
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function waitPay()
    {
        return view('order.vue-list', $this->getData('waitPay'))->render();
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function waitSend()
    {
        return view('order.vue-list',  $this->getData('waitSend'))->render();
    }

    /**
     * 催发货
     * @return string
     */
    public function expeditingSend()
    {
        return view('order.vue-list', $this->getData('expeditingSend'));
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function waitReceive()
    {
        return view('order.vue-list',  $this->getData('waitReceive'))->render();
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function completed()
    {
        return view('order.vue-list', $this->getData('completed'))->render();
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function cancelled()
    {
        return view('order.vue-list', $this->getData('cancelled'))->render();

    }


    public function export()
    {
        $export_type = request()->input('export_type');
        $template = request()->input('template');

        if ($export_type == 1) {
            $this->baseExport($template);
        } elseif ($export_type == 2) {
            $this->directExport($template);
        }

    }


    public function baseExport($template)
    {

        set_time_limit(30);
        $export_page = request()->export_page ? request()->export_page : 1;
        //清除之前没有导出的文件
        if ($export_page == 1) {
            $fileNameArr = file_tree(storage_path('exports'));
            foreach ($fileNameArr as $val) {
                if (file_exists(storage_path('exports/' . basename($val)))) {
                    unlink(storage_path('exports/') . basename($val)); // 路径+文件名称
                }
            }
        }
        $orders = $this->orderModel()->with(['discounts', 'deductions'])->orderBy($this->orderModel()->getModel()->getTable() . '.id', 'desc');
        $export_model = new OrderExportService($orders, $export_page);

        if (!$export_model->builder_model->isEmpty()) {
            $file_name = date('Ymdhis', time()). '订单导出' .$export_page;//返现记录导出
            $export_data[0] = $template == 2 ? $this->getColumns() : $this->getColumnsV1();
            foreach ($export_model->builder_model->toArray() as $key => $item) {
                $address = explode(' ', $item['address']['address']);
                $fistOrder = $item['has_many_first_order'] ? '首单' : '';

                if ($item['dispatch_type_id'] == \app\common\models\DispatchType::PACKAGE_DELIVER && app('plugins')->isEnabled('package-deliver')) {

                    $deliverOrder = \Yunshop\PackageDeliver\model\PackageDeliverOrder::where('order_id', $item['id'])
                        ->with(['hasOneDeliver', 'hasOneDeliverClerk'])
                        ->first();

                    $deliver_name = $deliverOrder->deliver_id.'-'.$deliverOrder->hasOneDeliver->deliver_name;

                    if ($deliverOrder->hasOneDeliverClerk) {
                        $package_deliver_name = '[UID:'.$deliverOrder->hasOneDeliverClerk->uid.']'.$deliverOrder->hasOneDeliverClerk->realname;

                    } elseif($item['status'] == 3) {
                        $package_deliver_name = '后台确认';
                    } else {
                        $package_deliver_name = '';
                    }
                } else {
                    $deliver_name = '';
                    $package_deliver_name = '';
                }


                $goods = [];
                if ($template == 2){
                    $form = $this->getFormDataByOderId($item['id']);

                    foreach ($item['has_many_order_goods'] as $v) {
                        $goods[] = [
                            $v['title'],
                            $v['goods_option_title'],
                            $v['goods_sn'],
                            $v['total'],
                            isset($form[$v['goods_id']]) ? $form[$v['goods_id']] : '',
                        ];
                    }
                }else{
                    $goods[] = [
                        $this->getGoods($item, 'goods_title'),
                        $this->getGoods($item, 'goods_sn'),
                        $this->getGoods($item, 'total'),
                    ];
                }

                $export_data[$key + 1] = [
                    $item['id'],
                    $item['order_sn'],
                    $item['has_one_order_pay']['pay_sn'],
                    $item['belongs_to_member']['uid'],
                    $this->getNickname($item['belongs_to_member']['nickname']),
                    $item['address']['realname'],
                    $item['address']['mobile'],
                    !empty($address[0]) ? $address[0] : '',
                    !empty($address[1]) ? $address[1] : '',
                    !empty($address[2]) ? $address[2] : '',
                    $item['address']['address'],
                    $goods,
                    $item['pay_type_name'],
                    $this->getExportDiscount($item, 'deduction'),
                    $this->getExportDiscount($item, 'coupon'),
                    $this->getExportDiscount($item, 'enoughReduce'),
                    $this->getExportDiscount($item, 'singleEnoughReduce'),
                    $item['goods_price'],
                    $item['dispatch_price'],
                    $item['price'],
                    $this->getGoods($item, 'cost_price'),
                    $item['status_name'],
                    $item['create_time'],
                    !empty(strtotime($item['pay_time'])) ? $item['pay_time'] : '',
                    !empty(strtotime($item['send_time'])) ? $item['send_time'] : '',
                    !empty(strtotime($item['finish_time'])) ? $item['finish_time'] : '',
                    $item['express']['express_company_name'],
                    '[' . $item['express']['express_sn'] . ']',
                    $item['has_one_order_remark']['remark'],
                    $item['note'],
                    $fistOrder,
                    $item['belongs_to_member']['realname'],
                    ' '.$item['belongs_to_member']['idcard'],
                    $package_deliver_name,
                    $deliver_name,
                    isset($item['has_one_refund_apply']) ? $item['has_one_refund_apply']['refund_type_name'] . ':' . $item['has_one_refund_apply']['status_name'] : ''
                ];
            }
            $export_model->export($file_name, $export_data, $this->exportRoute);
        }
    }

    public function directExport($template)
    {
        $export_page = request()->export_page ? request()->export_page : 1;
        $orders = $this->orderModel()->with([
            'discounts',
            'deductions',
            'hasManyParentTeam' => function($q) {
                if (app('plugins')->isEnabled('team-dividend')) {
                    $q->whereHas('hasOneTeamDividend')
                        ->with(['hasOneTeamDividend' => function($q) {
                            $q->with(['hasOneLevel']);
                        }])
                        ->with('hasOneMember')
//                        ->orderBy('id', 'desc')
                        ->orderBy('level', 'asc');
                } else {
                    $q->with('hasOneMember')
                        ->orderBy('level', 'asc');
                }
            },
        ]);
        $export_model = new OrderExportService($orders, $export_page);

        $levelId = [];
        $level_name = [];
        if (app('plugins')->isEnabled('team-dividend')) {
            $team_list = TeamDividendLevelModel::getList()->get();
            foreach ($team_list as $level) {
                $level_name[] = $level->level_name;
                $levelId[] = $level->id;
            }
        }

        if (!$export_model->builder_model->isEmpty()) {
            $file_name = date('Ymdhis', time()) . '订单导出' .$export_page;//返现记录导出
//            $export_data[0] = $template == 2 ? $this->getColumns() : $this->getColumnsV1();
            //处理表头
            $head = $template == 2 ? $this->getColumns() : $this->getColumnsV1();
            $export_data[0] = array_merge($level_name, $head);

            foreach ($export_model->builder_model->toArray() as $key => $item) {

                $level = $this->getLevel($item, $levelId);

                $export_data[$key + 1] = $level;

                $address = explode(' ', $item['address']['address']);



                $goods = [];
                if ($template == 2){
                    $form = $this->getFormDataByOderId($item['id']);

                    foreach ($item['has_many_order_goods'] as $v) {
                        $goods[] = [
                            $v['title'],
                            $v['goods_option_title'],
                            $v['goods_sn'],
                            $v['total'],
                            isset($form[$v['goods_id']]) ? $form[$v['goods_id']] : '',
                        ];
                    }
                }else{
                    $goods[] = [
                        $this->getGoods($item, 'goods_title'),
                        $this->getGoods($item, 'goods_sn'),
                        $this->getGoods($item, 'total'),
                    ];
                }

                array_push($export_data[$key + 1],
                    $item['id'],
                    $item['order_sn'],
                    $item['has_one_order_pay']['pay_sn'],
                    $item['belongs_to_member']['uid'],
                    $this->getNickname($item['belongs_to_member']['nickname']),
                    $item['address']['realname'],
                    $item['address']['mobile'],
                    !empty($address[0]) ? $address[0] : '',
                    !empty($address[1]) ? $address[1] : '',
                    !empty($address[2]) ? $address[2] : '',
                    $item['address']['address'],
                    $goods,
                    $item['pay_type_name'],
                    $this->getExportDiscount($item, 'deduction'),
                    $this->getExportDiscount($item, 'coupon'),
                    $this->getExportDiscount($item, 'enoughReduce'),
                    $this->getExportDiscount($item, 'singleEnoughReduce'),
                    $item['goods_price'],
                    $item['dispatch_price'],
                    $item['price'],
                    $this->getGoods($item, 'cost_price'),
                    $item['status_name'],
                    $item['create_time'],
                    !empty(strtotime($item['pay_time'])) ? $item['pay_time'] : '',
                    !empty(strtotime($item['send_time'])) ? $item['send_time'] : '',
                    !empty(strtotime($item['finish_time'])) ? $item['finish_time'] : '',
                    $item['express']['express_company_name'],
                    '[' . $item['express']['express_sn'] . ']',
                    $item['has_one_order_remark']['remark'],
                    $item['note'],
                    isset($item['has_one_refund_apply']) ? $item['has_one_refund_apply']['refund_type_name'] . ':' . $item['has_one_refund_apply']['status_name'] : ''
                );
            }
            $export_model->export($file_name, $export_data, $this->exportRoute, 'direct_export');
        }
    }

    public function getFormDataByOderId($order_id)
    {
        $result = [];
        $set = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order.order_detail.diyform');
        if(!$set){
            return $result;
        }
        $orderGoods = \app\common\models\OrderGoods::where('order_id', $order_id)->get()->toArray();
        $orderGoodsIds = array_column($orderGoods, 'id');
        $orderGoods = array_column($orderGoods, null, 'id');
        $diyForms = OrderGoodsDiyForm::whereIn('order_goods_id', $orderGoodsIds)->get()->toArray();
        $dataIds = array_column($diyForms, 'diyform_data_id');
        $diyForms = array_column($diyForms, null, 'diyform_data_id');
        $datas = DiyformDataModel::whereIn('id', $dataIds)->get()->toArray();
        $item = [];
        foreach ($datas as $detail) {
            if ($detail) {
                $form = DiyformTypeModel::find($detail['form_id']);
            }
            $fields = iunserializer($form['fields']);
            foreach ($detail['form_data'] as $k => $v){
                if ($fields[$k]['data_type'] == 5) {
                    continue;
                }
                if(is_array($v)){
                    $v = implode(',', $v);
                }
                $item[] = $fields[$k]['tp_name'] . ':' . $v;
            }
            $result[$orderGoods[$diyForms[$detail['id']]['order_goods_id']]['goods_id']] = implode("\r\n", $item);
        }

        return $result;
    }

    public function getLevel($member, $levelId)
    {
        $data = [];
        foreach ($levelId as $k => $value) {
            foreach ($member['has_many_parent_team'] as $key => $parent) {

                if ($parent['has_one_team_dividend']['has_one_level']['id'] == $value) {
                    $data[$k] = $parent['has_one_member']['nickname'].' '.$parent['has_one_member']['realname'].' '.$parent['has_one_member']['mobile'];
                    break;
                }
            }
            $data[$k] = $data[$k] ?: '';
        }

        return $data;
    }

    protected function getColumns()
    {
        return ["订单id", "订单编号", "支付单号", "会员ID", "粉丝昵称", "会员姓名", "联系电话", '省', '市', '区', "收货地址",
            "商品名称", "商品规格", "商品编码", "商品数量", "自定义表单", "支付方式", '抵扣金额', '优惠券优惠', '全场满减优惠',
            '单品满减优惠', "商品小计", "运费", "应收款", "成本价", "状态", "下单时间", "付款时间", "发货时间", "完成时间",
            "快递公司", "快递单号", "订单备注", "用户备注", "首单", "真实姓名", "身份证", "核销员", "附加", "订单提示状态"
        ];
    }

    protected function getColumnsV1()
    {
        return ["订单id","订单编号", "支付单号", "会员ID", "粉丝昵称", "收货人姓名", "联系电话", '省', '市', '区', "收货地址",
            "商品名称", "商品编码", "商品数量", "支付方式", '抵扣金额', '优惠券优惠', '全场满减优惠', '单品满减优惠', "商品小计",
            "运费", "应收款", "成本价", "状态", "下单时间", "付款时间", "发货时间", "完成时间", "快递公司", "快递单号", "订单备注",
            "用户备注", "首单" , "真实姓名" , "身份证", '核销员', '附加', '订单提示状态'];
    }


    protected function getExportDiscount($order, $key)
    {
        $export_discount = [
            'deduction' => 0,    //抵扣金额
            'coupon' => 0,    //优惠券优惠
            'enoughReduce' => 0,  //全场满减优惠
            'singleEnoughReduce' => 0,    //单品满减优惠
        ];

        foreach ($order['discounts'] as $discount) {

            if ($discount['discount_code'] == $key) {

                $export_discount[$key] = $discount['amount'];
            }
        }

        if (!$export_discount['deduction']) {

            foreach ($order['deductions'] as $k => $v) {

                $export_discount['deduction'] += $v['amount'];
            }
        }

        return $export_discount[$key];
    }

    protected function getGoods($order, $key)
    {
        $goods_title = '';
        $goods_sn = '';
        $total = '';
        $cost_price = 0;
        foreach ($order['has_many_order_goods'] as $goods) {
            $res_title = $goods['title'];
            $res_title = str_replace('-', '，', $res_title);
            $res_title = str_replace('+', '，', $res_title);
            $res_title = str_replace('/', '，', $res_title);
            $res_title = str_replace('*', '，', $res_title);
            $res_title = str_replace('=', '，', $res_title);

            if ($goods['goods_option_title']) {
                $res_title .= '[' . $goods['goods_option_title'] . ']';
            }
            $order_goods = OrderGoods::find($goods['id']);
            if ($order_goods->goods_option_id) {
                $goods_option = GoodsOption::find($order_goods->goods_option_id);
                if ($goods_option) {
                    $goods_sn .= '【' . $goods_option->goods_sn . '】';
                }
            } else {
                $goods_sn .= '【' . $goods['goods_sn'] . '】';
            }

            $goods_title .= '【' . $res_title . '*' . $goods['total'] . '】';
            $total .= '【' . $goods['total'] . '】';
            $cost_price += $goods['goods_cost_price'];
        }
        $res = [
            'goods_title' => $goods_title,
            'goods_sn' => $goods_sn,
            'total' => $total,
            'cost_price' => $cost_price
        ];
        return $res[$key];
    }

    protected function getNickname($nickname)
    {
        if (substr($nickname, 0, strlen('=')) === '=') {
            $nickname = '，' . $nickname;
        }
        return $nickname;
    }

}