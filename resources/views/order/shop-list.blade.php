@extends('layouts.base')
@section('title', "订单列表")
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
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

    .el-popover{
        width: 90px;
        min-width: 90px;
    }
    .el-form-item {
        margin-bottom: 5px;
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
                <el-form :inline="true" :model="search_form" class="demo-form-inline">

                    {{--自定义添加搜索项--}}
                    {{--@foreach(\app\backend\modules\order\services\OrderViewService::searchTerm() as $key => $value)--}}
                        {{--@if ($value['type'] == 'text')--}}
                            {{--<el-form-item label="">--}}
                                {{--<el-input v-model="search_form.{{$value['name']}}" placeholder="{{$value['placeholder']}}"></el-input>--}}
                            {{--</el-form-item>--}}
                        {{--@endif--}}
                    {{--@endforeach--}}

                    <el-form-item v-if="extra_param.package_deliver" label="">
                        <el-input v-model="search_form.package_deliver_id" placeholder="自提点ID"></el-input>
                    </el-form-item>
                    <el-form-item v-if="extra_param.package_deliver" label="">
                        <el-input v-model="search_form.package_deliver_name" placeholder="自提点名称"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.member_id" placeholder="购买会员ID"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.member_info" placeholder="购买昵称/姓名/手机号"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.address_name" placeholder="收货人姓名"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.address_mobile" placeholder="收货人手机号"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.address" placeholder="收货地址"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.express" placeholder="快递单号"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.goods_id" placeholder="商品ID"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.goods_title" placeholder="商品名称"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.order_sn" placeholder="订单编号"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.parent_id" placeholder="上级ID"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.pay_sn" placeholder="支付单号"></el-input>
                    </el-form-item>

                    {{--订单类型--}}
                    {{--<el-form-item label="">--}}
                        {{--<el-select v-model="search_form.plugin_id" clearable placeholder="订单类型" style="width:150px">--}}
                            {{--@foreach((new \app\backend\modules\order\services\OrderViewService)->getOrderType() as $order_type)--}}
                                {{--<el-option label="{{$order_type['name']}}" value="{{$order_type['plugin_id']}}"></el-option>--}}
                            {{--@endforeach--}}
                        {{--</el-select>--}}
                    {{--</el-form-item>--}}

                    <el-form-item label="">
                        <el-select v-model="search_form.first_order" clearable placeholder="是否搜索首单" style="width:150px">
                            <el-option label="搜索首单" value="1"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select v-model="search_form.order_status" clearable placeholder="订单状态" style="width:150px">
                            <el-option label="待支付" value="waitPay"></el-option>
                            <el-option label="待发货" value="1"></el-option>
                            <el-option label="待收货" value="2"></el-option>
                            <el-option label="已完成" value="3"></el-option>
                            <el-option label="已关闭" value="-1"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select v-model="search_form.pay_type" clearable placeholder="支付方式" style="width:150px">
                            @foreach(\app\backend\modules\order\services\OrderViewService::searchablePayType() as $pay_type)
                                <el-option label="{{$pay_type['name']}}" value="{{$pay_type['value']}}"></el-option>
                            @endforeach
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select v-model="search_form.pay_type_group" clearable placeholder="支付方式组" style="width:150px">
                            @foreach(\app\backend\modules\order\services\OrderViewService::payTypeGroup() as $pay_type_group)
                                <el-option label="{{$pay_type_group['name']}}" value="{{$pay_type_group['id']}}"></el-option>
                            @endforeach
                            {{--<el-option label="微信支付" value="1"></el-option>--}}
                            {{--<el-option label="支付宝支付" value="2"></el-option>--}}
                            {{--<el-option label="余额支付" value="3"></el-option>--}}
                            {{--<el-option label="后台付款" value="4"></el-option>--}}
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select v-model="search_form.time_field" clearable placeholder="操作时间" style="width:150px">
                            <el-option label="下单时间" value="create_time"></el-option>
                            <el-option label="支付时间" value="pay_time"></el-option>
                            <el-option label="发货时间" value="send_time"></el-option>
                            <el-option label="完成时间" value="finish_time"></el-option>
                            <el-option label="关闭时间" value="cancel_time"></el-option>
                            <el-option label="退款申请时间" value="refund_create_time"></el-option>
                            <el-option label="退款完成时间" value="refund_finish_time"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-date-picker
                            v-model="times"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            style="margin-left:5px;"
                            align="right">
                        </el-date-picker>
                    </el-form-item>
                    @if(app('plugins')->isEnabled('electronics-bill'))
                        <el-form-item label="">
                            <el-select v-model="search_form.bill_print" clearable placeholder="电子面单打印状态">
                                <el-option label="已打印" value="print"></el-option>
                                <el-option label="未打印" value="not_print"></el-option>
                            </el-select>
                        </el-form-item>
                    @endif
                    @if(app('plugins')->isEnabled('invoice') && \Setting::get('plugin.invoice.is_open')==1)
                        <el-form-item label="">
                            <el-select v-model="search_form.is_invoice" clearable placeholder="是否需要开票">
                                <el-option label="是" value="1"></el-option>
                                <el-option label="否" value="0"></el-option>
                            </el-select>
                        </el-form-item>
                    @endif
                    <el-form-item label="">
                        <el-button type="primary" @click="search(1)">搜索</el-button>
                        <el-button  @click="export1(1,1)">导出(旧)</el-button>
                        <el-button  @click="export1(1,2)">导出(新)</el-button>
                        <el-button v-if="extra_param.team_dividend"  @click="export1(2)">导出直推（经销商）</el-button>
                    </el-form-item>
                </el-form>

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

                            <div v-if="item.no_refund" class="vue-ellipsis" style="color:red;max-width:200px">
                                <strong>不可退款</strong>&nbsp;&nbsp;&nbsp;
                            </div>

                            <div v-if="item.manual_refund_log != null" class="vue-ellipsis" style="color:red;max-width:200px">
                                <strong>退款并关闭订单</strong>&nbsp;&nbsp;&nbsp;
                            </div>

                            <div v-for="(topContent,topKey1) in item.top_row" :key="topKey1" class="vue-ellipsis" style="color:#29BA9C;max-width:230px" >
                                <strong>[[topContent]]</strong>&nbsp;&nbsp;&nbsp;
                            </div>
                        </div>

                        <div style="flex:1;text-align:right;min-width:150px;">
                            <a @click="closeOrder(item.id,item)" style="color:#29BA9C;font-size:13px;font-weight:600" v-if="item.fixed_button.close.is_show">关闭订单</a>
                            <a @click="closeOrder1(item.id,item)" style="color:#29BA9C;font-size:13px;font-weight:600" v-if="item.fixed_button.manualRefund.is_show" >退款并关闭订单</a>
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
                                </div>
                                <div class="list-con-goods-price">
                                    <div>原价：[[item1.goods_price]]</div>
                                    <div>应付：[[item1.price]]</div>
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
                                <div>会员([[item.uid]])已被删除</div>
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="text-align:center;min-width: 90px;">
                            <div><strong>[[item.pay_type_name]]</strong></div>
                            <div><strong v-if="item.has_one_dispatch_type">[[item.has_one_dispatch_type.name]]</strong></div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="min-width: 120px;">
                            <div style="min-width:70%;margin:0 auto">
                                <div>商品小计：￥[[item.goods_price]]</div>
                                <div>运费：￥[[item.dispatch_price]]</div>
                                <div v-if="item.change_price!='0.00'">卖家改价：￥[[item.change_price]]</div>
                                <div v-if="item.change_dispatch_price!='0.00'">卖家改运费：￥[[item.change_dispatch_price]]</div>
                                <div>应付款：￥[[item.price]]</div>
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="text-align:center">
                            <div style="min-width:70%;margin:0 auto">
                                <div style="color:#29BA9C">[[item.status_name]]</div>
                                <!-- <div v-if="item.status==0">
                                    <a style="color:#29BA9C" @click="changePrice(item.id,item)">修改价格</a>
                                </div>
                                <div>
                                    <a @click="gotoDetail(item)" style="color:#29BA9C">查看详情</a>
                                </div> -->
                            </div>
                        </div>
                        <div class="list-con-member-info vue-ellipsis" style="text-align:center;min-width: 80px;border-right:0">
                            <!-- 未付款 -->
                            {{--<div v-if="item.status==0">
                                <el-button @click="confirmPay(item.order_id)" size="mini" type="primary" style="margin-bottom:5px;width:80%;margin:0 auto">确认付款</el-button>
                            </div>--}}
                            <!-- 待发货 -->
                            {{--<div v-if="item.status==1 && item.dispatch_type_id==1">
                                <el-button @click="confirmSend(item.order_id,item)" size="mini" type="primary" style="width:80%;margin:0 auto;margin-bottom:5px;">确认发货</el-button><br>
                                <el-button @click="cancelSend(item.order_id)" plain size="mini" type="danger" style="width:80%;margin:0 auto;margin-bottom:5px;">取消发货</el-button>
                            </div>
                            <div v-if="item.status==2 && item.dispatch_type_id==1">
                                <el-button @click="confirmReceive(item.order_id)" size="mini" type="primary" style="width:80%;margin:0 auto;margin-bottom:5px;">确认收货</el-button><br>
                            </div>
                            <div v-if="item.status==1 && item.dispatch_type_id==3">
                                <el-button @click="confirmSend(item.order_id,item)" size="mini" type="primary" style="width:80%;margin:0 auto;margin-bottom:5px;">确认发货</el-button>
                            </div>
                            <div v-if="item.status==2 && (item.dispatch_type_id==2 ||item.dispatch_type_id==3)">
                                <el-button @click="confirmReceive(item.order_id)" size="mini" type="primary" style="width:80%;margin:0 auto;margin-bottom:5px;">确认核销</el-button>
                            </div>--}}
                            <div v-for="(item1,index1) in item.backend_button_models" :key="index1">
                                <el-button @click="orderConfirm(item1.value,item)" size="mini" :type="item1.type" style="width:80%;margin:0 auto;margin-bottom:5px;">
                                    [[item1.name]]
                                </el-button>
                            </div>


                            <!-- <el-button plain size="mini" type="danger"  style="margin-bottom:5px;width:80%;margin:0 suto">取消发货</el-button>
                            <el-button size="mini" type="primary"  style="margin-bottom:5px;width:80%;margin:0 suto">确认发货</el-button> -->

                        </div>

                    </div>
                    <div class="list-member">
                        <div v-if="item.address">
                            [[item.address.realname]]&nbsp;&nbsp;&nbsp;[[item.address.mobile]]&nbsp;&nbsp;&nbsp;[[item.address.address]]
                        </div>
                        <div style="justify-content: flex-end;flex:1;display:flex">
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
        <!-- 确认发货 -->
        <!-- <el-dialog :visible.sync="confirm_send_show" width="750px"  title="确认发货">
            <div style="height:400px;overflow:auto" id="confirm-send">
                <el-form ref="send" :model="send" :rules="send_rules" label-width="15%">
                    <el-form-item label="收件人信息" prop="aggregation">
                            <div>收 件 人: [[address_info.realname]] / [[address_info.mobile]]</div>
                            <div>收货地址: [[address_info.address]]</div>
                    </el-form-item>
                    <el-form-item label="配送方式" prop="">
                        <el-radio v-model="send.dispatch_type_id" :label="1">快递</el-radio>
                    </el-form-item>
                    <el-form-item label="快递公司">
                        <el-select v-model="send.express_code" clearable filterable placeholder="快递公司" :disabled='readonly' style="width:70%;">
                            <el-option label="其他快递" value=""></el-option>
                            <el-option v-for="(item,index) in expressCompanies" :key="index" :label="item.name" :value="item.value"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="快递单号" prop="">
                        <el-input v-model="send.express_sn" :disabled='readonly' style="width:70%;"></el-input>
                    </el-form-item>
                </el-form>

            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="confirm_send_show = false">取 消</el-button>
                <el-button type="primary" @click="sureconfirmSend">确认发货 </el-button>
            </span>
        </el-dialog> -->
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
                    <el-table-column label="小计" align="center" prop="price" width="150">
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
                            ￥[[(parseFloat(change_all_money))]]
                        </div>
                        <div style="margin-right:15px">=</div>
                        <div style="margin-right:15px">
                            买家实付<br>
                            <strong style="color:#f00">￥[[(parseFloat(all_money))]]</strong>
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
                times:[],

                //页面返回全部参数
                responseResults:{},

                //插件判断条件
                extra_param:{},

                //订单条件
                code:'all',

                expressCompanies:[],//快递公司
                //搜索条件参数
                search_form:{
                    member_id:"",
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
                change_price_show:false,//修改价格弹窗
                change_price_id:"",
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
            }
        },
        created() {
            let result = this.viewReturn();
            this.__initial(result);
            this.search_form.member_id = this.getParam('member_id')
        },
        mounted() {
            this.getData(1);
        },
        methods: {
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

                console.log(data);
            },
            getData(page) {
                //console.log(this.times);

                // let json = {
                //     status:this.search_form.status,
                //     store_id:this.search_form.package_deliver_id,
                //     store_name:this.search_form.package_deliver_name,
                //     order_sn:this.search_form.order_sn,
                //     pay_sn:this.search_form.pay_sn,
                //     member_id:this.search_form.member_id,
                //     member_info:this.search_form.member_info,
                //     address_name:this.search_form.address_name,
                //     address_mobile:this.search_form.address_mobile,
                //     express:this.search_form.express,
                //     goods_id:this.search_form.goods_id,
                //     goods_title:this.search_form.goods_title,
                //     pay_type:this.search_form.pay_type,
                //     time_field:this.search_form.time_field,
                // };
                let requestData = {
                    page:page,
                    code: this.code,
                    search: JSON.parse(JSON.stringify(this.search_form)),
                };


                if(this.times && this.times.length>0) {
                    requestData.search.start_time = this.times[0];
                    requestData.search.end_time = this.times[1];
                }
                console.log(requestData);

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
            initProvince(val) {
                console.log(val);
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
                console.log(val)
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

            search(val) {
                this.getData(val);
            },
            // // 确认付款
            // confirmPay(id) {
            //     this.$confirm('确认此订单已付款吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
            //         let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
            //         this.$http.post('{!! yzWebFullUrl('order.vue-operation.pay') !!}',{order_id:id}).then(function (response) {
            //             if (response.data.result) {
            //                 this.$message({type: 'success',message: '操作成功'});
            //             }
            //             else{
            //                 this.$message({type: 'error',message: response.data.msg});
            //             }
            //             loading.close();
            //             this.search(this.current_page)
            //         },function (response) {
            //             this.$message({type: 'error',message: response.data.msg});
            //             loading.close();
            //         }
            //     );
            //     }).catch(() => {
            //         this.$message({type: 'info',message: '已取消操作'});
            //     });
            // },
            // 关闭订单
            closeOrder(id,item) {
                this.close_order_show = true;
                this.close_order_con = "";
                this.close_order_id = id;
                this.close_order_api = item.fixed_button.close.api;
                console.log(this.close_order_api)
            },
             // 确认关闭订单
            sureCloseOrder() {
                let json = {
                    // route:'order.vue-operation.close',
                    order_id:this.close_order_id,
                    reson:this.close_order_con,
                };
                console.log(json);
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
                console.log(json);
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
            // // 取消发货
            // cancelSend(id) {
            //     this.cancel_send_show = true;
            //     this.cancel_send_con = "";
            //     this.cancel_send_id = id;
            //     console.log(id)
            // },
            // // 确认取消发货
            // sureCancelSend() {
            //     let json = {
            //         // route:'order.operation.manualRefund',
            //         order_id:this.cancel_send_id,
            //         cancelreson:this.cancel_send_con,
            //     };
            //     console.log(json);
            //     let loading = this.$loading({target:document.querySelector("#cancel-send"),background: 'rgba(0, 0, 0, 0)'});
            //     this.$http.post('{!! yzWebFullUrl('order.vue-operation.cancel-send') !!}',json).then(function (response) {
            //         if (response.data.result) {
            //             this.$message({type: 'success',message: '关闭订单成功!'});
            //         }
            //         else{
            //             this.$message({type: 'error',message: response.data.msg});
            //         }
            //         loading.close();
            //         this.close_order_show = false;
            //         this.search(this.current_page);
            //     },function (response) {
            //         this.$message({type: 'error',message: response.data.msg});
            //         loading.close();
            //         this.close_order_show = false;
            //     })
            // },
            // // 确认收货
            // confirmReceive(id) {
            //     this.$confirm('确认订单收货吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
            //         let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
            //         this.$http.post('{!! yzWebFullUrl('order.vue-operation.receive') !!}',{order_id:id}).then(function (response) {
            //             if (response.data.result) {
            //                 this.$message({type: 'success',message: '操作成功'});
            //             }
            //             else{
            //                 this.$message({type: 'error',message: response.data.msg});
            //             }
            //             loading.close();
            //             this.search(this.current_page)
            //         },function (response) {
            //             this.$message({type: 'error',message: response.data.msg});
            //             loading.close();
            //         }
            //     );
            //     }).catch(() => {
            //         this.$message({type: 'info',message: '已取消操作'});
            //     });
            // },
            // // 确认发货
            // confirmSend(id,item) {

            //     let synchro = this.synchro;
            //     this.readonly = false;
            //     if (synchro){
            //         this.$http.post(this.url_open,{id:id}).then(function (response) {
            //                 if (response.data.result) {
            //                     this.send.express_code = response.data.resp.shipper_code;
            //                     this.send.express_sn = response.data.resp.logistic_code;
            //                     this.readonly = true;
            //                     // this.send.express_code = 1;
            //                    console.log('66666',response.data.resp.logistic_code);
            //                 }
            //             },function (response) {
            //                 this.$message({type: 'error',message: response.data.msg});

            //             }
            //         );
            //     }
            //     console.log('898998',synchro);
            //     this.confirm_send_show = true;
            //     this.confirm_send_con = "";
            //     this.send = {
            //         dispatch_type_id :1,
            //         express_code:"",
            //         express_sn:""
            //     }
            //     this.confirm_send_id = id;
            //     this.address_info = item.address || {};
            // },
            // // 确认确认发货
            // sureconfirmSend() {
            //     let json = {
            //         dispatch_type_id:this.send.dispatch_type_id,
            //         express_code:this.send.express_code,
            //         express_sn:this.send.express_sn,
            //         order_id:this.confirm_send_id,
            //     };
            //     console.log(json);
            //     // if(this.send.express_sn == "") {
            //     //     this.$message.error("快递单号不能为空！");
            //     //     return;
            //     // }
            //     let loading = this.$loading({target:document.querySelector("#cancel-send"),background: 'rgba(0, 0, 0, 0)'});
            //     this.$http.post('{!! yzWebFullUrl('order.vue-operation.send') !!}',json).then(function (response) {
            //         if (response.data.result) {
            //             this.$message({type: 'success',message: '确认发货成功!'});
            //         }
            //         else{
            //             this.$message({type: 'error',message: response.data.msg});
            //         }
            //         loading.close();
            //         this.confirm_send_show = false;
            //         this.search(this.current_page);
            //     },function (response) {
            //         this.$message({type: 'error',message: response.data.msg});
            //         loading.close();
            //         this.confirm_send_show = false;
            //     })
            // },
            // //多包裹发货
            // separateSend(id,item) {
            //     this.more_send_show = true;
            //     this.confirm_send_id = id;
            //     this.send = {
            //         dispatch_type_id :1,
            //         express_code:"",
            //         express_sn:"",
            //     };

            //     this.address_info = item.address || {};
            //     this.getSeparateSendOrderGoods(id);
            // },
            // // 多包裹确认发货 选择商品
            // moreSendChange(selection) {
            //     let arr = [];
            //     for(let j = 0,len = selection.length; j < len; j++){
            //         console.log(selection[j].id);
            //         arr.push(selection[j].id);
            //     }
            //     this.send_order_goods_ids = arr;
            // },
            // // 获取可选择的商品 多包裹发货
            // getSeparateSendOrderGoods(id) {
            //     this.$http.post('{!! yzWebFullUrl('order.multiple-packages-order-goods.get-order-goods') !!}', {order_id:id}).then(function (response) {
            //         if (response.data.result) {
            //             this.order_goods_send_list = response.data.data;
            //             // console.log(this.order_goods_send_list);
            //         } else{
            //             this.$message({type: 'error',message: response.data.msg});
            //         }
            //     },function (response) {
            //         this.$message({type: 'error',message: response.data.msg});
            //     });
            // },
            // //多包裹确认发货
            // confirmMoreSend() {

            //     if(this.send_order_goods_ids == undefined || this.send_order_goods_ids.length <= 0) {
            //         this.$message.error("请选择分批发货订单商品！");
            //         return;
            //     }

            //     if(this.send.express_sn == "") {
            //         this.$message.error("快递单号不能为空！");
            //         return;
            //     }

            //     let json = {
            //         dispatch_type_id:this.send.dispatch_type_id,
            //         express_code:this.send.express_code,
            //         express_sn:this.send.express_sn,
            //         order_id:this.confirm_send_id,
            //         order_goods_ids:this.send_order_goods_ids,
            //     };
            //     console.log(json);

            //     let loading = this.$loading({target:document.querySelector("#separate-send"),background: 'rgba(0, 0, 0, 0)'});
            //     this.$http.post('{!! yzWebFullUrl('order.vue-operation.separate-send') !!}',json).then(function (response) {
            //         if (response.data.result) {
            //             this.$message({type: 'success',message: '发货成功!'});
            //         } else{
            //             this.$message({type: 'error',message: response.data.msg});
            //         }
            //         loading.close();
            //         this.more_send_show = false;
            //         location.reload(); //刷新页面
            //     },function (response) {
            //         this.$message({type: 'error',message: response.data.msg});
            //         loading.close();
            //         this.more_send_show = false;
            //     })
            // },
            orderConfirm(operationType, order) {
                console.log(operationType, order);
                this.dialog_show++;
                this.operationOrder = order;
                this.operationType = operationType;

                // if (operationType == 1) {
                //     this.confirmPay(order.id);
                // } else if (operationType == 2) {
                //     this.confirmSend(order.id, order);
                // } else if (operationType == 3) {
                //     this.confirmReceive(order.id);
                // } else if (operationType == 'cancel_send') {
                //     this.cancelSend(order.id);
                // } else if (operationType == 'separate_send') {
                //     this.separateSend(order.id, order);
                // }

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
                console.log(new_price)
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
                console.log(json);
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

            export1(export_type,template){
                let that = this;
                console.log(that.search_form);

                let json = that.search_form;

                if(this.times && this.times.length>0) {
                    json.start_time = this.times[0];
                    json.end_time = this.times[1];
                }

                let url = this.exportUrl;

                url += "&export_type="+export_type+"&template="+template;

                if (that.code) {
                    url+="&code="+that.code
                }

                for(let i in json){
                    if (json[i]) {
                        url+="&search["+i+"]="+ json[i]
                    }
                }

                console.log(url);
                window.location.href = url;
            },
            gotoGoods(id,item) {
                console.log(item)
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
            reloadList() {
                this.search(this.current_page)
            }
        },
    })
</script>
@endsection