@extends('layouts.base')
@section('title', "订单列表")
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .el-select {position: relative;}
    .el-select__tags { position: inherit;transform: translateY(0);padding: 3px 0;min-height: 40px;}
    .el-select__tags ~ .el-input {height: 100%;position: absolute;top: 50%;left: 0;transform: translateY(-50%);}
    .el-select__tags ~ .el-input .el-input__inner {min-height: 20px; height: 100% !important;}
    .el-select__input.is-mini { min-height: 20px;}
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
    .el-tabs__item,.is-top{font-size:16px}
    .el-tabs__active-bar { height: 3px;}

    .list-title{display:flex;width:100%;background:#f9f9f9;padding:15px 10px;font-weight:900;border:1px solid #e9e9e9;}
    .list-title .list-title-1{display:flex;align-items:center;justify-content: center;}
    .list-info{display:flex ;padding: 10px;justify-content: left;background:#f9f9f9;}

    .list-con{display:flex;width:100%;font-size:12px;font-weight:500;align-items: stretch;border-bottom: 1px solid rgb(233, 233, 233);}
    .list-con-goods{display:flex;align-items:center;justify-content: center;box-sizing:border-box;padding-left:10px;border-top:1px solid #e9e9e9;min-height:90px}
    .list-con-goods-text{min-height:70px;overflow:hidden;flex:1;display: flex;flex-direction: column;justify-content: space-between;}
    .list-con-goods-price{border-right:1px solid #e9e9e9;border-left:1px solid #e9e9e9;min-width:150px;min-height:90px;text-align: left;padding:20px;display: flex;flex-direction: column;}
    .list-con-goods-title{font-size:14px;line-height:20px;text-overflow: -o-ellipsis-lastline;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 2;line-clamp: 2;-webkit-box-orient: vertical;}
    .list-con-goods-option{font-size:12px;color:#999}
    .list-con-goods-after-sales{font-size:12px;color:red}

    .list-con-member-info{display:flex;padding:0 2px;flex-direction: column;flex:1;min-width: 120px;line-height:28px;justify-content: center;text-align:left;font-size:14px;border-top:1px solid #e9e9e9;border-right:1px solid #e9e9e9;}

    .list-member{padding: 10px;font-size: 12px;font-weight: 500;display:flex}

    .list-num{flex:3;display:flex;align-items:center;border-right:1px solid #e9e9e9;justify-content: center;}
    .list-gen{display:flex;align-items:center;justify-content: center;line-height:28px;}
    .list-gen-txt{flex:1;border-right:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;align-items:center;justify-content: center;display:flex;}
    .list-opt{flex:1;display:flex;align-items:center;border-left:1px solid #e9e9e9;justify-content: center;}
     /* 导航 */
    .el-radio-button .el-radio-button__inner,.el-radio-button:first-child .el-radio-button__inner {border-radius: 4px 4px 4px 4px;border-left: 0px;}
    .el-radio-button__inner{border:0;}
    .el-radio-button:last-child .el-radio-button__inner {border-radius: 4px 4px 4px 4px;}
    .el-checkbox__inner{margin:0 15px;width:18px;height:18px;}
    .el-checkbox__input.is-checked .el-checkbox__inner::after {transform: rotate(45deg) scaleY(1);padding-top: 3px;padding-left:3px;}

    .a-btn {
        border-radius: 2px;
        padding: 8px 12px;
        box-sizing: border-box;
        color: #666;
        font-weight: 500;
        text-align: center;
        margin-left: 1%;
        background-color: #fff;
    }
    .a-btn:hover{
        background-color: #29BA9C;
        color: #FFF;
    }

    .a-colour1 {
        background-color: #fff;
        color: #666;
    }
    .a-colour2 {
        background-color: #29BA9C;
        color: #FFF;
    }
    .el-form-item {
        margin-bottom: 5px;
    }
    .el-popover{
        width: 90px;
        min-width: 90px;
    }
    .vue-search .el-form-item {
        height: 40px;
    }

    .row-bottom-class {
        margin-left: 15px;

    }

</style>
<div class="all" id="card">
    <div id="app" v-cloak>

        {{--订单类型选项卡--}}
        @include('order.typeTabs')

        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">订单筛选</div>
                <div class="vue-main-title-button">
                </div>
            </div>
            <div class="vue-search">

                <template>
                    <{!! (new \app\backend\modules\order\services\OrderViewService())->searchImport('name') !!}
                            :view-return="responseResults"
                            :search-form="search_form"
                            :other-data="otherData"
                            @sync-form="syncSearchForm"
                            @search="searchMutual"
                            @export="agentExport"
                    >
                    </{!! (new \app\backend\modules\order\services\OrderViewService())->searchImport('name') !!}>
                </template>


            </div>
        </div>
        <div class="vue-main">
            <div class="vue-main-form">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    {{--<div class="vue-main-title-content" style="flex:0 0 140px">商品订单列表</div>--}}
                    <div class="" style="text-align:left;font-size:14px;color:#999">
                        <span>订单数：[[count.total]]</span>&nbsp;&nbsp;&nbsp;
                        <span>订单金额：[[count.total_price]]</span>&nbsp;&nbsp;&nbsp;
                        <span>运费：[[count.dispatch_price]]</span>&nbsp;&nbsp;&nbsp;
                        <el-button @click="confirm_batch_send_show = true" v-if="code == 'waitSend'"  size="mini" type="primary">
                            一键发货
                        </el-button>
                    </div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div v-for="(item,index) in list" style="border:1px solid #e9e9e9;border-radius:10px;margin-bottom:10px">
                    <div class="list-info">
                        <div style="display:flex;flex-wrap:wrap">
                            <div class="vue-ellipsis" style="max-width:250px">
                                <strong>[[item.store_name]]</strong>&nbsp;&nbsp;&nbsp;
                            </div>
                            <div class="vue-ellipsis" style="color:#999;max-width:150px">
                                <strong>订单ID：</strong>[[item.id]]&nbsp;&nbsp;&nbsp;
                            </div>
                            <div v-if="item.order_sn" class="vue-ellipsis" style="color:#999;max-width:240px">
                                <strong>订单编号：</strong>[[item.order_sn]]&nbsp;&nbsp;&nbsp;
                            </div>
                            <div v-if="item.has_one_order_pay" class="vue-ellipsis" style="color:#999;max-width:240px">
                                <strong>支付单号：</strong>[[item.has_one_order_pay.pay_sn]]&nbsp;&nbsp;&nbsp;
                            </div>
                            <div class="vue-ellipsis" style="color:#999;max-width:230px">
                                <strong>下单时间：</strong>[[item.create_time]]&nbsp;&nbsp;&nbsp;
                            </div>

                            <div v-if="item.has_many_first_order != null && item.has_many_first_order.length > 0" class="vue-ellipsis" style="color:#999;max-width:100px">
                                <strong>首单</strong>&nbsp;&nbsp;&nbsp;
                            </div>

                            <div v-if="item.has_one_refund_apply" class="vue-ellipsis" style="color:red;max-width:230px">
                                <strong>[[item.has_one_refund_apply.refund_type_name]]：[[item.has_one_refund_apply.status_name]]</strong>&nbsp;&nbsp;&nbsp;
                            </div>
                            <div v-if="!item.refund_id && item.part_refund" style="color:red;max-width:230px">
                                <strong>部分退款</strong>&nbsp;&nbsp;
                            </div>

                            <div v-if="item.no_refund" class="vue-ellipsis" style="color:red;max-width:200px">
                                <strong>不可退款</strong>&nbsp;&nbsp;&nbsp;
                            </div>

                            <div v-if="item.manual_refund_log != null || (item.has_one_refund_apply && item.has_one_refund_apply.part_refund == 4)" class="vue-ellipsis" style="color:red;max-width:200px">
                                <strong>退款并关闭</strong>&nbsp;&nbsp;&nbsp;
                            </div>

                            <div v-if="item.leshua_refund_error_msg != null" class="vue-ellipsis" style="color:red;max-width:500px">
                                <strong>(乐刷: [[ item.leshua_refund_error_msg ]])</strong>
                            </div>

                            <div v-for="(topContent,topKey1) in item.top_row" :key="topKey1" class="vue-ellipsis" style="color:#29BA9C;max-width:230px" >
                                <strong>[[topContent]]</strong>&nbsp;&nbsp;&nbsp;
                            </div>
                        </div>

                        <div style="flex:1;text-align:right;min-width:150px;">
                            <span style="margin-left: 10px;" v-if="item.fixed_button.partRefund.is_show">
                                <a @click="partRefund(item.id,item)" style="color:#29BA9C;font-size:13px;font-weight:600">部分退款</a>
                            </span>
                            <span style="margin-left: 10px;" v-if="item.fixed_button.close.is_show">
                                <a @click="closeOrder(item.id,item)" style="color:#29BA9C;font-size:13px;font-weight:600">关闭订单</a>
                            </span>
                            <span style="margin-left: 10px;" v-if="item.fixed_button.manualRefund.is_show">
                                <a @click="closeOrder1(item.id,item)" style="color:#29BA9C;font-size:13px;font-weight:600">退款并关闭</a>
                            </span>

                        </div>
                    </div>
                    <div class="list-con">
                        <div style="flex:3;min-width:400px">
                            <div v-for="(item1,index1) in item.has_many_order_goods" class="list-con-goods">
                                <div class="list-con-goods-img" style="width:80px">
                                    <el-image :src="item1.thumb"  style="width:70px;height:70px"></el-image>
                                </div>
                                <div class="list-con-goods-text" :style="{justifyContent:(item1.goods_option_title?'':'center')}">
                                    <div class="list-con-goods-title" style="color:#29BA9C;cursor: pointer;" @click="gotoGoods(item1.goods_id,item)">[[item1.title]]</div>
                                    <div class="list-con-goods-option" v-if="item1.goods_option_title">规格：[[item1.goods_option_title]]</div>
                                    <div v-if="item1.after_sales">
                                        <div v-if="item1.refund_id" class="list-con-goods-after-sales">
                                            [[item1.after_sales.refund_type_name]]：[[item1.after_sales.refund_status_name]] * [[item1.after_sales.refunded_total]]
                                        </div>
                                        <div v-if="(!item1.refund_id) && item1.after_sales.refunded_total" class="list-con-goods-after-sales" >
                                            已售后：[[item1.after_sales.refunded_total]]
                                        </div>
                                    </div>
                                </div>
                                <div class="list-con-goods-price">
                                    <div>现价：[[item1.goods_price]]</div>
                                    @if(\Setting::get('shop.member')['vip_price'] == 1)
                                        <div>会员价：[[item1.vip_price]]</div>
                                    @endif
                                    <div>实付：[[item1.payment_amount]]</div>
                                    <div>数量：[[item1.total]]</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis">
                            <div v-if="item.belongs_to_member" style="min-width:70%;margin:0 auto">
                                <div @click="gotoMember(item.uid)" style="line-height:32px;color:#29BA9C;cursor: pointer;" class="vue-ellipsis">
                                    <strong>[[item.belongs_to_member.nickname]] </strong>
                                </div>
                                <div>[[item.has_many_member_certified?item.has_many_member_certified.realname:item.belongs_to_member.realname]]</div>
                                <div>[[item.belongs_to_member.mobile]]</div>
                            </div>
                            <div v-else style="min-width:70%;margin:0 auto">
                                <div v-if="item.member_cancel&&item.member_cancel.status==2">该会员已注销</div>
                                <div v-else-if="item.uid==0"></div>
                                <div v-else>会员([[item.uid]])已被删除</div>
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="text-align:center;min-width: 90px;">
                            <div><strong>[[item.pay_type_name]]</strong></div>
                            <div><strong v-if="item.has_one_dispatch_type">[[item.has_one_dispatch_type.name]]</strong></div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="min-width: 120px;">
                            <div style="min-width:75%;margin:0 auto">
                                <div>商品小计：￥[[item.goods_price]]</div>
                                <div>运费：￥[[item.dispatch_price]]</div>
                                <div v-if="item.change_price!='0.00'">卖家改价：￥[[item.change_price]]</div>
                                <div v-if="item.change_dispatch_price!='0.00'">卖家改运费：￥[[item.change_dispatch_price]]</div>
                                <div>应付款：￥[[item.price]]</div>
                                <div>商品总数量：[[item.goods_total]]</div>
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="text-align:center">
                            <div style="min-width:70%;margin:0 auto">
                                <div style="color:#29BA9C">[[item.status_name]]</div>
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="text-align:center;min-width: 80px;border-right:0">

                            <div v-for="(item1,index1) in item.backend_button_models" :key="index1">
                                <el-button @click="orderConfirm(item1.value,item)" size="mini" :type="item1.type" style="width:80%;margin:0 auto;margin-bottom:5px;">
                                    [[item1.name]]
                                </el-button>
                            </div>
                        </div>

                    </div>
                    <div class="list-member">
                        <div style="display:flex;flex-wrap:wrap;justify-content: flex-start">
                            <template v-if="item.row_bottom">
                                <template v-for="(bottom_item,bottom_key1) in item.row_bottom">
                                    <span :class="bottom_key1 ? 'row-bottom-class' : '' "  :style="bottom_item.style">
                                        [[bottom_item.text]]
                                    </span>
                                </template>
                            </template>
                            &nbsp;&nbsp;&nbsp;

                            <span v-if="item.has_one_order_remark" style="
                                width: 300px;
                                overflow: hidden;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            ">商家备注：[[item.has_one_order_remark.remark]]</span>


                        </div>
                        <div style="justify-content: flex-end;flex:1;display:flex">
                            <div v-if="extra_param.printer" style="margin-right:15px">
                                <el-tooltip class="item" effect="dark" content="打印小票" placement="bottom">
                                    <a style="color:#29BA9C;" @click="orderPrinter(item.id,item)">
{{--                                        <i class="el-icon-printer"></i>--}}
                                        <img src="{!! \app\common\helpers\Url::shopUrl('static/images/printer.png') !!}" style="width: 20px;">
                                    </a>
                                </el-tooltip>
                            </div>
                            <div v-if="item.has_many_city_delivery_another&&item.has_many_city_delivery_another.length!=0">
                                <city-delivery :item="item"></city-delivery>
                            </div>
                            <div  style="margin-right:15px" v-if="item.fixed_button.invoice.is_show">
                                <el-popover placement="top-start" trigger="hover" style="min-width:80px;">
                                    <div style="width:60px;" @click="oneClickInvoicing(item.id,item.price)">
                                        <el-button type="text">一键开票</el-button>
                                    </div>

                                    <div style="width:60px;">
                                        <a @click="oneClickInvoicing(item.id,item.price,`{!! yzWebFullUrl('plugin.invoice.admin.invoicing-order.set-invoice') !!}&order_id=${item.id}`)">
                                            <el-button type="text">手动开票</el-button>
                                        </a>
                                    </div>
                                    <a style="color:#29BA9C" slot="reference">[[item.fixed_button.invoice.name]]</a>
                                </el-popover>

                            </div>
                            @if(app('plugins')->isEnabled('electronics-bill'))
                                <electronics-bill :item="item"></electronics-bill>
                            @endif
                            <div v-if="item.status==0" style="margin-right:15px">
                                <a style="color:#29BA9C" @click="changePrice(item.id,item)">修改价格</a>
                            </div>
                            <div v-if="item.fixed_button.detail.is_show">
                                <a @click="gotoDetail(item)" style="color:#29BA9C">查看详情</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--订单类型-动态引入主键--}}
        @foreach((new \app\backend\modules\order\services\OrderViewService())->importVue() as $routeKey => $vueRoute)
            <template>
            <{{$vueRoute['primary']}}
            :operation-type="operationType"
            :operation-order="operationOrder"
            :express-companies="expressCompanies"
            :dialog_show="dialog_show"
            @search="reloadList"
            >
            </{{$vueRoute['primary']}}>
            </template>
        @endforeach
        {{--<template>--}}
            {{--<order-operation--}}
                {{--:operation-type="operationType"--}}
                {{--:operation-order="operationOrder"--}}
                {{--:synchro="synchro"--}}
                {{--:express-companies="expressCompanies"--}}
                {{--:dialog_show="dialog_show"--}}
                {{--@search="reloadList"--}}
            {{-->--}}
            {{--</order-operation>--}}
        {{--</template>--}}

        <!-- 退款 -->
        <el-dialog :visible.sync="close_order_show" width="750px"  title="关闭订单">
            <div style="height:300px;overflow:auto" id="close-order">
                <div style="color:#000;font-weight:500">关闭订单原因</div>
                <el-input v-model="close_order_con" :rows="10" type="textarea"></el-input>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="close_order_show = false">取 消</el-button>
                <el-button type="primary" @click="sureCloseOrder">确 定 </el-button>
            </span>
        </el-dialog>
        <!-- 手动退款 -->
        <el-dialog :visible.sync="close_order1_show" width="750px"  title="退款并关闭订单">
            <div style="height:300px;overflow:auto">
                <div style="color:#000;font-weight:500">退款原因</div>
                <el-input v-model="close_order1_con" :rows="10" type="textarea"></el-input>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="close_order1_show = false">取 消</el-button>
                <el-button type="primary" @click="sureCloseOrder1">退 款 </el-button>
            </span>
        </el-dialog>
        <!-- 部分退款 -->
        <el-dialog :visible.sync="part_refund_show" width="750px"  title="部分退款" @close="default_check=false">
            <div style="overflow:auto">
                <div style="color:#000;font-weight:500">退款金额:￥[[part_refund_price]]</div>
            </div>
            <div style="height:300px;overflow:auto">
                <div style="color:#000;font-weight:500">申请说明</div>
                <el-input v-model="part_refund_con" :rows="10" type="textarea"></el-input>
            </div>
            <el-divider></el-divider>
            <div v-for="(item_part,index_part) in part_refund_goods" style="display: flex;justify-content: space-between;align-items: center;padding-bottom: 10px">
                <el-checkbox :label="item_part.id" :key="index_part" style="width: 100%;" @change="changeCheck($event,item_part.id)" v-model="item_part.checked"><br/></el-checkbox>
                <div style="min-width: 600px;display: flex;align-items: center;justify-content: space-between;">
                    <div>
                        <img :src="item_part.thumb" style="width:60px;height:60px;">
                        <span>[[item_part.title]]</span>
                        <span class="help-block" v-if="item_part.option">规格：[[item_part.option]]</span>
                    </div>
                    <div style="float: right">
                        <el-input-number v-model="item_part.total" controls-position="right"  :min="1" :max="item_part.remark"  @change="changeCheck"></el-input-number>
                        <div>最大数量:[[item_part.remark]]</div>
                    </div>
                </div>
            </div>

            <div slot="footer" class="dialog-footer" style="display: flex;justify-content: space-between;align-items: center;">
                <el-checkbox label="全选" @change="allCheck($event)" v-model="default_check" style="margin-bottom: 0"></el-checkbox>
                <div>
                    <el-button @click="part_refund_show = false">取 消</el-button>
                    <el-button type="primary" @click="surePartRefund">确 定 </el-button>
                </div>
            </div>
        </el-dialog>
        <!-- 取消发货 -->
        <!-- <el-dialog :visible.sync="cancel_send_show" width="750px"  title="取消发货">
            <div style="height:300px;overflow:auto" id="cancel-send">
                <div style="color:#000;font-weight:500">取消发货原因</div>
                <el-input v-model="cancel_send_con" :rows="10" type="textarea"></el-input>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancel_send_show = false">取 消</el-button>
                <el-button type="primary" @click="sureCancelSend">取消发货 </el-button>
            </span>
        </el-dialog> -->
        <!-- 批量发货发货 -->
        <el-dialog :visible.sync="confirm_batch_send_show" width="750px"  title="一键发货（退款订单不能发货）">
            <div style="height:200px;overflow:auto" id="confirm-send">
                <el-form ref="send" :model="batch_send" :rules="send_rules" label-width="15%">
                    <el-form-item label="配送方式" prop="">
                        <el-radio v-model="batch_send.dispatch_type_id" :label="1">快递</el-radio>
                    </el-form-item>
                    <el-form-item label="快递公司">
                        <el-select v-model="batch_send.express_code" clearable filterable placeholder="快递公司" :disabled='readonly' style="width:70%;">
                            <el-option label="其他快递" value=""></el-option>
                            <el-option v-for="(item,index) in expressCompanies" :key="index" :label="item.name" :value="item.value"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="快递单号" prop="">
                        <el-input v-model="batch_send.express_sn" :disabled='readonly' style="width:70%;"></el-input>
                    </el-form-item>
                </el-form>

            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="confirm_batch_send_show = false">取 消</el-button>
                <el-button type="primary" @click="sureconfirmBatchSend">确认发货 </el-button>
            </span>
        </el-dialog>
        <!-- 多包裹确认发货 -->
        <!-- <el-dialog :visible.sync="more_send_show" width="750px"  title="分批发货">
            <div style="" id="separate-send">
                <el-form ref="send" :model="send" label-width="15%">
                    <el-form-item label="收件人信息" prop="aggregation">
                        <div>收 件 人: [[address_info.realname]] / [[address_info.mobile]]</div>
                        <div>收货地址: [[address_info.address]]</div>
                    </el-form-item>
                    <el-form-item label="快递公司">
                        <el-select v-model="send.express_code" clearable filterable placeholder="快递公司" style="width:70%;">
                            <el-option label="其他快递" value=""></el-option>
                            <el-option v-for="(v1,k1) in expressCompanies" :key="k1" :label="v1.name" :value="v1.value"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="快递单号" prop="">
                        <el-input v-model="send.express_sn" style="width:70%;"></el-input>
                    </el-form-item>
                </el-form>
                <el-table ref="multipleTable"  :data="order_goods_send_list" tooltip-effect="dark"  height="250" style="width: 100%" @selection-change="moreSendChange">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column width="550">
                        <template slot-scope="scope">
                            <div style="display:flex;width: 88%;">
                                <div style="width:50px;height:50px">
                                    <img :src="scope.row.thumb" alt="" style="width:50px;height:50px">
                                </div>
                                <div style="margin-left:20px;display: flex;flex-direction: column;justify-content: space-between;">
                                    <div style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">[[scope.row.title]]</div>
                                    <div style="color:#999">[[scope.row.goods_id]]</div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column>
                        <template slot-scope="scope">
                            <div style="color:#999">[[scope.row.goods_option_title]]</div>
                        </template>
                    </el-table-column>
                </el-table>

            </div>
            <span slot="footer" class="dialog-footer">
                    <el-button @click="more_send_show = false">取 消</el-button>
                    <el-button type="primary" @click="confirmMoreSend()">确认发货 </el-button>
                </span>
        </el-dialog> -->
        <!-- 修改价格 -->
        <el-dialog :visible.sync="change_price_show" width="65%"  title="修改价格">
            <div style="height:500px;overflow:auto" id="change-price">
                <el-table :data="order_goods_model" style="width: 100%">
                    <el-table-column label="商品名称" prop="has_one_goods.title"></el-table-column>
                    <el-table-column label="单价" align="center" prop="goods_price" width="120">
                        <template slot-scope="scope">
                            <div>
                                [[parseFloat(scope.row.price)/parseFloat(scope.row.total)]]
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="数量" align="center" prop="total" width="100"></el-table-column>
                    <el-table-column label="支付金额" align="center" prop="payment_amount" width="150">
                        <template slot-scope="scope">
                            <div>
                                [[scope.row.price]]
                                <el-tag v-if="scope.row.change_price&&scope.row.change_price!=0&&scope.row.change_price!='0.00'" type="danger">改价</el-tag>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="加价或减价" align="center" prop="display_order">
                        <template slot-scope="scope">
                            <div>
                                <el-input @input="inputPrice(scope.$index,scope.row.new_price)" v-model="scope.row.new_price" size="small" style="width:95%"></el-input>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="运费" align="center" prop="dispatch_price">
                        <template slot-scope="scope" v-if="scope.$index==0">
                            <div>
                                <el-input @input="inputPrice('dispatch_pricec',dispatch_price)" v-model="dispatch_price" size="small" style="width:95%"></el-input>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="" align="center" prop="display_order">
                        <template slot-scope="scope" v-if="scope.$index==0">
                            <div>
                                <el-link :underline="false" @click="clearDispatch">直接免运费</el-link>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="tip" style="color:#f00;margin:10px 0;">提示：改价后价格不能小于0元</div>
                <div style="background:#eff3f6;border-radius:8px;padding:20px 10px;color:#000;font-weight:500;line-height:36px;">
                    <div style="font-size:16px;font-weight:600;color:#000">购买者信息</div>
                    <div>
                        <div style="display:inline-block;width:150px;text-align:right;margin-right:30px">姓名</div>
                        <div style="display:inline-block;">[[order_model.address?order_model.address.realname:""]]</div>
                    </div>
                    <div>
                        <div style="display:inline-block;width:150px;text-align:right;margin-right:30px">联系方式</div>
                        <div style="display:inline-block;">[[order_model.address?order_model.address.mobile:""]]</div>
                    </div>
                    <div>
                        <div style="display:inline-block;width:150px;text-align:right;margin-right:30px">联系地址</div>
                        <div style="display:inline-block;">[[order_model.address?order_model.address.address:""]]</div>
                    </div>
                </div>
            </div>
            <span slot="footer" class="dialog-footer" style="display:flex;justify-content: flex-end;">
                <div style="display:inline-block;color:#000;margin-right:20px;font-weight:500;">
                    <div style="display:flex;text-align:center;line-height:28px;align-items: center;">
                        <div style="margin-right:15px">
                            原价<br>
                            ￥[[parseFloat(order_model.price)-(parseFloat(order_model.dispatch_price))]]
                        </div>
                        <div style="margin-right:15px">+</div>
                        <div style="margin-right:15px">
                            运费<br>
                            ￥[[parseFloat(dispatch_price)]]
                        </div>
                        <div style="margin-right:15px">+</div>
                        <div style="margin-right:15px">
                            价格修改<br>
                            ￥[[(parseFloat(change_all_money).toFixed(2))]]
                        </div>
                        <div style="margin-right:15px">=</div>
                        <div style="margin-right:15px">
                            买家实付<br>
                            <strong style="color:#f00">￥[[(parseFloat(all_money).toFixed(2))]]</strong>
                        </div>
                    </div>
                </div>
                <div style="line-height:56px">
                    <el-button @click="change_price_show = false">取 消</el-button>
                    <el-button type="primary" @click="sureChangePrice">确认改价</el-button>
                </div>
            </span>
        </el-dialog>

        <!-- 分页 -->
        <div class="vue-page" v-if="total>0">
            <el-row>
                <el-col align="right">
                    <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                        :page-size="per_page" :current-page="current_page" background
                        ></el-pagination>
                </el-col>
            </el-row>
        </div>

        <invoice v-model="OneclickInvoicing" :show="OneclickInvoicing" ref="invoice" :order_id="order_id" url="{!! yzWebFullUrl('order.order-list.index') !!}"></invoice>
    </div>
</div>
{{--订单类型-动态加载js文件--}}
@foreach((new \app\backend\modules\order\services\OrderViewService())->importVue() as $routeKey => $vueRoute)
    @include($vueRoute['path'])
@endforeach


@include((new \app\backend\modules\order\services\OrderViewService())->searchImport('path'))

{{--@include('public.admin.orderOperation')--}}
{{--@include('public.admin.orderOperationV')--}}
@include('public.admin.invoice')
<script>
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {

            return {
                OneclickInvoicing:false,//一键开票预览
                order_id:"",
                commonPartUrl: '{!! yzWebFullUrl('order.order-list.common-part') !!}', //获取公共参数链接
                listUrl: '{!! yzWebFullUrl('order.order-list.get-list') !!}',//获取订单数据链接
                exportUrl: '{!! yzWebFullUrl('order.order-list.export') !!}',//订单数据导出链接
                detailUrl: '{!! yzWebFullUrl('order.detail.vue-index') !!}',
                url_open : "{!! yzWebUrl('order.waybill.waybill') !!}",
                list:[],
                count:{},
                has_many_level:[],
                change_sort:'',

                times:[], //搜索时间

                responseResults:{}, //页面返回全部参数

                otherData:{}, //传给搜索组件的额外参数

                extra_param:{},  //插件判断条件

                //订单条件
                code:'all',

                expressCompanies:[],//快递公司
                //搜索条件参数
                search_form:{
                    member_id:"",
                    order_sn:'{!! $_REQUEST['order_sn']?:'' !!}',
                },

                close_order_show:false,//关闭订单弹窗
                close_order_con:"",//关闭订单原因
                close_order_id:"",
                close_order_api:"",
                close_order1_show:false,//手动关闭订单弹窗
                close_order1_con:"",//手动关闭订单原因
                close_order1_id:"",
                close_order1_api:"",
                cancel_send_show:false,// 取消发货弹窗
                cancel_send_con:"",//取消发货原因
                cancel_send_id:'',
                confirm_send_show:false,// 确认发货弹窗
                confirm_send_id:"",
                confirm_batch_send_show:false,// 确认发货弹窗
                change_price_show:false,//修改价格弹窗
                change_price_id:"",
                part_refund_show:false,//部分退款弹窗
                part_refund_id:"",
                part_refund_con:"",//部分退款原因
                part_refund_price:0,
                part_refund_api:"",
                part_refund_goods:[],
                part_refund_arr:[],
                change_all_money:0.00,//价格修改
                all_money:0.00,//买家实付
                order_model:{},
                dispatch_price:0,//运费
                order_goods_model:[],
                address_info:{},

                //发货提交信息
                send:{
                    dispatch_type_id:1,
                    express_code:"",
                    express_sn:"",
                },
                //发货提交信息
                batch_send:{
                    dispatch_type_id:1,
                    express_code:"",
                    express_sn:"",
                },
                send_rules:{

                },
                // 多包裹发货
                more_send_show:false,
                order_goods_send_list:[],
                send_order_goods_ids:[],


                street:0,
                province_list:[],
                city_list : [],
                district_list : [],
                street_list : [],
                areaLoading:false,


                rules: {},
                current_page:1,
                total:1,
                per_page:1,
                synchro:0,
                readonly:false,

                operationType:'',
                operationOrder:{},
                dialog_show:0,

                default_check:false,
                is_source_open: 0,
                source_list: [],
            }
        },
        created() {
            let result = this.viewReturn();
            this.__initial(result);
        },
        mounted() {
            // console.log(this.search_form);
            this.getData(1);
        },
        methods: {


            //搜索子组件传过来数据
            syncSearchForm(form,other) {

                this.search_form = form;

                console.log(form,other);
            },

            //搜索组件提交搜索
            searchMutual() {
                console.log(this.search_form);
                this.search(1);
            },
            //搜索组件提交导出
            agentExport(export_type,template,exportUrl) {
                this.export1(export_type,template,exportUrl);
            },

            oneClickInvoicing(id,price,url){
                if (price<=0) {
                    this.$message.error("0元或0元以下商品不支持开票");
                    return false;
                }
                if (url) {
                    window.location.href = url;
                    return false;
                }
                //一键开票
                this.order_id = id;
                this.OneclickInvoicing = true;
            },

            //视图返回数据
            viewReturn() {
                return {!! $data?:'{}' !!};
            },
            //初始化页面数据，请求链接
            __initial(data) {
                if (data.code) {
                    this.code = data.code;
                }
                if (data.listUrl) {
                    this.listUrl = data.listUrl;
                }
                if (data.commonPartUrl) {
                    this.commonPartUrl = data.commonPartUrl;
                }
                if (data.exportUrl) {
                    this.exportUrl = data.exportUrl;
                }

                if (data.extraParam) {
                    this.extra_param = data.extraParam;
                }

                this.expressCompanies = data.expressCompanies;

                this.responseResults = data;
                this.is_source_open = data.is_source_open;
                this.source_list = data.source_list;
                // console.log(data);
            },
            getData(page) {

                let requestData = {
                    page:page,
                    code: this.code,
                    search: JSON.parse(JSON.stringify(this.search_form)),
                };
                // console.log(requestData);
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.listUrl,requestData).then(function(response) {
                    if (response.data.result) {
                        // this.list = response.data.data.list;
                        //this.expressCompanies = response.data.data.expressCompanies;
                        this.count = response.data.data.count;

                        this.list = response.data.data.list.data;

                        this.current_page = response.data.data.list.current_page;
                        this.total = response.data.data.list.total;
                        this.per_page = response.data.data.list.per_page;
                        this.synchro = response.data.data.synchro;
                        loading.close();

                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                    loading.close();

                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                });
            },

            search(val) {
                this.getData(val);
            },
            initProvince(val) {
                // console.log(val);
                this.areaLoading = true;
                this.$http.get("{!! yzWebUrl('area.list.init', ['area_ids'=>'']) !!}"+val).then(response => {
                    this.province_list = response.data.data;
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            changeProvince(val) {
                this.city_list = [];
                this.district_list = [];
                this.street_list = [];
                // this.search_form.province_id = "";
                this.search_form.city_id = "";
                this.search_form.district_id = "";
                this.search_form.street_id = "";
                this.areaLoading = true;
                let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
                this.$http.get(url).then(response => {
                    if (response.data.data.length) {
                        this.city_list = response.data.data;
                    } else {
                        this.city_list = null;
                    }
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            // 市改变
            changeCity(val) {
                this.district_list = [];
                this.street_list = [];
                this.search_form.district_id = "";
                this.search_form.street_id = "";
                this.areaLoading = true;
                let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
                this.$http.get(url).then(response => {
                    if (response.data.data.length) {
                        this.district_list = response.data.data;
                    } else {
                        this.district_list = null;
                    }
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            // 区改变
            changeDistrict(val) {
                // console.log(val)
                this.street_list = [];
                this.search_form.street_id = "";
                this.areaLoading = true;
                let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
                this.$http.get(url).then(response => {
                    if (response.data.data.length) {
                        this.street_list = response.data.data;
                    } else {
                        this.street_list = null;
                    }
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            gotoDetail(item) {
                // let link = this.detailUrl +'&id='+item.id;
                let link = item.fixed_button.detail.api +'&id='+item.id+'&order_id='+item.id;
                // window.location.href = link;
                window.open(link);
            },
            // 关闭订单
            closeOrder(id,item) {
                this.close_order_show = true;
                this.close_order_con = "";
                this.close_order_id = id;
                this.close_order_api = item.fixed_button.close.api;
                // console.log(this.close_order_api)
            },
             // 确认关闭订单
            sureCloseOrder() {
                let json = {
                    // route:'order.vue-operation.close',
                    order_id:this.close_order_id,
                    reson:this.close_order_con,
                };
                // console.log(json);
                let loading = this.$loading({target:document.querySelector("#close-order"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.close_order_api,json).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success',message: '关闭订单成功!'});
                    }
                    else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                    loading.close();
                    this.close_order_show = false;
                    this.search(this.current_page);
                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});
                    loading.close();
                    this.close_order_show = false;
                })
            },
            // 手动退款并关闭订单
            closeOrder1(id,item) {
                this.close_order1_show = true;
                this.close_order1_con = "";
                this.close_order1_id = id;
                this.close_order1_api = item.fixed_button.manualRefund.api;

            },
             // 手动确认关闭订单
            sureCloseOrder1() {
                let json = {
                    route:'order.vue-operation.manualRefund',
                    order_id:this.close_order1_id,
                    reson:this.close_order1_con,
                }
                // console.log(json);
                let loading = this.$loading({target:document.querySelector("#close-order"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.close_order1_api,json).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success',message: '关闭订单成功!'});
                    }
                    else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                    loading.close();
                    this.close_order1_show = false;
                    this.search(this.current_page);
                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});
                    loading.close();
                    this.close_order1_show = false;
                })
            },

            // 部分退款
            partRefund(id,item) {

                this.$http.post("{!! yzWebFullUrl('order.detail.order-goods-part-refund') !!}",{
                    order_id:id,
                }).then(function (response) {
                    if (response.data.result) {
                        let list = [];
                        let orderGoods = response.data.data;
                        orderGoods.forEach((item,index) => {
                            if(item.refundable_total > 0){

                                let new_title = item.title;
                                if (item.title.length > 15) {
                                    new_title = item.title.substring(0, 15) + '...';
                                }

                                list.push({id:item.id,thumb:item.thumb,title:new_title,total:item.refundable_total,remark:item.refundable_total,unit_price:item.unit_price,checked:false,option:item.goods_option_title});
                            }
                        });

                        this.part_refund_goods = list;
                    } else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});

                });

                // var list = [];
                // item.has_many_order_goods.forEach((item,index) => {
                //     if(item.title.length > 15){
                //       var new_title = item.title.substring(0,15)+'...';
                //     }else{
                //       var new_title = item.title;
                //     }
                //     if(item.is_refund != 1){
                //         list.push({id:item.id,thumb:item.thumb,title:new_title,total:item.total,remark:item.total,price:item.payment_amount,checked:false,option:item.goods_option_title});
                //     }
                // });
                // this.part_refund_goods = list;
                this.part_refund_id = id;
                this.part_refund_api = item.fixed_button.partRefund.api;
                this.part_refund_con = "";//部分退款原因
                this.part_refund_price = 0;
                this.part_refund_show = true;
            },
            changeCheck(ev,row) {
                let newArr = this.part_refund_goods.filter(item => item.checked).map(citem => {
                    return {'id': citem.id, 'total': citem.total, 'unit_price': citem.unit_price, 'remark': citem.remark};
                });
                var sum = 0;
                newArr.forEach((item, index) => {
                    sum = sum + item.unit_price * item.total;
                });
                this.part_refund_arr = newArr;
                this.part_refund_price = Math.floor(sum * 100) / 100;
            },
            allCheck(e) {
                if (e) {
                    this.part_refund_goods.map(item => {
                        return item.checked = true;
                    });
                    let newArr = this.part_refund_goods.filter(item => item.checked).map(citem => {
                        return {'id': citem.id, 'total': citem.total, 'unit_price': citem.unit_price, 'remark': citem.remark};
                    });
                    var sum = 0;
                    newArr.forEach((item, index) => {
                        sum = sum + item.unit_price * item.total;
                    });
                    this.part_refund_price = Math.floor(sum * 100) / 100;
                } else {
                    this.part_refund_goods.map(item => {
                        return item.checked = false;
                    });
                    this.part_refund_price = 0;
                }
            },
            surePartRefund() {
                let newArr = this.part_refund_goods.filter(item => item.checked).map(citem => {
                    return {'id':citem.id,'total':citem.total};
                 });
                if(newArr.length <= 0){
                    this.$message({type: 'error',message: '请选择退款商品'});
                    return false;
                }
                let json = {
                    content:this.part_refund_con,
                    order_goods:newArr,
                    freight_price:"0",
                    order_id:this.part_refund_id,
                    other_price:0,
                    part_refund:1,
                    reason:'后台退款',
                    receive_status:0,
                    refund_type:0,
                    refund_way_type:0,
                };
                // console.log(json); return false;
                let loading = this.$loading({target:document.querySelector("#close-order"),background: 'rgba(0, 0, 0, 0)'});
                let newUrl = "{!! yzWebFullUrl('refund.pay') !!}";
                this.$http.post(this.part_refund_api,json).then(function (response) {
                    if (response.data.result) {
                        window.location.href = newUrl+'&refund_id='+response.data.data;
                    } else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                    loading.close();
                    this.part_refund_show = false;
                    this.search(this.current_page);
                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});
                    loading.close();
                    this.part_refund_show = false;
                })
            },

            orderConfirm(operationType, order) {
                // console.log(operationType, order);
                this.dialog_show++;
                this.operationOrder = order;
                this.operationType = operationType;


            },
            // 修改价格
            changePrice(id,item) {
                this.change_price_show = true;
                this.change_price_id = id;
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.change-order-price.index-api') !!}',{order_id:this.change_price_id}).then(function (response) {
                        if (response.data.result) {
                            // this.$message({type: 'success',message: '操作成功'});
                            this.order_goods_model = response.data.data.order_goods_model;
                            this.order_goods_model.forEach((item,index) => {
                                this.order_goods_model[index].new_price = 0.00
                            })
                            this.order_model = response.data.data.order_model;
                            this.dispatch_price = this.order_model.dispatch_price;
                            this.getNewPrice();

                        }
                        else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    }
                );
            },
            // 打印小票
            orderPrinter(id) {
                this.change_price_id = id;
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('order.order-list.orderPrinter') !!}',{order_id:this.change_price_id}).then(function (response) {
                        if (response.data.result) {
                             this.$message({type: 'success',message: '请求成功'});
                        }
                        else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    }
                );
            },
            // 输入改价
            inputPrice(index,price) {
                // 强制刷新
                if(index != 'dispatch_price') {
                    this.order_goods_model.push({});
                    this.order_goods_model.splice(this.order_goods_model.length-1,1)
                }
                if (this.judgeSign(price) == -1) {
                    this.$message.error("请输入数字");
                    if(index == 'dispatch_price') {
                        this.dispatch_price = 0.00;
                    }
                    else {
                        this.order_goods_model[index].new_price = 0.00
                    }
                    return false;
                }
                this.getNewPrice();
            },
            getNewPrice() {
                let new_price = 0;
                this.order_goods_model.forEach((item,index) => {
                    new_price += parseFloat(item.new_price);
                })
                // console.log(new_price)
                this.change_all_money =  new_price;
                this.all_money = (parseFloat(this.order_model.price)-(parseFloat(this.order_model.dispatch_price))) + parseFloat(new_price) + parseFloat(this.dispatch_price);
                // this.
            },
            // 直接免运费
            clearDispatch() {
                this.dispatch_price = 0.00;
                this.getNewPrice();
            },
            // 确认改价
            sureChangePrice() {
                let json = {
                    order_id:this.change_price_id,
                    dispatch_price:this.dispatch_price,
                    order_goods:[],
                }
                this.order_goods_model.forEach((item,index) => {
                    json.order_goods.push({order_goods_id:item.id,change_price:item.new_price});
                })
                // console.log(json);
                let loading = this.$loading({target:document.querySelector("#change-price"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('order.change-order-price.store-api') !!}',json).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success',message: '修改价格成功!'});
                    }
                    else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                    loading.close();
                    this.change_price_show = false;
                    this.search(this.current_page);
                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});
                    loading.close();
                    this.change_price_show = false;
                })
            },
            // 判断是否是数字
            judgeSign(num) {
                var reg = new RegExp("^-?[0-9]*.?[0-9]*$");
                if ( reg.test(num) ) {
                    var absVal = Math.abs(num);
                    return num==absVal?'是正数':'是负数';
                }
                else {
                    return -1;
                }
            },

            //一键发货
            sureconfirmBatchSend()
            {
                let requestData = {
                    code: this.code,
                    search: JSON.parse(JSON.stringify(this.search_form)),
                    batch_send: JSON.parse(JSON.stringify(this.batch_send)),
                };
                // console.log(requestData);
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(`{!! yzWebFullUrl('order.order-list.batchSend') !!}`,requestData).then(function(response) {
                    if (response.data.result) {
                        this.$message({
                            message: response.data.msg,
                            type: 'success'
                        });
                        setTimeout(function () {
                            location.reload();
                            }, 2000
                        );

                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                    loading.close();

                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                });
            },

            export1(export_type,template,exportUrl = ''){
                let that = this;
                // console.log(that.search_form);

                let json = that.search_form;

                // if(this.times && this.times.length>0) {
                //     json.start_time = this.times[0];
                //     json.end_time = this.times[1];
                // }

                let url = exportUrl ? exportUrl : this.exportUrl;

                url += "&export_type="+export_type+"&template="+template;

                if (that.code) {
                    url+="&code="+that.code
                }

                for(let i in json){
                    if (json[i]) {
                        if(i==='goods_title'){
                            let replace = json[i].replace(/\+/g,'%2B');
                            url+="&search["+i+"]="+ replace
                        }else{
                            url+="&search["+i+"]="+ json[i]
                        }
                    }
                }

                // console.log(url);
                window.location.href = url;
            },
            gotoGoods(id,item) {
                // console.log(item)
                if(!item.fixed_button.goods_detail.is_show) {
                    return
                }
                let link = item.fixed_button.goods_detail.api
                // window.location.href = `{!! yzWebFullUrl('goods.goods.edit') !!}`+`&id=`+id;
                window.location.href = link+`&id=`+id;
            },
            gotoMember(id) {
                window.location.href = `{!! yzWebFullUrl('member.member.detail') !!}`+`&id=`+id;
            },

            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },
            getParam(name) {
                var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                var r = window.location.search.substr(1).match(reg);
                if (r != null) return unescape(r[2]);
                return null;
            },
            // 将标准时间转为字符串
            formatTime(date) {
                let y = date.getFullYear()
                let m = date.getMonth() + 1
                m = m < 10 ? '0' + m : m
                let d = date.getDate()
                d = d < 10 ? '0' + d : d
                let h = date.getHours()
                h = h < 10 ? '0' + h : h
                let minute = date.getMinutes()
                minute = minute < 10 ? '0' + minute : minute
                let second = date.getSeconds()
                second = second < 10 ? '0' + second : second
                return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second
            },
            reloadList() {
                this.search(this.current_page)
            }
        },
    })
</script>
@endsection
