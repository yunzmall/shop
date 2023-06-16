@extends('layouts.base')
@section('title', '售后详情')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=QXSZyPZk26shrYzAXjTkDLx5LbRCHECz"></script>
    <style>
        body{font-weight:500;color:#333;}
        .all{background:#eff3f6}

        .el-form-item__label{margin-right:30px}
        .vue-main-form .el-form-item__content{margin-left:calc(15% + 30px) !important}
        .order-sum-li{width:16.66%;line-height:28px}
        .list-con-goods-text{min-height:70px;overflow:hidden;flex:1;display: flex;flex-direction: column;justify-content: space-between;}
        .list-con-goods-price{border-right:1px solid #e9e9e9;border-left:1px solid #e9e9e9;min-width:150px;min-height:90px;text-align: left;padding:20px;display: flex;flex-direction: column;}
        .list-con-goods-title{font-size:14px;line-height:20px;text-overflow: -o-ellipsis-lastline;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 2;line-clamp: 2;-webkit-box-orient: vertical;}
        .list-con-goods-option{font-size:12px;color:#999}
        .list-con-goods-after-sales{font-size:12px;color:red}
    </style>
    <div class="all">
        <div id="app" v-cloak>

            <div class="vue-crumbs">
                <a>订单管理</a> > 售后详情
            </div>
            <el-form ref="form" :model="form" label-width="15%">
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基本信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item  label="购买会员" prop="store_name">
                            <div v-if="refundDetail.has_one_member != null" style="display:flex">
                                <div style="width:100px;height:100px">
                                    <img :src="refundDetail.has_one_member.avatar_image" alt="" style="width:100%;height:100%">
                                </div>
                                <div style="line-height:33px;margin-left:15px">
                                    <div>(会员ID:[[refundDetail.uid]]) [[refundDetail.has_one_member.nickname]]</div>
                                    <div>[[refundDetail.has_one_member.realname]]</div>
                                    <div>[[refundDetail.has_one_member.mobile]]</div>
                                </div>
                            </div>

                            {{--<div v-if="" style="display:flex">--}}
                                {{--<div>(会员ID:[[refundDetail.uid]]) 已被合并为会员ID [[yz_member.mark_member_id]]</div>--}}
                            {{--</div>--}}

                        </el-form-item>
                        <el-form-item label="售后编号">
                            <div>[[refundDetail.order_sn]]</div>
                        </el-form-item>
                        <el-form-item label="订单编号">
                            <div>
                                <a @click="gotoOrderDetail()" style="color:#29BA9C">[[refundDetail.order.order_sn]]</a>
                            </div>
                        </el-form-item>
                        <el-form-item label="订单金额">
                            <div>[[refundDetail.order.price]]</div>
                        </el-form-item>
                        <el-form-item label="支付方式" prop="">
                            <div style="display:inline-block;margin-right:20px">[[refundDetail.order.pay_type_name]]</div>
                        </el-form-item>
                        <el-form-item label="订单状态" prop="">
                            <div style="display:inline-block;margin-right:20px">[[refundDetail.order.status_name]]</div>
                        </el-form-item>
                        <el-form-item label="物流信息" prop="" v-if="refundDetail.order.dispatch != null && refundDetail.order.dispatch.length > 0">
                            <el-button size="small" type="primary" @click="showDispatchInfo()">查看</el-button>
                        </el-form-item>
                    </div>
                </div>

                <!-- 售后商品 -->
                <div v-if="refundDetail.refund_order_goods !== undefined && refundDetail.refund_order_goods.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">售后商品</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="" prop="">
                            <el-table :data="refundDetail.refund_order_goods" style="width: 100%">
                                {{--<el-table-column prop="goods_id" label="商品ID" width="100" align="center"></el-table-column>--}}
                                <el-table-column prop="" label="" width="100" align="center">
                                    <template slot-scope="scope">
                                        <img :src="scope.row.goods_thumb" style="width:50px;height:50px;">
                                    </template>
                                </el-table-column>
                                <el-table-column prop="down_time" label="" min-width="180" align="left">
                                    <template slot-scope="scope">
                                        <div class="list-con-goods-title"
                                             style="color:#29BA9C;cursor: pointer;"
                                             @click="gotoGoods(scope.row.goods_id)">
                                            [[scope.row.goods_title]]
                                        </div>
                                        <div class="list-con-goods-option">规格： [[scope.row.goods_option_title]]</div>
                                        <div class="list-con-goods-option">数量： [[scope.row.refund_total]]</div>

                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-form-item>
                    </div>
                </div>

                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">售后申请</div>
                    </div>
                    <div  class="vue-main-form">
                        <el-form-item label="售后状态" prop="">
                            <div style="display:inline-block;margin-right:20px">[[refundDetail.status_name]]</div>
                            <div style="display:inline-block;margin-right:10px" v-for="(button1,key1) in refundDetail.backend_button_models" :key="key1">
                                <el-tooltip style="max-width: 150px" effect="dark"
                                            :disabled="!(button1.desc !== undefined && button1.desc.length > 0)"
                                            placement="top-start">
                                    <template slot="content">
                                        <div v-if="typeof(button1.desc) != 'string' && button1.desc.length > 0">
                                            <div v-for="(content1,k1) in button1.desc">[[content1]]<br/></div>
                                        </div>
                                        <div v-else>[[button1.desc]]</div>
                                    </template>
                                    <el-button @click="refundConfirm(button1.value)"  size="small" plain :type="button1.type">
                                        [[button1.name]]
                                    </el-button>
                                </el-tooltip>

                            </div>
                        </el-form-item>
                        <el-form-item label="" prop="">
                            <el-steps  align-center style="width:80%">
                                <el-step v-for="(process_item1,processIndex1) in refundDetail.refundSteps"
                                         :key="processIndex1"
                                         :icon="process_item1.icon"
                                         :status="process_item1.status"
                                         :title="process_item1.title">
                                    <template slot="description">
                                        <div>[[process_item1.desc]]</div>

                                        <template v-if="process_item1.value == 20 && refundDetail.return_express != null">
                                            <div>快递名称：[[refundDetail.return_express.express_company_name]]</div>
                                            <div>快递单号：[[refundDetail.return_express.express_sn]]</div>
                                            <el-button plain size="mini" type="primary" @click="selectRefundLogistics(process_item1.value)">查看物流</el-button>
                                        </template>

                                        <template v-if="process_item1.value == 30 && (refundDetail.has_many_resend_express != null && refundDetail.has_many_resend_express.length > 0)">
                                            <div v-if="refundDetail.has_many_resend_express.length == 1">
                                                <div>快递名称：[[refundDetail.has_many_resend_express[0].express_company_name]]</div>
                                                <div>快递单号：[[refundDetail.has_many_resend_express[0].express_sn]]</div>
                                            </div>
                                            <el-button plain size="mini" type="primary" @click="selectRefundLogistics(process_item1.value)">查看物流</el-button>
                                        </template>

                                    </template>
                                </el-step>
                            </el-steps>
                        </el-form-item>
                        <el-form-item label="售后类型" prop="">
                            <div>[[refundDetail.refund_type_name]]</div>
                        </el-form-item>
                        <el-form-item label="收货状态" prop="">
                            <div>[[refundDetail.receive_status_name]]</div>
                        </el-form-item>
                        <el-form-item label="退货方式" prop="" v-if="refundDetail.refund_type == 2">
                            <div>[[refundDetail.refund_way_type_name]]</div>
                        </el-form-item>
                        <el-form-item v-if="refundDetail.refund_type != 2" label="退款金额" prop="">
                            <div>
                                ￥[[refundDetail.price]](包含运费￥[[refundDetail.freight_price]]，其他费用￥[[refundDetail.other_price]])
                                <el-tag v-if="refundDetail.change_log != null" size="small" type="danger">改价</el-tag>
                                <template v-if="refundDetail.status < 6">
                                    <a style="color:#29BA9C;padding-left: 15px" @click="refundChangePriceShow()">修改金额</a>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item label="退款原因" prop="">
                            <div>[[refundDetail.reason]]</div>
                        </el-form-item>
                        <el-form-item label="说明" prop="">
                            <div>[[refundDetail.content]]</div>
                        </el-form-item>
                        <el-form-item v-if="refundDetail.images != null && refundDetail.images.length > 0" label="图片凭证" prop="">
                            <div>
                                <el-image style="width: 150px" v-for="(refund_img,imgskey3) in refundDetail.images" :src="refund_img"></el-image>
                            </div>
                        </el-form-item>
                    </div>
                    {{--<div class="vue-main-form" v-if="!(refundDetail != null && Object.keys(refundDetail).length > 0)">--}}
                        {{--<el-form-item label="协商记录" prop="">--}}
                            {{--<a style="color:#29BA9C;" @click="showRefundProcessLog">查看</a>--}}
                        {{--</el-form-item>--}}
                    {{--</div>--}}
                </div>

                <div>
                    <div class="vue-head">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">协商记录</div>
                        </div>
                        <div class="vue-main-form">
                            <el-form-item label="" prop="">
                                <a style="color:#29BA9C;" @click="showRefundProcessLog">查看</a>
                            </el-form-item>
                        </div>
                    </div>
                </div>

                <!-- 手续费 -->
                {{--<div v-if="orderDetail.order_fees != null && orderDetail.order_fees.length > 0" class="vue-head">--}}
                    {{--<div class="vue-main-title">--}}
                        {{--<div class="vue-main-title-left"></div>--}}
                        {{--<div class="vue-main-title-content">手续费信息</div>--}}
                    {{--</div>--}}
                    {{--<div class="vue-main-form">--}}
                        {{--<el-form-item label="" prop="">--}}
                            {{--<table class="el-table" style="width:70%;">--}}
                                {{--<thead>--}}
                                {{--<tr>--}}
                                    {{--<td class="is-center">手续费名称</td>--}}
                                    {{--<td class="is-center">手续费金额</td>--}}
                                {{--</tr>--}}
                                {{--</thead>--}}
                                {{--<tbody>--}}
                                {{--<template v-for="(order_fee,order_fee_key) in orderDetail.order_fees">--}}
                                    {{--<tr>--}}
                                        {{--<td class="is-center">[[order_fee.name]]</td>--}}
                                        {{--<td class="is-center">[[order_fee.amount]]</td>--}}
                                    {{--</tr>--}}
                                {{--</template>--}}
                                {{--</tbody>--}}
                            {{--</table>--}}
                        {{--</el-form-item>--}}
                    {{--</div>--}}
                {{--</div>--}}



                {{--//todo 这来以后可以引入插件的售后信息--}}
                {{--@foreach(\app\common\modules\widget\Widget::current()->getItem('order_refund_detail') as $key1=>$value1)--}}
                    {{--{!! widget($value1['class'], ['order_id'=> request()->input('id')])!!}--}}
                {{--@endforeach--}}


                <!-- 插件控制 -->
                <div v-if="false">
                    <div class="vue-head">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">插件描述</div>
                        </div>
                        <div class="vue-main-form">
                            插件订单内容
                        </div>
                    </div>
                </div>
            </el-form>


            <!-- 查看退货、换货物流信息 -->
            <el-dialog :visible.sync="modal_refund_logistics" width="750px" center title="售后物流信息">
                <div style="height:400px;overflow:auto" v-for="(r_logistics,refund_key1) in refundLogistics">
                    <el-form label-width="15%">
                        <el-form-item label="物流公司" prop="">
                            [[r_logistics.company_name]]
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <div style="display:inline-block;margin-right:20px">[[r_logistics.express_sn]]</div>
                        </el-form-item>
                        <el-form-item label="物流情况" prop="">
                            <template v-if="r_logistics.data != null">
                                <div v-for="(item3, k3) in r_logistics.data"  :key="k3">[[item3.time]] [[item3.context]]</div>
                            </template>

                        </el-form-item>
                    </el-form>
                </div>
                <span slot="footer" class="dialog-footer">
                <el-button @click="modal_refund_logistics = false">关 闭</el-button>
                    {{--<el-button type="primary" @click="confirmRefundReject()">确 认</el-button>--}}
            </span>
            </el-dialog>

            <!-- 查看物流信息 -->
            <el-dialog :visible.sync="modal_dispatch_info" width="750px" center title="物流信息">
                <div style="height:400px;overflow:auto" id="dispatch-info">
                    <!-- 多包裹 -->
                    <div>
                        <div style="display:flex;margin-bottom:15px;" v-for="(item3,index3) in refundDetail.order.dispatch">
                            <div class="left" style="width:105px;margin-right:30px;text-align:right">
                                包裹[[index3 + 1]]信息
                            </div>
                            <div class="right" style="flex:1">
                                <div>
                                    <div class="wl-img" style="width:70px;height:70px;position:relative;">
                                        <img :src="item3.thumb" style="width:100%;height:100%">
                                        <div style="line-height:18px;background:RGB(17, 9, 5,0.7);color:#fff;position:absolute;bottom:0;width:100%;text-align:center">
                                            共[[item3.count]]件
                                        </div>
                                        <div @click="getGoodDetail(item3)" style="position:absolute;bottom:0;right:-80px;cursor: pointer;color:#ff9b19 ">
                                            查看商品
                                        </div>
                                    </div>
                                    <div style="padding:5px 0">公司：[[item3.company_name]]</div>
                                    <div style="padding:5px 0">运单号：[[item3.express_sn]]</div>
                                    <template v-if="item3.data != null">
                                        <div style="padding:5px 0" v-for="(d1, k2) in item3.data"  :key="k2">[[d1.time]] [[d1.context]]</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="modal_dispatch_info = false">关 闭</el-button>
                </span>
            </el-dialog>


            <!-- 查看商品 -->
            <el-dialog :visible.sync="get_goods_show" width="750px"  title="商品列表">
                <div id="separate-send">
                    <el-table ref="multipleTable1"  :data="get_goods_list" tooltip-effect="dark"  height="550" style="width: 100%">
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
                    <el-button @click="get_goods_show = false">取 消</el-button>
                </span>
            </el-dialog>

            <!-- 修改退款金额 -->
            <el-dialog :visible.sync="refund_change_show" center  width="650px"  title="修改退款金额">
                <div style="height:350px;overflow:auto" id="refund-change">
                    <div class="tip" style="color:#f00;margin:10px 0;">提示：修改后退款金额不能小于0元</div>
                    <el-form :inline="true"  label-position="top" size="small" label-width="500px">
                        <el-form-item style="width: 20%" label="商品金额">
                            ￥[[parseFloat(refund_apply_goods_price)]]
                        </el-form-item>
                        <el-form-item style="width: 30%" label="商品金额加或减">
                            <el-input @input="inputPrice('apply_price',refund_change_price)" v-model="refund_change_price" size="small"></el-input>
                        </el-form-item>
                        <el-form-item style="width: 15%" label="运费">
                            <el-input @input="inputPrice('freight_price',refund_change_freight_price)" v-model="refund_change_freight_price" size="small"></el-input>
                        </el-form-item>
                        <el-form-item style="width: 15%" label="其他金额">
                            <el-input @input="inputPrice('other_price',refund_change_other_price)" v-model="refund_change_other_price" size="small"></el-input>
                        </el-form-item>
                    </el-form>

                    <div style="display:inline-block;color:#000;margin-left:20px;margin-top:50px;font-weight:500;">
                        <div style="display:flex;text-align:center;line-height:28px;align-items: center;">
                            <div style="margin-right:15px">
                                退款商品金额<br>
                                ￥[[parseFloat(refund_apply_goods_price)]]
                            </div>
                            <div style="margin-right:15px">+</div>
                            <div style="margin-right:15px">
                                运费<br>
                                ￥[[(parseFloat(refund_change_freight_price))]]
                            </div>
                            <div style="margin-right:15px">+</div>
                            <div style="margin-right:15px">
                                其他金额<br>
                                ￥[[(parseFloat(refund_change_other_price))]]
                            </div>
                            <div style="margin-right:15px">+</div>
                            <div style="margin-right:15px">
                                价格修改<br>
                                ￥[[(parseFloat(refund_change_price))]]
                            </div>
                            <div style="margin-right:15px">=</div>
                            <div style="margin-right:15px">
                                退款金额<br>
                                <strong style="color:#f00">￥
                                    [[parseFloat(refund_price).toFixed(2)]]
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="refund_change_show = false">取 消</el-button>
                    <el-button type="primary" @click="refundChangePrice()">确认修改 </el-button>
                </span>
            </el-dialog>

            <!-- 查看售后商品 -->
            <el-dialog :visible.sync="refund_order_goods_show" center width="600px"  title="售后商品">
                <div style="height:350px;overflow:auto">
                    <el-table :data="refundOrderGoods" style="width: 100%">
                        <el-table-column align="center">
                            <template slot-scope="scope">
                                <img :src="scope.row.goods_thumb" style="width:50px;height:50px;">
                            </template>
                        </el-table-column>
                        <el-table-column min-width="180" align="left">
                            <template slot-scope="scope">
                                <div class="list-con-goods-title"
                                     style="color:#29BA9C;">
                                    [[scope.row.goods_title]]
                                </div>
                                <div class="list-con-goods-option">
                                    [[ scope.row.goods_option_title ? '规格：' + scope.row.goods_option_title : '单规格' ]]
                                </div>
                                <div class="list-con-goods-option">
                                    数量： [[scope.row.refund_total]]
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="refund_order_goods_show = false">取 消</el-button>
                </span>
            </el-dialog>

            <!-- 查看订单协商记录 -->
            <el-dialog :visible.sync="order_refund_process_log_show" center width="700px"  title="协商记录">
                <div style="height:380px;overflow:auto">
                    <div v-for="(processLog, log1) in refundDetail.process_log">
                        <div style="background-color: #f9f9f9;border-radius:5px;margin-top: 12px">
                            <div style="padding:15px">
                                <div style="float:right">[[processLog.created_at]]</div>
                                <div>
                                    <i type="primary"
                                       :class="processLog.operator == 1 ? 'el-icon-user-solid':'el-icon-s-shop'"></i>
                                    [[processLog.operate_name]]
                                </div>
                            </div>
                            <div style="border-bottom:1px solid #e9e9e9;;clear: both;"></div>
                            <div style="padding:15px 20px">
                                <div v-for="(logDetail, log2) in processLog.detail">
                                    <div v-if="logDetail">[[logDetail]]</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="order_refund_process_log_show = false">取 消</el-button>
                </span>
            </el-dialog>


            {{--//售后操作组件--}}
            <refund-order-operation-base
                    :refund-operation-type="refundOperationType"
                    :operation-refund="refundDetail"
                    :refund_dialog_show="refund_dialog_show"
                    :refund-order="refundDetail.order">
            </refund-order-operation-base>

            <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>

        </div>
    </div>


    {{--引入售后组件--}}
    @include('refund.component.refundOrderOperationBase')

    @include('public.admin.uploadfile')

    <script>

        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    id:0,

                    goodsEditUrl: '', //订单商品编辑页跳转链接
                    orderDetailUrl: '',


                    //图片文件上传组件
                    uploadShow:false,
                    chooseImgName:'',
                    submit_url:'',
                    showVisible:false,
                    loading: false,
                    uploadImg1:'',


                    form:{},
                    yz_member:{},
                    total_discount_price:0,
                    expressCompanies:[],//快递公司


                    //退款
                    refundDetail:{}, //退款信息
                    refundOrderGoods:[],
                    refund_change_price:0,
                    refund_apply_goods_price:0,
                    refund_change_other_price:0,
                    refund_change_freight_price:0,
                    refund_price:0,
                    order_refund_process_log_show:false, //协商记录
                    refund_order_goods_show: false, //售后商品列表
                    refund_change_show:false, //修改退款金额
                    modal_refund_logistics: false,
                    modal_dispatch_info: false,//物流信息
                    refundLogistics:[], //退货物流信息

                    get_goods_show:false, //查看商品
                    get_goods_list:[],

                    //售后操作组件
                    refundOperationType:'',
                    operationRefund:{},
                    refund_dialog_show:0,
                    //换货确认发货
                    refund_resend_show:false,
                    refund_resend:{
                        express_code:"",
                        express_sn:"",
                    },
                }
            },
            created() {
                let result = this.viewReturn();
                this.__initial(result);
            },
            mounted() {

            },
            methods: {
                //视图返回数据
                viewReturn() {
                    return {!! $data?:'{}' !!};
                },
                //初始化页面数据，请求链接
                __initial(data) {

                    this.refundDetail = data.refund;


                    if (data.orderDetailUrl) {
                        this.orderDetailUrl = data.orderDetailUrl;
                    }

                    if (data.goodsEditUrl) {
                        this.goodsEditUrl = data.goodsEditUrl;
                    }


                    if (this.refundDetail) {
                        this.refund_apply_goods_price = this.refundDetail.apply_price;
                        this.refund_price = this.refundDetail.price;
                    }
                    console.log(data);
                },

                showRefundProcessLog() {
                    this.order_refund_process_log_show = true;
                },
                selectRefundGoods(refundGoodsLog) {
                    this.refund_order_goods_show = true;
                    this.refundOrderGoods = refundGoodsLog;
                },

                //显示退款物流信息
                selectRefundLogistics(value) {
                    console.log(value);
                    this.modal_refund_logistics = true;
                    let loading = this.$loading({target:document.querySelector("#update-list"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('refund.detail.express') !!}',{refund_id:this.refundDetail.id,refund_value:value}).then(function (response) {
                            if (response.data.result){
                                // console.log(response.data.data);
                                this.refundLogistics = response.data.data;
                                loading.close();
                            } else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    );
                },
                //查询商家发货物流信息
                selectResendLogistics(value) {


                    this.modal_refund_logistics = true;
                    let loading = this.$loading({
                        target: document.querySelector("#update-list"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('refund.detail.express') !!}', {
                        refund_id: this.refundDetail.id,
                        refund_value: value
                    }).then(function (response) {
                            if (response.data.result) {
                                // console.log(response.data.data);
                                this.refundLogistics = response.data.data;
                                loading.close();
                            } else {
                                this.$message({message: response.data.msg, type: 'error'});
                            }
                            loading.close();
                        }, function (response) {
                            this.$message({message: response.data.msg, type: 'error'});
                            loading.close();
                        }
                    );


                },

                //退款操作
                refundConfirm(operationType) {

                    this.refund_dialog_show++;
                    this.operationRefund = this.refundDetail;
                    this.refundOperationType = operationType;

                },
                //正则过滤掉除\-\.0-9以外的字符，不会写
                inputPrice(type,change_price) {

                    if (this.judgeSign(change_price) == -1) {
                        this.$message.error("请输入数字");
                        if(type == 'freight_price') {
                            this.refund_change_freight_price = 0;
                        } else if (type == 'other_price') {
                            this.refund_change_other_price = 0;
                        } else {
                            this.refund_change_price = 0;
                        }
                        return false;
                    }

                    let refund_goods_price = parseFloat(this.refund_apply_goods_price) + parseFloat(this.refund_change_price);
                    this.refund_price = refund_goods_price + parseFloat(this.refund_change_freight_price) + parseFloat(this.refund_change_other_price);
                },
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
                refundChangePriceShow() {
                    this.refund_change_freight_price = this.refundDetail.freight_price;
                    this.refund_change_other_price = this.refundDetail.other_price;
                    this.refund_change_show = true;
                },

                //确认修改退款金额
                refundChangePrice() {

                    // if(this.refund_change_price === '') {
                    //     this.$message.error("修改金额不能为空！");
                    //     return false;
                    // }


                    let json = {
                        'order_id':this.id,
                        'refund_id':this.refundDetail.id,
                        'change_price':this.refund_change_price,
                        'change_freight_price':this.refund_change_freight_price,
                        'change_other_price':this.refund_change_other_price
                    };
                    let loading = this.$loading({target:document.querySelector("#refund-change"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('refund.vue-operation.change-price') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功!'});
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.refund_change_show = false;
                        location.reload();//重新获取数据刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.refund_change_show = false;
                    });
                },


                gotoOrderDetail() {

                    if (this.orderDetailUrl) {
                        let link = this.orderDetailUrl + `&id=` +  this.refundDetail.order.id;
                        window.location.href = link;
                        return;
                    }

                    let link = this.refundDetail.order.fixed_button.detail.api + `&id=` +  this.refundDetail.order.id;

                    window.open(link);
                },

                gotoGoods(id) {

                    if (this.goodsEditUrl) {
                        let link = this.goodsEditUrl + `&id=` +  id;
                        window.location.href = link;
                        return;
                    }

                    if(!this.refundDetail.order.fixed_button.goods_detail.is_show) {
                        return
                    }
                    let link = this.refundDetail.order.fixed_button.goods_detail.api;
                    window.location.href = link+`&id=`+id;

                    //window.open(this.goodsEditLink +`&id=`+id);
                },

                clearImg(str,type,index) {
                    if(!type) {
                        this.form[str] = "";
                        this.form[str+'_url'] = "";
                    }
                    else {
                        this.form[str].splice(index,1);
                        this.form[str+'_url'].splice(index,1);
                    }
                    this.$forceUpdate();
                },
                openUpload(str) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },

                sureImg(name,image,image_url) {
                    this.form[name] = image;
                    this.form[name+'_url'] = image_url;
                    let url = image_url.split('.')
                    console.log(url)

                    if(url[url.length-1] == 'pdf') {
                        this.is_pdf = true;
                    }
                    else {
                        this.is_pdf = false;

                    }
                },
                clearImg(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
                },
                reloadList() {
                    location.reload(); //刷新页面
                },

                reloadList() {
                    location.reload(); //刷新页面
                },

                //查看物流信息
                showDispatchInfo() {
                    this.modal_dispatch_info = true;
                },

                // 查看商品
                getGoodDetail(item) {
                    console.log(item)
                    this.get_goods_show = true;
                    this.get_goods_list = [];
                    this.get_goods_list = item.goods || [];
                },

            },
        })

    </script>
@endsection


