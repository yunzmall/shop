<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/1
 * Time: 9:56
 */

namespace app\backend\modules\order\controllers;

use app\backend\modules\goods\models\Category;
use app\backend\modules\goods\models\GoodsOption;
use app\backend\modules\member\models\MemberParent;
use app\backend\modules\order\models\VueOrder;
use app\backend\modules\order\models\OrderGoods;
use app\backend\modules\order\services\OrderViewService;
use app\common\components\BaseController;

use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\services\ExportService;
use app\common\services\member\level\LevelUpgradeService;
use app\common\services\OrderExportService;
use app\frontend\modules\order\services\OrderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Yunshop\Diyform\models\DiyformDataModel;
use Yunshop\Diyform\models\DiyformTypeModel;
use Yunshop\Diyform\models\OrderGoodsDiyForm;
use Yunshop\GoodsSource\common\models\GoodsSet;
use Yunshop\GoodsSource\common\models\GoodsSource;
use Yunshop\MorePrinter\common\services\OrderPrintService;
use Yunshop\PackageDelivery\models\DeliveryOrder;
use Yunshop\Printer\common\services\NewPrintingService;
use Yunshop\StoreCashier\common\models\StoreDelivery;
use Yunshop\TeamDividend\models\TeamDividendLevelModel;
use Yunshop\Exhelper\common\models\ExhelperPanel;
use Yunshop\Exhelper\common\models\ExhelperSys;
use Yunshop\Exhelper\common\models\SendUser;


class OrderListController extends BaseController
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

        return $model;
    }

    protected function setOrderModel()
    {
        $search = request()->input('search');
        $code = request()->input('code');
        return $this->getOrder()->statusCode($code)->orders($search);
    }

    protected function orderModel()
    {
        if (isset($this->orderModel)) {
            return $this->orderModel;
        }

        return $this->orderModel = $this->setOrderModel();
    }


    protected function getData($code = '')
    {
        $data = [
            'code'          => $code,
            'listUrl'       => '', //订单查询路由
            'commonPartUrl' => '', //订单查询路由
            'exportUrl'     => '', //订单导出路由
            'detailUrl'     => '', //订单详情
        ];
        $source_status = false;
        $source_is_open = \Setting::get('plugin.goods_source.is_open');
        if (app('plugins')->isEnabled('goods-source') && (is_null($source_is_open) || $source_is_open)) {
            $source_status = true;
        }
        if ($source_status) {
            $source_list = GoodsSource::uniacid()->select(['id', 'source_name'])->get()->toArray();
        } else {
            $source_list = new Collection();
        }
        if ($extraData = $this->mergeExtraData()) {
            $data = array_merge($data, $extraData);
        }

        //插件参数
        $extraParam = $this->mergeExtraParam();
        $data['extraParam'] = $extraParam ?: [];
        $data['is_source_open'] = $source_status ? 1 : 0;
        $data['source_list'] = $source_list ?: [];

        $data['expressCompanies'] = \app\common\repositories\ExpressCompany::create()->all();
        return ['data' => json_encode($data)];
    }

    protected function mergeExtraData()
    {
    }

    protected function mergeExtraParam()
    {
        $extraParam = [
            'package_deliver' => app('plugins')->isEnabled('package-deliver'),
            'team_dividend'   => app('plugins')->isEnabled('team-dividend'),
            'printer'         => (app('plugins')->isEnabled('printer') || app('plugins')->isEnabled('more-printer'))
        ];

        return $extraParam;
    }

    public function orderPrinter()
    {
        try {
            $order_id = request()->order_id;
            $order = Order::find($order_id);
            if (!$order) {
                throw new \Exception('订单未找到');
            }
            //打印机
            if (app('plugins')->isEnabled('printer')) {
                \app\common\modules\shop\ShopConfig::current()->set('printer_owner', [
                    'owner'    => 1,
                    'owner_id' => 0
                ]);
                $print_type = 2;
                $code = '商城支付打印';
                if ($order->status == 0) {
                    $print_type = 1;
                    $code = '商城下单打印';
                }
                $NewPrintingService = new NewPrintingService($order, $print_type, $code);
                if ($NewPrintingService->verify()) {
                    $NewPrintingService->handle();
                    return $this->successJson('ok');
                } else {
                    return $this->errorJson('打印失败，请配置打印机！');
                }
            }

            if (app('plugins')->isEnabled('more-printer')) {
                $print_type = 2;
                if ($order->status == 0) {
                    $print_type = 1;
                }
                $order_service = new OrderPrintService($order, $print_type);
                $order_service->newPrinting();
                return $this->successJson('ok');
            }
            throw new \Exception('未开启打印机插件');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //此方法多余了
    public function commonPart()
    {
        //插件参数
        $extraParam = $this->mergeExtraParam();

        $info['extraParam'] = $extraParam ?: [];

        $data['expressCompanies'] = \app\common\repositories\ExpressCompany::create()->all();

        return $this->successJson('commonPart', $info);
    }

    public function getList()
    {
        $sort = request()->search['sort'];
        $search = request()->input('search');

        if ($sort == 1) {
            $condition['order_by'][] = [$this->orderModel()->getModel()->getTable() . '.uid', 'desc'];
            $condition['order_by'][] = [$this->orderModel()->getModel()->getTable() . '.id', 'desc'];
        }


        $orderModel = $this->orderModel();
        if (app('plugins')->isEnabled('order-inventory') && \Yunshop\OrderInventory\services\SetService::pluginIsOpen(
            )) {
            //不显示存货订单
            $orderModel = \Yunshop\OrderInventory\services\InventoryService::orderListWhere($orderModel);
        }

        if (app('plugins')->isEnabled('invoice')) {
            $orderModel = $orderModel->with('orderInvoice');
            if ($search['is_invoice']) {
                $orderModel->whereHas('orderInvoice', function ($query) use ($search) {
                    return $query->where('apply', $search['is_invoice']);
                });
            }
        }
        if ($search['source_id']) {
            $set_goods_id = GoodsSet::where('source_id', $search['source_id'])->pluck('goods_id')->all();
            $order_ids = OrderGoods::uniacid()->whereIn('goods_id', $set_goods_id)->pluck('order_id')->unique()->all();
            $orderModel->whereIn('yz_order.id', $order_ids);
        }
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


        $page->map(function ($order) {
            /**
             * todo 为了不在模型的 $appends 属性加动态显示
             */
            $order->fixed_button = $order->fixed_button;
            $order->top_row = $order->top_row;

            $order->part_refund = (!$order->refund_id && RefundApply::getAfterSales($order->id)->count()) ? 1 : 0;

            // 查询乐刷订单, 然后显示是否有退款不足的情况
            if (in_array($order->pay_type_id, [85, 86, 87])) {
                $refundRecords = \Yunshop\LeshuaPay\models\RefundRecords::where('order_id', $order->id)->first();
                $order->leshua_refund_error_msg = $refundRecords->msg ?? '';
            }
        });

        $source_status = false;
        $source_is_open = \Setting::get('plugin.goods_source.is_open');
        if (app('plugins')->isEnabled('goods-source') && (is_null($source_is_open) || $source_is_open)) {
            $source_status = true;
        }
        if ($source_status) {
            $source_list = GoodsSource::uniacid()->select(['id', 'source_name'])->get();
        } else {
            $source_list = new Collection();
        }

        $count['total'] = $page->total();

        $list = $page->toArray();

        $data = [
            'list'           => $list,
            'count'          => $count,
            'is_source_open' => $source_status ? 1 : 0,
            'source_list'    => $source_list,
        ];


        return $this->successJson('list', $data);
    }

    public function getSynchro()
    {
        //判断是否开启了同步运单号
        $synchro = 0;
        if (app('plugins')->isEnabled('exhelper')) {
            $set = ExhelperSys::uniacid()->first();
            if ($set) {//判断是否填了快递助手信息
                $send = SendUser::uniacid()->where('isdefault', 1)->first();
                $panel = ExhelperPanel::uniacid()->where('isdefault', 1)->first();

                if ($send && $panel) {//判断是否有默认发货人和默认模板
                    $synchro = 1;//开启同步运单号
                } else {
                    $synchro = 0;
                }
            } else {
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
        return view('order.vue-list', $this->getData('waitSend'))->render();
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
        return view('order.vue-list', $this->getData('waitReceive'))->render();
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
        if (request()->search['order_status']) {
            $search = request()->search;
            $search['order_status'] = explode(',', $search['order_status']);
            request()->offsetSet('search', $search);
        }
        if ($export_type == 1) {
            $this->baseExport($template);
        } elseif ($export_type == 2) {
            $this->directExport($template);
        }
    }

    public function batchSend()
    {
        $order_ids = $this->orderModel()->pluck('id');

        $send_data = request()->batch_send;
        $i = 0;
        foreach ($order_ids as $order_id) {
            try {
                $param = [
                    "dispatch_type_id" => $send_data['dispatch_type_id'],
                    "express_code"     => $send_data['express_code'],
                    "express_sn"       => $send_data['express_sn'],
                    "order_id"         => $order_id,
                ];
                \app\frontend\modules\order\services\OrderService::orderSend($param);
            } catch (\Exception $e) {
                $i++;
            }
        }

        return $this->successJson('一键发货成功，失败条数' . $i . '(有退款订单不能发货)');
    }

    protected function extraExportValue($item)
    {
        return [];
    }

    protected function extraExportColumn()
    {
        return [];
    }


    public function baseExport($template)
    {
        ini_set('memory_limit', -1); //订单里的商品和商品的分类过多会造成内存溢出
        set_time_limit(60);
        $export_page = request()->export_page ? request()->export_page : 1;
        //清除之前没有导出的文件
        if ($export_page == 1) {
            $dirArr = glob(storage_path('exports') . '/*', GLOB_BRACE);
            foreach ($dirArr as $dir) {
                //大量订单导出的时候耗时长,延迟删除
                if (time() - explode('_', basename($dir))[0] > 300) {
                    $dirName = basename($dir);
                    $fileNameArr = file_tree($dir);
                    foreach ($fileNameArr as $val) {
                        if (file_exists(storage_path('exports/') . $dirName . '/' . basename($val))) {
                            file_delete(storage_path('exports/' . $dirName . '/' . basename($val))); // 路径+文件名称
                        }
                    }
                    if (is_dir(storage_path('exports/') . $dirName)) {
                        rmdir(storage_path('exports/') . $dirName);
                    }
                }
            }
        }
        $order_model = $this->orderModel();
        if (request()->search['source_id']) {
            $set_goods_id = GoodsSet::where('source_id', request()->search['source_id'])->pluck('goods_id')->all();
            $order_ids = OrderGoods::uniacid()->whereIn('goods_id', $set_goods_id)->pluck('order_id')->unique()->all();
            $order_model->whereIn('yz_order.id', $order_ids);
        }

        $orders = $order_model->with(['discounts', 'deductions', 'orderInvoice'])->orderBy(
            $this->orderModel()->getModel()->getTable() . '.id',
            'desc'
        );
        $columns = $this->getColumns();
        if ($template == 2) {
            if (app('plugins')->isEnabled('package-delivery')) {
                $orders->with([
                    'hasOnePackageDeliveryOrder' => function ($query) {
                        $query->select('order_id', 'buyer_name', 'buyer_mobile');
                    }
                ]);
            }
            if (app('plugins')->isEnabled('package-deliver')) {
                $orders->with([
                    'hasOnePackageDeliverOrder' => function ($query) {
                        $query->with([
                            'hasOneDeliver' => function ($query) {
                                $query->select('id', 'deliver_name', 'deliver_mobile', 'realname');
                            }
                        ])->select('deliver_id', 'order_id', 'deliver_name');
                    }
                ]);
            }
            if (app('plugins')->isEnabled('goods-source')) {
                $orders->with([
                    'hasManyOrderGoods' => function ($query) {
                        $query->with([
                            'goodsSource' => function ($query) {
                                $query->with(['source']);
                            }
                        ]);
                    }
                ]);
            }
        }

        //导出(新)调整为250个订单一页
        $export_model = new OrderExportService($orders, $export_page, $template == 2 ? 250 : 500);

        if (!$export_model->builder_model->isEmpty()) {
            $category = new Category();
            $file_name = date('Ymdhis', time()) . '订单导出' . $export_page;//返现记录导出
            $export_data[0] = $template == 2 ? $columns : $this->getColumnsV1();
            $export_data[0] = array_merge($export_data[0], $this->extraExportColumn());

            foreach ($export_model->builder_model->toArray() as $key => $item) {
                $address = explode(' ', $item['address']['address']);
                $fistOrder = $item['has_many_first_order'] ? '首单' : '';


                $refundedCollect = \app\common\models\refund\RefundApply::getAfterSales($item['id'])
                    ->orderBy('id', 'desc')
                    ->get();

                if ($refundedCollect->isEmpty()) {
                    $refund_name = '';
                } else {
                    $refund_name = $refundedCollect->first(
                    )->part_refund == \app\common\models\refund\RefundApply::PART_REFUND ? '部分退款' : '';
                }

                //拼接多包裹快递信息
                $expressInfo = $this->getExpressString($item['expressmany']);

                $clerk_info = $this->getAuditor($item);
                $goods = [];
                if ($template == 2) {
                    $form = $this->getFormDataByOderId($item['id']);
                    $yzSupplyForm = [];

                    if (app('plugins')->isEnabled('yz-supply')) {
                        $middleground_configuration_ids = \Yunshop\YzSupply\models\MiddlegroundConfiguration::uniacid(
                        )->where('status', 1)->pluck('id');
                        foreach ($middleground_configuration_ids as $id) {
                            try {
                                $suppliers = (new \Yunshop\YzSupply\services\CloudRequestService($id))->getSuppliers();
                            } catch (\Exception $e) {
                                throw new ShopException('中台配置项ID：' . $id . '配置错误,错误信息：' . $e->getMessage());
                            }

                            if ($suppliers['code'] > 0) {
                                continue;
                            }
                            $yzSupplyForm[$id] = array_column($suppliers['data'], 'name', 'id');
                        }
                    }

                    foreach ($item['has_many_order_goods'] as $v) {
                        $goodsRefundedTotal = $v['after_sales']['complete_quantity'];

                        $cate = [];
                        $temp = [
                            $v['title'],
                            $v['goods']['alias'],
                            $v['goods_option_title'],
                            $v['goods_sn'],
                            $v['product_sn'],
                            $v['total'],
                            "{$goodsRefundedTotal}",
                            "" . max($v['total'] - $goodsRefundedTotal, 0),
                        ];

                        foreach ($v['goods']['belongs_to_categorys'] as $val) {
                            $cate[] = $category->getCateOrderByLevel($val['category_ids']);
                        }
                        if (empty($cate)) {
                            $cate = [['', '', '']];
                        }
                        $temp[] = $cate;
                        $temp[] = $v['vip_price'];

                        $temp[] = $form[$v['goods_id']] ? $form[$v['goods_id']] : '';
                        $temp[] = $v['goods_source']['source']['source_name'] ?: "";
                        $temp[] = $this->getExportYzSupplySupplyName($v['goods_id'], $yzSupplyForm) ?: '';//供应商名称
                        $goods[] = $temp;
                    }

                    $export_row_data = [
                        $item['id'],
                        $item['order_sn'],
                        $item['has_one_order_pay']['pay_sn'],
                        $item['belongs_to_member']['uid'],
                        $this->getNickname($item['belongs_to_member']['nickname']) ?: substr_replace(
                            $item['belongs_to_member']['mobile'],
                            '*******',
                            2,
                            7
                        ),
                        //                        $item['address']['realname'],
                        //                        $item['address']['mobile'],
                        $this->getExportRealName($item),
                        $this->getExportMobile($item),
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
                        $this->getGoods($item, 'cost_price', $category),
                        $item['price'],
                        '' . $refundedCollect->sum('price'),
                        '' . max($item['price'] - $refundedCollect->sum('price'), 0),
                        $item['status_name'],
                        $item['create_time'],
                        !empty(strtotime($item['pay_time'])) ? $item['pay_time'] : '',
                        !empty(strtotime($item['send_time'])) ? $item['send_time'] : '',
                        !empty(strtotime($item['finish_time'])) ? $item['finish_time'] : '',
                        $expressInfo['company'],
                        $expressInfo['sn'],
                        $item['has_one_order_remark']['remark'],
                        $this->checkStr($item['note']),
                        $fistOrder,
                        $item['has_many_member_certified']['realname'] ?: $item['belongs_to_member']['realname'],
                        ' ' . $item['belongs_to_member']['idcard'],
                        $clerk_info['auditor'],
                        $clerk_info['additional'],
                        $this->getExportRefundName($item) ?: $refund_name,
                        $this->invoiceType($item['order_invoice']['invoice_type']),
                        // 发票类型
                        ($item['order_invoice']['rise_type'] == 1) ? '个人' : '单位',
                        // 发票抬头
                        empty($item['order_invoice']['collect_name']) ? '' : $item['order_invoice']['collect_name'],
                        // 单位/抬头名称
                        empty($item['order_invoice']['gmf_taxpayer']) ? $item['order_invoice']['company_number'] : $item['order_invoice']['gmf_taxpayer'],
                        // 税号
                        empty($item['order_invoice']['content']) ? '' : $item['order_invoice']['content'],
                        // 发票内容
                        empty($item['order_invoice']['gmf_bank']) ? '' : $item['order_invoice']['gmf_bank'],
                        // 开户银行
                        empty($item['order_invoice']['gmf_bank_admin']) ? '' : $item['order_invoice']['gmf_bank_admin'],
                        // 银行账号
                        empty($item['order_invoice']['gmf_address']) ? '' : $item['order_invoice']['gmf_address'],
                        // 注册地址
                        empty($item['order_invoice']['gmf_mobile']) ? '' : $item['order_invoice']['gmf_mobile'],
                        // 注册电话
                        empty($item['order_invoice']['col_name']) ? '' : $item['order_invoice']['col_name'],
                        // 收票人姓名
                        empty($item['order_invoice']['col_name']) ? '' : $item['order_invoice']['col_mobile'],
                        // 收票人电话
                        empty($item['order_invoice']['col_address']) ? '' : $item['order_invoice']['col_address'],
                        // 收票人地址
                        empty($item['order_invoice']['email']) ? '' : $item['order_invoice']['email'],
                        // 邮箱
                        $this->getExportDeliverName($item),
                        $this->getExportDeliverOwnerName($item),
                        $this->getExportDeliverOwnerMobile($item),
                    ];
                } else {
//                    $goods[] = [
//                        $this->getGoods($item, 'goods_title'),
//                        $this->getGoods($item, 'goods_sn'),
//                        $this->getGoods($item, 'product_sn'),
//                        $this->getGoods($item, 'total'),
//                    ];
                    $goodsInfo = $this->getGoods($item, '', $category);
                    $export_row_data = [
                        $item['id'],
                        $item['order_sn'],
                        $item['has_one_order_pay']['pay_sn'],
                        $item['belongs_to_member']['uid'],
                        $this->getNickname($item['belongs_to_member']['nickname']) ?: substr_replace(
                            $item['belongs_to_member']['mobile'],
                            '*******',
                            2,
                            7
                        ),
                        $item['address']['realname'],
                        $item['address']['mobile'],
                        !empty($address[0]) ? $address[0] : '',
                        !empty($address[1]) ? $address[1] : '',
                        !empty($address[2]) ? $address[2] : '',
                        $item['address']['address'],
                        $goodsInfo['goods_title'],
                        $goodsInfo['alias'],
                        $goodsInfo['goods_sn'],
                        $goodsInfo['product_sn'],
                        $goodsInfo['total'],
                        $goodsInfo['first_cate'],
                        $goodsInfo['second_cate'],
                        $goodsInfo['third_cate'],
                        $goodsInfo['vip_price'],
                        $item['pay_type_name'],
                        $this->getExportDiscount($item, 'deduction'),
                        $this->getExportDiscount($item, 'coupon'),
                        $this->getExportDiscount($item, 'enoughReduce'),
                        $this->getExportDiscount($item, 'singleEnoughReduce'),
                        $item['goods_price'],
                        $item['dispatch_price'],
                        $item['price'],
                        $goodsInfo['cost_price'],
                        $item['status_name'],
                        $item['create_time'],
                        !empty(strtotime($item['pay_time'])) ? $item['pay_time'] : '',
                        !empty(strtotime($item['send_time'])) ? $item['send_time'] : '',
                        !empty(strtotime($item['finish_time'])) ? $item['finish_time'] : '',
                        $expressInfo['company'],
                        $expressInfo['sn'],
                        $item['has_one_order_remark']['remark'],
                        $this->checkStr($item['note']),
                        $fistOrder,
                        !empty($item['has_many_member_certified']['realname']) ? $item['has_many_member_certified']['realname'] : $item['belongs_to_member']['realname'],
                        !empty($item['has_many_member_certified']['idcard']) ? ' ' . $item['has_many_member_certified']['idcard'] : ' ' . $item['belongs_to_member']['idcard'],
                        $clerk_info['auditor'],
                        $clerk_info['additional'],
                        $this->getExportRefundName($item) ?: $refund_name,
                        $this->invoiceType($item['order_invoice']['invoice_type']),
                        // 发票类型
                        ($item['order_invoice']['rise_type'] == 1) ? '个人' : '单位',
                        // 发票抬头
                        empty($item['order_invoice']['collect_name']) ? '' : $item['order_invoice']['collect_name'],
                        // 单位/抬头名称
                        empty($item['order_invoice']['gmf_taxpayer']) ? $item['order_invoice']['company_number'] : $item['order_invoice']['gmf_taxpayer'],
                        // 税号
                        empty($item['order_invoice']['content']) ? '' : $item['order_invoice']['content'],
                        // 发票内容
                        empty($item['order_invoice']['gmf_bank']) ? '' : $item['order_invoice']['gmf_bank'],
                        // 开户银行
                        empty($item['order_invoice']['gmf_bank_admin']) ? '' : $item['order_invoice']['gmf_bank_admin'],
                        // 银行账号
                        empty($item['order_invoice']['gmf_address']) ? '' : $item['order_invoice']['gmf_address'],
                        // 注册地址
                        empty($item['order_invoice']['gmf_mobile']) ? '' : $item['order_invoice']['gmf_mobile'],
                        // 注册电话
                        empty($item['order_invoice']['col_name']) ? '' : $item['order_invoice']['col_name'],
                        // 收票人姓名
                        empty($item['order_invoice']['col_mobile']) ? '' : $item['order_invoice']['col_mobile'],
                        // 收票人电话
                        empty($item['order_invoice']['col_address']) ? '' : $item['order_invoice']['col_address'],
                        // 收票人地址
                        empty($item['order_invoice']['email']) ? '' : $item['order_invoice']['email'],
                        // 邮箱
                    ];
                }
                $export_row_data = array_merge($export_row_data, $this->extraExportValue($item));
                $export_data[$key + 1] = $export_row_data;
            }
//            dd($export_data);
            $export_model->export($file_name, $export_data, $this->exportRoute);
        } else {
            throw new ShopException('没有可导出订单');
        }
    }

    protected function getRefundedGoodsTotal($order_goods_id)
    {
        $a = \app\common\models\refund\RefundGoodsLog::getRefundedGoods($order_goods_id)->sum(
            'yz_order_refund_goods_log.refund_total'
        );

        return $a ?: 0;
    }

    // 过滤运算符
    public function checkStr($str, $fill = ' ')
    {
        if (!$str) {
            return $str;
        }
        $arr = ['='];
        foreach ($arr as $v) {
            if (strpos($str, $v) === 0) {
                return $fill . $str;
            }
        }

        return $str;
    }

    public function directExport($template)
    {
        $export_page = request()->export_page ? request()->export_page : 1;
        $orders = $this->orderModel()->with([
            'discounts',
            'deductions',
            'hasManyParentTeam' => function ($q) {
                if (app('plugins')->isEnabled('team-dividend')) {
                    $q->whereHas('hasOneTeamDividend')
                        ->with([
                            'hasOneTeamDividend' => function ($q) {
                                $q->with(['hasOneLevel']);
                            }
                        ])
                        ->with('hasOneMember')
//                        ->orderBy('id', 'desc')
                        ->orderBy('level', 'asc');
                } else {
                    $q->with('hasOneMember')
                        ->orderBy('level', 'asc');
                }
            },
            'orderInvoice'
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
            $file_name = date('Ymdhis', time()) . '订单导出' . $export_page;//返现记录导出
//            $export_data[0] = $template == 2 ? $this->getColumns() : $this->getColumnsV1();
            //处理表头
            $head = $template == 2 ? $this->getColumns() : $this->getColumnsV1();
            $export_data[0] = array_merge($level_name, $head);
            $export_data[0] = array_merge($export_data[0], $this->extraExportColumn());
            foreach ($export_model->builder_model->toArray() as $key => $item) {
                $clerk_info = $this->getAuditor($item);

                $level = $this->getLevel($item, $levelId);

                $export_data[$key + 1] = $level;

                $address = explode(' ', $item['address']['address']);

                //拼接多包裹快递信息
                $expressInfo = $this->getExpressString($item['expressmany']);


                $goodsInfo = $this->getGoods($item, '');
                $this_data = [
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
                    $goodsInfo['goods_title'],
                    $goodsInfo['alias'],
                    $goodsInfo['goods_sn'],
                    $goodsInfo['product_sn'],
                    $goodsInfo['total'],
                    $goodsInfo['first_cate'],
                    $goodsInfo['second_cate'],
                    $goodsInfo['third_cate'],
                    $goodsInfo['vip_price'],
                    $item['pay_type_name'],
                    $this->getExportDiscount($item, 'deduction'),
                    $this->getExportDiscount($item, 'coupon'),
                    $this->getExportDiscount($item, 'enoughReduce'),
                    $this->getExportDiscount($item, 'singleEnoughReduce'),
                    $item['goods_price'],
                    $item['dispatch_price'],
                    $item['price'],
                    $goodsInfo['cost_price'],
                    $item['status_name'],
                    $item['create_time'],
                    !empty(strtotime($item['pay_time'])) ? $item['pay_time'] : '',
                    !empty(strtotime($item['send_time'])) ? $item['send_time'] : '',
                    !empty(strtotime($item['finish_time'])) ? $item['finish_time'] : '',
                    $expressInfo['company'],
                    $expressInfo['sn'],
                    $item['has_one_order_remark']['remark'],
                    $item['note'],
                    $fistOrder = $item['has_many_first_order'] ? '首单' : '',
                    !empty($item['has_many_member_certified']['realname']) ? $item['has_many_member_certified']['realname'] : $item['belongs_to_member']['realname'],
                    !empty($item['has_many_member_certified']['idcard']) ? ' ' . $item['has_many_member_certified']['idcard'] : ' ' . $item['belongs_to_member']['idcard'],
                    $clerk_info['auditor'],
                    $clerk_info['additional'],
                    $this->getExportRefundName($item),
                    $this->invoiceType($item['order_invoice']['invoice_type']),
                    // 发票类型
                    ($item['order_invoice']['rise_type'] == 1) ? '个人' : '单位',
                    // 发票抬头
                    empty($item['order_invoice']['collect_name']) ? '' : $item['order_invoice']['collect_name'],
                    // 单位/抬头名称
                    empty($item['order_invoice']['gmf_taxpayer']) ? $item['order_invoice']['company_number'] : $item['order_invoice']['gmf_taxpayer'],
                    // 税号
                    empty($item['order_invoice']['content']) ? '' : $item['order_invoice']['content'],
                    // 发票内容
                    empty($item['order_invoice']['gmf_bank']) ? '' : $item['order_invoice']['gmf_bank'],
                    // 开户银行
                    empty($item['order_invoice']['gmf_bank_admin']) ? '' : $item['order_invoice']['gmf_bank_admin'],
                    // 银行账号
                    empty($item['order_invoice']['gmf_address']) ? '' : $item['order_invoice']['gmf_address'],
                    // 注册地址
                    empty($item['order_invoice']['gmf_mobile']) ? '' : $item['order_invoice']['gmf_mobile'],
                    // 注册电话
                    empty($item['order_invoice']['col_name']) ? '' : $item['order_invoice']['col_name'],
                    // 收票人姓名
                    empty($item['order_invoice']['col_mobile']) ? '' : $item['order_invoice']['col_mobile'],
                    // 收票人电话
                    empty($item['order_invoice']['col_address']) ? '' : $item['order_invoice']['col_address'],
                    // 收票人地址
                    empty($item['order_invoice']['email']) ? '' : $item['order_invoice']['email']
                    // 邮箱
                ];
                $this_data = array_merge($this_data, $this->extraExportValue($item));
                $export_data[$key + 1] = array_merge($export_data[$key + 1], $this_data);
            }
//            dd($export_data);
            $export_model->export($file_name, $export_data, $this->exportRoute, 'direct_export');
        }
    }

    protected function getExportRefundName($orderArray)
    {
        if ($orderArray['manual_refund_log']) {
            return '退款并关闭';
        }

        if ($orderArray['has_one_refund_apply'] && $orderArray['has_one_refund_apply']['status'] >= \app\common\models\refund\RefundApply::COMPLETE) {
            if ($orderArray['has_one_refund_apply']['part_refund'] == \app\common\models\refund\RefundApply::ORDER_CLOSE) {
                return '退款并关闭';
            }

            return $orderArray['has_one_refund_apply']['refund_type_name'];
        }

        return '';
    }

    public function getFormDataByOderId($order_id)
    {
        $result = [];
        $set = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order.order_detail.diyform');
        if (!$set) {
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
            foreach ($detail['form_data'] as $k => $v) {
                if ($fields[$k]['data_type'] == 5) {
                    continue;
                }
                if (is_array($v)) {
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
                    $data[$k] = $parent['has_one_member']['nickname'] . ' ' . $parent['has_one_member']['realname'] . ' ' . $parent['has_one_member']['mobile'];
                    break;
                }
            }
            $data[$k] = $data[$k] ?: '';
        }

        return $data;
    }

    //新导出
    protected function getColumns()
    {
        return [
            "订单id",
            "订单编号",
            "支付单号",
            "会员ID",
            "粉丝昵称",
            "会员姓名",
            "联系电话",
            '省',
            '市',
            '区',
            "收货地址",
            "商品名称",
            "商品简称",
            "商品规格",
            "商品编码",
            "商品条码",
            "商品数量",
            '退款数量',
            "剩余数量",
            "一级分类",
            "二级分类",
            "三级分类",
            "会员价",
            "自定义表单",
            "商品来源",
            "供应商名称",
            "支付方式",
            '抵扣金额',
            '优惠券优惠',
            '全场满减优惠',
            '单品满减优惠',
            "商品小计",
            "运费",
            "成本价",
            "应收款",
            "已退款金额",
            "剩余金额",
            "状态",
            "下单时间",
            "付款时间",
            "发货时间",
            "完成时间",
            "快递公司",
            "快递单号",
            "订单备注",
            "用户备注",
            "首单",
            "真实姓名",
            "身份证",
            "核销员",
            "附加",
            "订单提示状态",
            "发票类型",
            "发票抬头",
            "单位/抬头名称",
            "税号",
            "发票内容",
            "开户银行",
            "银行账号",
            "注册地址",
            "注册电话",
            "收票人姓名",
            "收票人手机号",
            "收票人地址",
            "邮箱",
            "自提点名称",
            "自提点负责人",
            "自提点电话"
        ];
    }

    //旧导出
    protected function getColumnsV1()
    {
        return [
            "订单id",
            "订单编号",
            "支付单号",
            "会员ID",
            "粉丝昵称",
            "收货人姓名",
            "联系电话",
            '省',
            '市',
            '区',
            "收货地址",
            "商品名称",
            "商品简称",
            "商品编码",
            "商品条码",
            "商品数量",
            "一级分类",
            "二级分类",
            "三级分类",
            "会员价",
            "支付方式",
            '抵扣金额',
            '优惠券优惠',
            '全场满减优惠',
            '单品满减优惠',
            "商品小计",
            "运费",
            "应收款",
            "成本价",
            "状态",
            "下单时间",
            "付款时间",
            "发货时间",
            "完成时间",
            "快递公司",
            "快递单号",
            "订单备注",
            "用户备注",
            "首单",
            "真实姓名",
            "身份证",
            '核销员',
            '附加',
            '订单提示状态',
            "发票类型",
            "发票抬头",
            "单位/抬头名称",
            "税号",
            "发票内容",
            "开户银行",
            "银行账号",
            "注册地址",
            "注册电话",
            "收票人姓名",
            "收票人手机号",
            "收票人地址",
            "邮箱"
        ];
    }


    protected function getExportDiscount($order, $key)
    {
        $export_discount = [
            'deduction'          => 0,    //抵扣金额
            'coupon'             => 0,    //优惠券优惠
            'enoughReduce'       => 0,  //全场满减优惠
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

    //订单导出核销员显示
    protected function getAuditor($order)
    {
        //自提点
        if ($order['dispatch_type_id'] == \app\common\models\DispatchType::PACKAGE_DELIVER && app('plugins')->isEnabled(
                'package-deliver'
            )) {
            $deliverOrder = \Yunshop\PackageDeliver\model\PackageDeliverOrder::where('order_id', $order['id'])
                ->with(['hasOneDeliver', 'hasOneDeliverClerk'])
                ->first();

            $deliver_name = $deliverOrder->deliver_id . '-' . $deliverOrder->hasOneDeliver->deliver_name;

            if ($deliverOrder->hasOneDeliverClerk) {
                $package_deliver_name = "[{$deliverOrder->hasOneDeliver->deliver_name}]" . $deliverOrder->hasOneDeliverClerk->realname . "[{$deliverOrder->hasOneDeliverClerk->uid}]";
            } elseif ($order['status'] == 3) {
                $package_deliver_name = '后台确认';
            } else {
                $package_deliver_name = '';
            }

            return ['auditor' => $package_deliver_name, 'additional' => $deliver_name];
        }

        //商城自提
        if ($order['dispatch_type_id'] == \app\common\models\DispatchType::PACKAGE_DELIVERY && app(
                'plugins'
            )->isEnabled('package-delivery')) {
            $deliveryOrder = DeliveryOrder::where('order_id', $order['id'])
                ->first();


            $shopName = \Setting::get('shop.shop.name') ?: '自营';

            if ($deliveryOrder->hasOneClerk) {
                $package_deliver_name = "[{$shopName}]" . $deliveryOrder->hasOneClerk->nickname . "[{$deliveryOrder->hasOneClerk->uid}]";
            } elseif ($order['status'] == 3) {
                $package_deliver_name = '后台确认';
            } else {
                $package_deliver_name = '';
            }
            return ['auditor' => $package_deliver_name, 'additional' => $shopName];
        }


        //门店自提
        if ($order['dispatch_type_id'] == \app\common\models\DispatchType::SELF_DELIVERY && app('plugins')->isEnabled(
                'store-cashier'
            )) {
            $storeOrder = \Yunshop\StoreCashier\common\models\StoreOrder::where('order_id', $order['id'])->first();

            if ($storeOrder->hasOneClerkMember) {
                $package_deliver_name = "[{$storeOrder->hasOneStore->store_name}]" . $storeOrder->hasOneClerkMember->nickname . "[{$storeOrder->hasOneClerkMember->uid}]";
            } elseif ($order['status'] == 3) {
                $package_deliver_name = '后台确认';
            } else {
                $package_deliver_name = '';
            }
            return [
                'auditor'    => $package_deliver_name,
                'additional' => $storeOrder->hasOneStore->id . '-' . $storeOrder->hasOneStore->store_name
            ];
        }


        return ['auditor' => '', 'additional' => ''];
    }

    protected function getGoods($order, $key, $category = '')
    {
        if (empty($category)) {
            $category = new Category();
        }
        $goods_title = '';
        $alias = '';
        $goods_sn = '';
        $total = '';
        $weight = '';
        $vip_price = '';
        $product_sn = '';
        $cost_price = 0;
        $firstCate = '';
        $secondCate = '';
        $thirdCate = '';
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
                    $weight .= '【' . $goods['total'] * $goods_option->weight . 'g】';
                    $goods_sn .= '【' . $goods_option->goods_sn . '】';
                }
            } else {
                $weight .= '【0g】';
                $goods_sn .= '【' . $goods['goods_sn'] . '】';
            }
            $product_sn .= '【' . $goods['product_sn'] . '】';
            $goods_title .= '【' . $res_title . '*' . $goods['total'] . '】';
            $alias .= '【' . $goods['goods']['alias'] . '】';
            $total .= '【' . $goods['total'] . '】';
            $vip_price .= '【' . $goods['vip_price'] . '】';
            $cost_price += $goods['goods_cost_price'];
            $cateList = [];
            foreach ($goods['goods']['belongs_to_categorys'] as $k => $v) {
                $cateList[] = $category->getCateOrderByLevel($v['category_ids']);
            }
            $firstCateTemp = array_column($cateList, 0);
            $secondCateTemp = array_column($cateList, 1);
            $thirdCateTemp = array_column($cateList, 2);
            foreach ($firstCateTemp as $temp) {
                $firstCate .= '【' . $temp . '】';
            }
            foreach ($secondCateTemp as $temp) {
                $secondCate .= '【' . $temp . '】';
            }
            foreach ($thirdCateTemp as $temp) {
                $thirdCate .= '【' . $temp . '】';
            }
        }
        $res = [
            'goods_title' => $goods_title,
            'alias'       => $alias,
            'goods_sn'    => $goods_sn,
            'product_sn'  => $product_sn,
            'total'       => $total,
            'weight'      => $weight,
            'vip_price'   => $vip_price,
            'cost_price'  => $cost_price,
            'first_cate'  => $firstCate,
            'second_cate' => $secondCate,
            'third_cate'  => $thirdCate,
        ];
        if (!$key) {
            return $res;
        }
        return $res[$key];
    }

    protected function getNickname($nickname)
    {
        if (substr($nickname, 0, strlen('=')) === '=') {
            $nickname = '，' . $nickname;
        }
        return $nickname;
    }

    protected function getExportRealName($order)
    {
        $name = $order['has_one_package_delivery_order'] ? $order['has_one_package_delivery_order']['buyer_name'] : $order['address']['realname'];
        return $name ?: '';
    }

    protected function getExportMobile($order)
    {
        $mobile = $order['has_one_package_delivery_order'] ? $order['has_one_package_delivery_order']['buyer_mobile'] : $order['address']['mobile'];
        return $mobile ?: '';
    }


    protected function getExportDeliverName($order)
    {
        $return = '';
        if ($order['has_one_package_delivery_order']) {
            $return = Setting::get('shop.contact.store_name');
        } elseif ($order['has_one_package_deliver_order']) {
            $return = $order['has_one_package_deliver_order']['deliver_name'] ?: $order['has_one_package_deliver_order']['has_one_deliver']['deliver_name'];
        }
        return $return ?: '';
    }

    protected function getExportDeliverOwnerName($order)
    {
        $return = '';
        if ($order['has_one_package_delivery_order']) {
            $return = '商城自提';
        } elseif ($order['has_one_package_deliver_order']) {
            $return = $order['has_one_package_deliver_order']['has_one_deliver']['realname'];
        }
        return $return ?: '';
    }

    protected function getExportDeliverOwnerMobile($order)
    {
        $return = '';
        if ($order['has_one_package_delivery_order']) {
            $return = Setting::get('shop.contact.phone');
        } elseif ($order['has_one_package_deliver_order']) {
            $return = $order['has_one_package_deliver_order']['has_one_deliver']['deliver_mobile'];
        }
        return $return ?: '';
    }

    protected function getExportYzSupplySupplyName($goods_id, $yzSupplyForm)
    {
        if (app('plugins')->isEnabled('yz-supply')) {
            $data = \Yunshop\YzSupply\models\YzGoods::uniacid()->select(
                'middleground_configuration_id',
                'shop_id'
            )->where('goods_id', $goods_id)->first();

            return $yzSupplyForm[$data->middleground_configuration_id][$data->shop_id] ?: '';
        }
        return '';
    }


    protected function invoiceType($type)
    {
        $result = '';
        if ($type == '0' || empty($type)) {
            $result = '电子发票';
        }
        if ($type == 1) {
            $result = '纸质发票';
        }
        if ($type == 2) {
            $result = '专用发票';
        }
        return $result;
    }

    /*
     * 获取快递包裹的公司字符串和单号字符串
     */
    protected function getExpressString($express)
    {
        $company = array_column($express, 'express_company_name');
        $sn = array_column($express, 'express_sn');
        $companyStr = '';
        $snStr = '';
        if (count($company) == 1) {
            $companyStr = $company[0];
        } else {
            foreach ($company as $val) {
                $companyStr .= '【' . $val . '】 ';
            }
        }
        if (count($sn) == 1) {
            $snStr = $sn[0];
        } else {
            foreach ($sn as $val) {
                $snStr .= '【' . $val . '】 ';
            }
        }
        return ['company' => $companyStr, 'sn' => $snStr];
    }
}
