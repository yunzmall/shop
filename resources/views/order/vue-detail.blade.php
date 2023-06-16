@extends('layouts.base')
@section('title', '订单详情')
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
                <a>订单管理</a> > 订单详情
            </div>
            <el-form ref="form" :model="form" label-width="15%">
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基本信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item  label="购买会员" prop="store_name">
                            <div v-if="orderDetail.belongs_to_member != null" style="display:flex">
                                <div style="width:100px;height:100px">
                                    <img :src="orderDetail.belongs_to_member.avatar_image" alt="" style="width:100%;height:100%">
                                </div>
                                <div style="line-height:33px;margin-left:15px">
                                    <div>(会员ID:[[orderDetail.uid]]) [[orderDetail.belongs_to_member.nickname]]</div>
                                    <div>[[orderDetail.belongs_to_member.realname]]</div>
                                    <div>[[orderDetail.belongs_to_member.mobile]]</div>
                                </div>
                            </div>

                            <div v-if="yz_member != null && yz_member.is_old" style="display:flex">
                                <div>(会员ID:[[orderDetail.uid]]) 已被合并为会员ID [[yz_member.mark_member_id]]</div>
                            </div>

                        </el-form-item>
                        <el-form-item label="订单编号">
                            <div>[[orderDetail.order_sn]]</div>
                        </el-form-item>
                        <el-form-item label="订单金额" prop="store_name">
                            <div style="display:flex;width:70%">
                                <div class="order-sum-li">
                                    <div>商品小计</div>
                                    <div>￥[[orderDetail.goods_price]]</div>
                                </div>

                                <div class="order-sum-li">
                                    <div>会员价</div>
                                    <div>￥[[orderDetail.vip_order_goods_price]]</div>
                                </div>
                                <div class="order-sum-li">
                                    <div>运费</div>
                                    <div>￥[[orderDetail.initial_freight]]</div>
                                </div>
                                <div v-if="orderDetail.fee_amount" class="order-sum-li">
                                    <div>手续费</div>
                                    <div>￥[[orderDetail.fee_amount]]</div>
                                </div>
                                <div v-if="orderDetail.service_fee_amount" class="order-sum-li">
                                    <div>服务费</div>
                                    <div>￥[[orderDetail.service_fee_amount]]</div>
                                </div>
                                <div class="order-sum-li">
                                    <div>优惠</div>
                                    <div>-￥[[parseFloat(total_discount_price).toFixed(2)]]</div>
                                    {{--<div>-￥[[orderDetail.discount_price]]</div>--}}
                                </div>
                                <div class="order-sum-li">
                                    <div>抵扣</div>
                                    <div>-￥[[orderDetail.deduction_price]]</div>
                                </div>
                                <div class="order-sum-li">
                                    <div>最终运费</div>
                                    <div>￥[[orderDetail.dispatch_price]]</div>
                                </div>
                                <div class="order-sum-li">
                                    <div>应收款</div>
                                    <div style="color:red;font-weight:600">￥[[orderDetail.price]]</div>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="支付方式" prop="">
                            <!-- <div>未付款</div> -->
                            <div style="display:inline-block;margin-right:20px">[[orderDetail.pay_type_name]]</div>
                            <el-button v-if="orderDetail.order_pays != null" size="small" plain type="primary" @click="getPayList">
                                查看支付记录
                            </el-button>

                        </el-form-item>
                        <el-form-item label="订单状态" prop="">
                            <div style="display:inline-block;margin-right:20px">[[orderDetail.status_name]]</div>
                            <div style="display:inline-block;margin-right:10px" v-for="(item1,index1) in orderDetail.backend_button_models" :key="index1">
                                <el-button @click="orderConfirm(item1.value,orderDetail)"  size="small" plain :type="item1.type">
                                    [[item1.name]]
                                </el-button>
                            </div>
                            {{--<el-button size="small" plain type="primary">确认付款</el-button>--}}
                        </el-form-item>
                        {{--流程类型颜色demo--}}
                        {{--<el-form-item label="" prop="">--}}
                        {{--<el-steps :space="200" :active="1" finish-status="success">--}}
                            {{--<el-step title="等待" status="wait"></el-step>--}}
                            {{--<el-step title="进行中" status="process"></el-step>--}}
                            {{--<el-step title="已完成" status="finish"></el-step>--}}
                            {{--<el-step title="成功" status="success"></el-step>--}}
                            {{--<el-step title="错误" status="error"></el-step>--}}
                        {{--</el-steps>--}}
                        {{--</el-form-item>--}}
                        {{--订单流程--}}
                        <el-form-item label="" prop="">
                            <el-steps align-center style="width:70%" process-status="process">
                                <el-step v-for="(process_item,processIndex) in orderDetail.orderSteps" :key="processIndex" :icon="process_item.icon" :status="process_item.status"  :title="process_item.title" :description="process_item.desc"></el-step>
                            </el-steps>
                        </el-form-item>

                        <el-form-item v-if="orderDetail.has_one_expediting_delivery" label="催发货时间" prop="">
                            <div>[[orderDetail.has_one_expediting_delivery.created_at]]</div>
                        </el-form-item>

                        <el-form-item v-if="orderDetail.status == -1" label="关闭原因" prop="">
                            <div>[[orderDetail.close_reason]]</div>
                        </el-form-item>

                        <el-form-item label="用户备注" prop="">
                            <div>[[orderDetail.note]]</div>
                        </el-form-item>

                        <el-form-item label="商家备注" prop="">
                            <el-input v-model="form.merchant_remark" type="textarea" placeholder="多行输入" rows="8" style="width:70%">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="" prop="">
                            <el-button size="small" type="primary" @click="saveMerchantRemark()">保存备注</el-button>
                        </el-form-item>
                        <el-form-item label="兑换码" prop="" v-if="orderDetail.has_one_exchange_code">
                            <div style="display:inline-block;margin-right:20px">[[orderDetail.has_one_exchange_code.record.code.name]]</div>
                        </el-form-item>
                        <el-form-item label="兑换码序列号" prop="" v-if="orderDetail.has_one_exchange_code">
                            <div style="display:inline-block;margin-right:20px">[[orderDetail.has_one_exchange_code.record.code_sn]]</div>
                        </el-form-item>

                    </div>
                </div>

                <div v-if="orderDetail.address != null && orderDetail.dispatch_type_id != 15" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">收货信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="收货人" prop="">
                            <div>[[orderDetail.address.realname]]</div>
                        </el-form-item>
                        <el-form-item label="联系电话" prop="">
                            <div>[[orderDetail.address.mobile]]</div>
                        </el-form-item>
                        <el-form-item label="收货地址" prop="">
                            <div>[[orderDetail.address.address]]</div>
                        </el-form-item>
                        <el-form-item label="配送日期" prop="" v-show="orderDetail.address.delivery_day">
                            <div>[[orderDetail.address.delivery_day]]</div>
                        </el-form-item>
                        <el-form-item label="配送时间" prop="" v-show="orderDetail.address.delivery_time">
                            <div>[[orderDetail.address.delivery_time]]</div>
                        </el-form-item>
                        <el-form-item label="" prop="">
                            <el-button v-if="orderDetail.status == 0 || orderDetail.status == 1" size="small" type="primary" @click="showAddressUpdate()">修改信息</el-button>
                            <el-button size="small" type="text" @click="getUpdateIndex">修改记录</el-button>
                        </el-form-item>
                        <!-- 物流信息 -->
                        <div v-if="dispatch != null && dispatch.length > 0">
                            <el-form-item label="物流信息" prop="">
                                <el-button size="small" type="primary" @click="showDispatchInfo()">查看</el-button>
                            </el-form-item>
                        </div>
                    </div>
                </div>

                {{-- 根据配送方案来显示视图--}}
                <div v-if="orderDetail.address != null && orderDetail.dispatch_type_id == 15" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">商城自提信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="自提点" prop="">
                            <div>[[orderDetail.address.realname]]</div>
                        </el-form-item>
                        <el-form-item label="联系电话" prop="">
                            <div>[[orderDetail.address.mobile]]</div>
                        </el-form-item>
                        <el-form-item label="自提点地址" prop="">
                            <div>[[orderDetail.address.address]]</div>
                        </el-form-item>
                        <el-form-item label="" prop="">
                            <el-button v-if="orderDetail.status == 0 || orderDetail.status == 1" size="small" type="primary" @click="showAddressUpdate()">修改信息</el-button>
                            <el-button size="small" type="text" @click="getUpdateIndex">修改记录</el-button>
                        </el-form-item>
                        <!-- 物流信息 -->
                        <div v-if="dispatch != null && dispatch.length > 0">
                            <el-form-item label="物流信息" prop="">
                                <el-button size="small" type="primary" @click="showDispatchInfo()">查看</el-button>
                            </el-form-item>
                        </div>
                    </div>
                </div>

                <div v-if="(refundApply != null && Object.keys(refundApply).length > 0) || (orderDetail.has_many_refund_apply != null && orderDetail.has_many_refund_apply.length > 0)" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">售后申请</div>
                    </div>
                    <div v-if="refundApply != null && Object.keys(refundApply).length > 0" class="vue-main-form">
                        <el-form-item label="售后状态" prop="">
                            <div style="display:inline-block;margin-right:20px">[[refundApply.status_name]]</div>
                            <div style="display:inline-block;margin-right:10px" v-for="(button1,key1) in refundApply.backend_button_models" :key="key1">
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
                                <el-step v-for="(process_item1,processIndex1) in refundApply.refundSteps"
                                        :key="processIndex1"
                                        :icon="process_item1.icon"
                                        :status="process_item1.status"
                                        :title="process_item1.title">
                                    <template slot="description">
                                        <div>[[process_item1.desc]]</div>

                                        <template v-if="process_item1.value == 20 && refundApply.return_express != null">
                                            <div>快递名称：[[refundApply.return_express.express_company_name]]</div>
                                            <div>快递单号：[[refundApply.return_express.express_sn]]</div>
                                            <el-button plain size="mini" type="primary" @click="selectRefundLogistics(process_item1.value)">查看物流</el-button>
                                        </template>

                                        <template v-if="process_item1.value == 30 && (refundApply.has_many_resend_express != null && refundApply.has_many_resend_express.length > 0)">
                                            <div v-if="refundApply.has_many_resend_express.length == 1">
                                                <div>快递名称：[[refundApply.has_many_resend_express[0].express_company_name]]</div>
                                                <div>快递单号：[[refundApply.has_many_resend_express[0].express_sn]]</div>
                                            </div>
                                            <el-button plain size="mini" type="primary" @click="selectRefundLogistics(process_item1.value)">查看物流</el-button>
                                        </template>

                                    </template>
                                </el-step>
                            </el-steps>
                        </el-form-item>
                        <el-form-item v-if="refundApply.refund_order_goods != null && refundApply.refund_order_goods.length > 0" label="商品" prop="">
                            <div>
                                <a style="color:#29BA9C;" @click="selectRefundGoods(refundApply.refund_order_goods)">查看</a>
                            </div>
                        </el-form-item>
                        <el-form-item label="售后类型" prop="">
                            <div>[[refundApply.refund_type_name]]</div>
                        </el-form-item>
                        <el-form-item label="收货状态" prop="">
                            <div>[[refundApply.receive_status_name]]</div>
                        </el-form-item>
                        <el-form-item label="退货方式" prop="" v-if="refundApply.refund_type == 2">
                            <div>[[refundApply.refund_way_type_name]]</div>
                        </el-form-item>
                        <el-form-item v-if="refundApply.refund_type != 2" label="退款金额" prop="">
                            <div>
                                ￥[[refundApply.price]](包含运费￥[[refundApply.freight_price]]，其他费用￥[[refundApply.other_price]])
                                <el-tag v-if="refundApply.change_log != null" size="small" type="danger">改价</el-tag>
                                <template v-if="refundApply.status < 6 && orderDetail.plugin_id == 0">
                                    <a style="color:#29BA9C;padding-left: 15px" @click="refundChangePriceShow()">修改金额</a>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item label="退款原因" prop="">
                            <div>[[refundApply.reason]]</div>
                        </el-form-item>
                        <el-form-item label="说明" prop="">
                            <div>[[refundApply.content]]</div>
                        </el-form-item>
                        <el-form-item label="协商记录" prop="">
                            <a style="color:#29BA9C;" @click="showRefundProcessLog">查看</a>
                        </el-form-item>
                        <el-form-item v-if="refundApply.images != null && refundApply.images.length > 0" label="图片凭证" prop="">
                            <div>
                                <el-image style="width: 150px" v-for="(refund_img,imgskey3) in refundApply.images" :src="refund_img"></el-image>
                            </div>
                        </el-form-item>
                    </div>
                    <div class="vue-main-form" v-if="!(refundApply != null && Object.keys(refundApply).length > 0)">
                        <el-form-item label="协商记录" prop="">
                            <a style="color:#29BA9C;" @click="showRefundProcessLog">查看</a>
                        </el-form-item>
                    </div>
                </div>
                <!-- 发票 todo 这里的页面感觉需要优化一下-->
                <!-- v-if="orderDetail.collect_name || (orderDetail.order_invoice && orderDetail.order_invoice.collect_name)" -->
                <div  style="display:flex;" v-if="invoiceInfo != null && invoiceInfo.collect_name">
                    <div class="vue-head" style="flex:5">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">发票信息</div>
                        </div>
                        <div class="vue-main-form">
                            <el-form label-width="25%">
                                <el-form-item label="发票类型：" prop="">
                                    [[invoiceInfo.invoice_type == 2?"专用发票":invoiceInfo.invoice_type == 1?"纸质发票":"电子发票"]]
                                    <!-- <div>[[orderDetail.invoice_type == 1 ? '纸质发票' : '电子发票' ]]</div> -->
                                </el-form-item>

                                <!-- 专用发票/电子专票 -->
                                <template v-if="invoiceInfo.invoice_type == 2">
                                    <el-form-item label="单位：" prop="">[[invoiceInfo.collect_name]]</el-form-item>
                                    <el-form-item label="税号：" prop="">[[invoiceInfo.gmf_taxpayer]]</el-form-item>
                                    <el-form-item label="开户银行：" prop="">[[invoiceInfo.gmf_bank]]</el-form-item>
                                    <el-form-item label="银行账号：" prop="">[[invoiceInfo.gmf_bank_admin]]</el-form-item>
                                    <el-form-item label="注册地址：" prop="">[[invoiceInfo.gmf_address]]</el-form-item>
                                    <el-form-item label="注册电话：" prop="">[[invoiceInfo.gmf_mobile]]</el-form-item>
                                    <el-form-item label="收票人手机号：" prop="">[[invoiceInfo.col_mobile]]</el-form-item>
                                    <el-form-item label="邮箱：" prop="">[[invoiceInfo.email]]</el-form-item>
                                </template>
                                <!--  电子发票和纸质发票 -->
                                <template v-else>
                                    <el-form-item label="发票抬头：">[[invoiceInfo.rise_type == 1? "个人":"单位"]]</el-form-item>
                                    <el-form-item label="单位/抬头名称：" prop="">[[invoiceInfo.collect_name]]</el-form-item>
                                    <el-form-item label="税号：" prop="" v-if="invoiceInfo.rise_type == 0">[[invoiceInfo.gmf_taxpayer]]</el-form-item>
                                    <el-form-item label="发票内容：" prop="" v-if="is_invoice_open==1&&invoiceInfo.content!=''">[[invoiceInfo.content]]</el-form-item>
                                    <template v-if="invoiceInfo.rise_type == 0">
                                        <el-form-item label="开户银行：" prop="">[[invoiceInfo.gmf_bank]]</el-form-item>
                                        <el-form-item label="银行账号：" prop="">[[invoiceInfo.gmf_bank_admin]]</el-form-item>
                                        <el-form-item label="注册地址：" prop="">[[invoiceInfo.gmf_address]]</el-form-item>
                                        <el-form-item label="注册电话：" prop="">[[invoiceInfo.gmf_mobile]]</el-form-item>
                                    </template>
                                    <template v-if="invoiceInfo.invoice_type == 1">
                                        <el-form-item label="收票信息：" prop="">[[invoiceInfo.col_name]] / [[invoiceInfo.col_mobile]]</el-form-item>
                                        <el-form-item label="收票地址：" prop="">[[invoiceInfo.col_address]]</el-form-item>
                                    </template>
                                    <template v-if="invoiceInfo.invoice_type == 0">
                                        <el-form-item label="收票人手机号：" prop="">[[invoiceInfo.col_mobile]]</el-form-item>
                                        <el-form-item label="邮箱：" prop="">[[invoiceInfo.email]]</el-form-item>
                                    </template>
                                </template>
                            </el-form>

                        </div>
                    </div>
                    <div class="vue-head" style="flex:4;">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">上传发票</div>
                        </div>
                        <div class="vue-main-form">
                            <el-form-item label="" prop="thumb">
                                <div class="upload-box" @click="openUpload('thumb')" v-if="!form.thumb_url">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('thumb')" class="upload-boxed" v-if="form.thumb_url&&!is_pdf" style="height:82px">
                                    <img :src="form.thumb_url" alt="" style="width:150px;height:82px;border-radius: 5px;cursor: pointer;">
                                    <i class="el-icon-close" @click.stop="clearImg('thumb','img')" title="点击清除图片"></i>
                                    <div class="upload-boxed-text">点击重新上传</div>
                                </div>
                                <div @click="openUpload('thumb')" class="upload-boxed" v-if="form.thumb_url&&is_pdf" style="height:82px;border:1px solid #ccc">
                                    <div style="text-align:center;">PDF文件</div>
                                    <!-- <embed :src="form.thumb_url" width="100%" height="100%" :href="form.thumb_url"></embed> -->
                                    <!-- <img :src="form.thumb_url" alt="" style="width:150px;height:82px;border-radius: 5px;cursor: pointer;"> -->
                                    <i class="el-icon-close" @click.stop="clearImg('advert_one','img')" title="点击清除图片"></i>
                                    <div class="upload-boxed-text">点击重新上传</div>
                                </div>
                                <div v-if="is_pdf">
                                    <a :href="form.thumb_url" target="_blank">预览PDF文件</a>
                                </div>
                            </el-form-item>
                            <el-form-item label="" prop="">
                                <el-button size="small" type="primary" @click="saveInvoice()" >保存发票</el-button>
                            </el-form-item>

                        </div>
                    </div>

                </div>
                <!-- 优惠信息 -->
                <div v-if="orderDetail.discounts != null && orderDetail.discounts.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">优惠信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="" prop="">
                            <table class="el-table" style="width:70%;">
                                <thead>
                                    <tr>
                                        <td class="is-center">优惠名称</td>
                                        <td class="is-center">优惠金额</td>
                                    </tr>
                                </thead>
                                <tbody>
                                <template v-for="(discount,discount_key) in orderDetail.discounts">
                                    <tr>
                                        <td class="is-center">[[discount.name]]</td>
                                        <td class="is-center">[[discount.amount]]</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>

                        </el-form-item>

                    </div>
                </div>
                <!-- 手续费 -->
                <div v-if="orderDetail.order_fees != null && orderDetail.order_fees.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">手续费信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="" prop="">
                            <table class="el-table" style="width:70%;">
                                <thead>
                                <tr>
                                    <td class="is-center">手续费名称</td>
                                    <td class="is-center">手续费金额</td>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(order_fee,order_fee_key) in orderDetail.order_fees">
                                    <tr>
                                        <td class="is-center">[[order_fee.name]]</td>
                                        <td class="is-center">[[order_fee.amount]]</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </el-form-item>
                    </div>
                </div>
                <!-- 服务费 -->
                <div v-if="orderDetail.order_service_fees != null && orderDetail.order_service_fees.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">服务费信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="" prop="">
                            <table class="el-table" style="width:70%;">
                                <thead>
                                <tr>
                                    <td class="is-center">服务费名称</td>
                                    <td class="is-center">服务费金额</td>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(order_service_fee,order_service_fee_key) in orderDetail.order_service_fees">
                                    <tr>
                                        <td class="is-center">[[order_service_fee.name]]</td>
                                        <td class="is-center">[[order_service_fee.amount]]</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </el-form-item>
                    </div>
                </div>
                <!-- 抵扣信息 -->
                <div v-if="orderDetail.deductions !== undefined && orderDetail.deductions.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">抵扣信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="" prop="">
                            <table class="el-table" style="width:70%;">
                                <thead>
                                <tr>
                                    <td class="is-center">名称</td>
                                    <td class="is-center">抵扣值</td>
                                    <td class="is-center">抵扣金额</td>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(deduction,deduction_key) in orderDetail.deductions">
                                    <tr>
                                        <td class="is-center">[[deduction.name]]</td>
                                        <td class="is-center">[[deduction.coin]]</td>
                                        <td class="is-center">[[deduction.amount]]</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>

                        </el-form-item>

                    </div>
                </div>
                <!-- 优惠卷信息 -->
                <div v-if="orderDetail.coupons !== undefined && orderDetail.coupons.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">优惠券信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="" prop="">
                            <table class="el-table" style="width:70%;">
                                <thead>
                                <tr>
                                    <td class="is-center">名称</td>
                                    <td class="is-center">抵扣金额</td>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(coupon,coupon_key) in orderDetail.coupons">
                                    <tr>
                                        <td class="is-center">[[coupon.name]]</td>
                                        <td class="is-center">[[coupon.amount]]</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>

                        </el-form-item>

                    </div>
                </div>
                @foreach(\app\common\modules\widget\Widget::current()->getItem('order_detail') as $key1=>$value1)
                    {!! widget($value1['class'], ['order_id'=> request()->input('id')])!!}
                @endforeach

                <div v-if="div_from.status">
                    <div class="vue-head">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">个人表单信息</div>
                        </div>
                        <div class="vue-main-form">
                            <el-form-item label="真实姓名" prop="">
                                <div>[[div_from.member_name]]</div>
                            </el-form-item>
                            <el-form-item label="身份证" prop="">
                                <div>[[div_from.member_card]]</div>
                            </el-form-item>
                        </div>
                    </div>
                </div>

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
                <!-- 商品信息 -->
                <div v-if="orderDetail.has_many_order_goods !== undefined && orderDetail.has_many_order_goods.length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">商品信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-table :data="orderDetail.has_many_order_goods" style="width: 100%">
                            <el-table-column prop="goods_id" label="商品ID" width="100" align="center"></el-table-column>
                            <el-table-column prop="" label="商品" width="60" align="center">
                                <template slot-scope="scope">
                                    <img :src="scope.row.thumb" style="width:50px;height:50px;">
                                </template>
                            </el-table-column>
                            <el-table-column prop="down_time" label="" min-width="180" align="left">
                                <template slot-scope="scope">
                                        <div class="list-con-goods-title"
                                             style="color:#29BA9C;cursor: pointer;"
                                             @click="gotoGoods(scope.row.goods_id)">
                                            [[scope.row.title]]
                                        </div>
                                        <div class="list-con-goods-option">规格： [[scope.row.goods_option_title]]</div>
                                        <div class="list-con-goods-option">数量： [[scope.row.total]]</div>

                                        <div v-if="scope.row.after_sales">
                                            <div v-if="scope.row.refund_id" class="list-con-goods-after-sales" >
                                                [[scope.row.after_sales.refund_type_name]]：[[scope.row.after_sales.refund_status_name]] * [[scope.row.after_sales.refunded_total]]
                                            </div>
                                            <div v-if="(!scope.row.refund_id) && scope.row.after_sales.refunded_total" class="list-con-goods-after-sales" >
                                                已售后：[[scope.row.after_sales.refunded_total]]
                                            </div>
                                        </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="down_time" label="商品价格" min-width="230" align="center">
                                <template slot-scope="scope">
                                    <div>
                                        <div style="display:flex;justify-content: center;">
                                            <div style="flex:1;text-align:right">现价：</div>
                                            <div style="flex:1;text-align:left">￥[[scope.row.goods_price]]</div>
                                        </div>
                                        <div style="display:flex;justify-content: center;">
                                            <div style="flex:1;text-align:right">原价：</div>
                                            <div style="flex:1;text-align:left">￥[[scope.row.goods_market_price]]</div>
                                        </div>
                                        <div style="display:flex;justify-content: center;">
                                            <div style="flex:1;text-align:right">成本价：</div>
                                            <div style="flex:1;text-align:left">￥[[scope.row.goods_cost_price]]</div>
                                        </div>
                                        @if(\Setting::get('shop.member')['vip_price'] == 1)
                                            <div style="display:flex;justify-content: center;">
                                                <div style="flex:1;text-align:right">会员价：</div>
                                                <div style="flex:1;text-align:left">￥[[scope.row.goods_vip_price]]</div>
                                            </div>
                                        @endif
                                        <div style="display:flex;justify-content: center;">
                                            <div style="flex:1;text-align:right">均摊支付：</div>
                                            <div style="flex:1;text-align:left">￥[[scope.row.payment_amount]]</div>
                                        </div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="down_time" label="优惠均摊信息" min-width="243" align="center">
                                <template slot-scope="scope">
                                <div  v-if="scope.row.order_goods_discounts !== undefined && scope.row.order_goods_discounts.length > 0" v-for="(goods_discount,index1) in scope.row.order_goods_discounts">
                                    <div style="display:flex;justify-content: center;">
                                        <div style="flex:1;text-align:right">[[goods_discount.name]]</div>
                                        <div style="flex:1;text-align:left">￥[[goods_discount.amount]]</div>
                                    </div>
                                </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="down_time" label="抵扣信息" min-width="180" align="center">
                                <template slot-scope="scope">
                                <div  v-if="scope.row.order_goods_deductions !== undefined && scope.row.order_goods_deductions.length > 0" v-for="(goods_deduction,index2) in scope.row.order_goods_deductions">
                                    <div style="display:flex;justify-content: center;">
                                        <div style="flex:1;text-align:right">[[goods_deduction.name]]</div>
                                        <div style="flex:1;text-align:left">￥[[goods_deduction.used_coin]]</div>
                                    </div>
                                </div>
                                </template>
                            </el-table-column>
                        </el-table>


                    </div>
                </div>
            </el-form>
            <!-- 修改收货地址 -->
            <el-dialog :visible.sync="modal_order_address_show" width="900px"  title="修改收货信息">
                <div style="overflow:auto">
                    <div style="overflow:auto" id="update-address">
                        <el-form ref="updateAddress" :model="updateAddress" :rules="addressRules" label-width="15%">
                            <el-form-item label="收件人" prop="realname" required>
                                <el-input v-model="updateAddress.realname" placeholder="收件人姓名" style="width:70%"></el-input>
                            </el-form-item>
                            <el-form-item label="联系方式" prop="phone" required>
                                <el-input v-model="updateAddress.phone" placeholder="收件人电话" style="width:70%"></el-input>
                            </el-form-item>
                            <el-form-item label="地址" v-loading="areaLoading">
                                <el-select v-model="updateAddress.province_id" clearable placeholder="省" @change="changeProvince(updateAddress.province_id)" style="width:150px">
                                    <el-option v-for="(item,index) in province_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                                </el-select>
                                <el-select v-model="updateAddress.city_id" clearable placeholder="市" @change="changeCity(updateAddress.city_id)" style="width:150px">
                                    <el-option v-for="(item,index) in city_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                                </el-select>
                                <el-select v-model="updateAddress.district_id" clearable placeholder="区/县" @change="changeDistrict(updateAddress.district_id)" style="width:150px">
                                    <el-option v-for="(item,index) in district_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                                </el-select>
                                <el-select v-if="street == 1" v-model="updateAddress.street_id" clearable placeholder="街道/乡镇" style="width:150px">
                                    <el-option v-for="(item,index) in street_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="详细地址" prop="address" required>
                                <el-input v-model="updateAddress.address" placeholder="详细地址" style="width:70%"></el-input>
                            </el-form-item>
                        </el-form>

                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="modal_order_address_show = false">取 消</el-button>
                    <el-button type="primary" @click="addressUpdate('updateAddress')">确 认</el-button>
                </span>
            </el-dialog>
            <!-- 修改记录 -->
            <el-dialog :visible.sync="modal_update_show" width="900px"  title="修改记录">
                <div style="overflow:auto">
                    <el-table :data="update_list" style="width: 100%;height:500px;overflow:auto" id="update-list">
                        <el-table-column label="修改时间" prop="created_at" align="center"></el-table-column>
                        <el-table-column label="修改前收货信息" prop="" align="center">
                            <template slot-scope="scope">
                                <div> [[scope.row.old_name]]</div>
                                <div> [[scope.row.old_phone]]</div>
                                <div> [[scope.row.old_address]]</div>
                            </template>
                        </el-table-column>
                        <el-table-column label="修改后收货信息" prop="" align="center">
                            <template slot-scope="scope">
                                <div> [[scope.row.realname]]</div>
                                <div> [[scope.row.phone]]</div>
                                <div> [[scope.row.new_address]]</div>
                            </template>
                        </el-table-column>



                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="modal_update_show = false">取 消</el-button>
                    <!-- <el-button type="primary" @click="addressUpdate">确 认</el-button> -->
                </span>
            </el-dialog>
            <!-- 支付记录 -->
            <el-dialog :visible.sync="modal_pay_show" width="1100px"  title="支付记录">
                <div style="overflow:auto">
                    <el-table :data="pay_list" style="width: 100%;height:500px;overflow:auto" id="pay-list">
                        <el-table-column label="ID" prop="id" align="center"></el-table-column>
                        <el-table-column label="支付单号">
                            <template slot-scope="scope">
                                <a :href="'{{ yzWebUrl('orderPay.detail', array('order_pay_id' => '')) }}'+[[scope.row.id]]" target="_blank">
                                    [[scope.row.pay_sn]]
                                </a>
                            </template>
                        </el-table-column>
                        <el-table-column prop="amount" label="支付金额"></el-table-column>
                        <el-table-column prop="status_name" label="状态"></el-table-column>
                        <el-table-column prop="pay_type_name" label="支付方式"></el-table-column>
                        <el-table-column prop="created_at" label="创建时间"></el-table-column>
                        <el-table-column prop="pay_time" label="支付时间"></el-table-column>
                        <el-table-column prop="refund_time" label="退款时间"></el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="modal_pay_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <!-- 查看物流信息 -->
            <el-dialog :visible.sync="modal_dispatch_info" width="750px" center title="物流信息">
                <div style="height:400px;overflow:auto" id="dispatch-info">
                    <!-- 多包裹 -->
                    <div>
                        <div style="display:flex;margin-bottom:15px;" v-for="(item3,index3) in dispatch">
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
                    <div v-for="(orderRefund, refundKey) in orderDetail.has_many_refund_apply">
                        <div style="border: 2px solid #e9e9e9;border-radius:5px;margin-bottom: 10px">
                            <div style="padding:15px;color: #209D83;margin-bottom: -12px">
                                售后编号：[[orderRefund.refund_sn]]
                            </div>
                            <div v-for="(processLog, log1) in orderRefund.process_log">
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
                                        <div v-if="processLog.operator == 1 && Object.keys(orderRefund.refund_order_goods).length > 0">
                                            商品：<el-button  type="text" @click="selectRefundGoods(orderRefund.refund_order_goods)">查看</el-button>
                                        </div>
                                        <div v-for="(logDetail, log2) in processLog.detail">
                                            <div v-if="logDetail">[[logDetail]]</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--<div v-for="(refundLog, log1) in orderDetail.has_many_refund_apply">--}}
                        {{--<div style="background-color: #f9f9f9;border-radius:5px;margin-bottom: 10px">--}}
                            {{--<div style="padding:20px">--}}
                                {{--<div style="float:right">[[refundLog.created_at]]</div>--}}
                                {{--<div>--}}
                                    {{--<i type="primary"--}}
                                       {{--:class="refundLog.operator == 1 ? 'el-icon-user-solid':'el-icon-s-shop'"></i>--}}
                                    {{--[[refundLog.operate_name]]--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div style="border-bottom:1px solid #e9e9e9;;clear: both;"></div>--}}
                            {{--<div style="padding:15px 20px">--}}
                                {{--<div v-for="(logDetail, log2) in refundLog.detail">--}}
                                    {{--<div v-if="logDetail">[[logDetail]]</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}

                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="order_refund_process_log_show = false">取 消</el-button>
                </span>
            </el-dialog>


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

            {{--//售后操作组件--}}
            <refund-order-operation-base
                    :refund-operation-type="refundOperationType"
                    :operation-refund="refundApply"
                    :refund_dialog_show="refund_dialog_show"
                    :refund-order="orderDetail"
                    @sure="sureImg">
            </refund-order-operation-base>


            <el-dialog :visible.sync="map_show" width="60%" center title="选择坐标">
                <div>
                    <div v-if="map_search_show">
                        <input v-model="map_keyword" style="width:70%" @keyup.enter="searchMap"
                               class="el-input__inner">
                        <el-button type="primary" @click="searchMap()">搜索</el-button>
                    </div>
                    <div ref="ditucontent" style="width:100%;height:450px;margin:20px 0"></div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button v-if="map_change_show" @click="sureMap">确 定</el-button>
                    <el-button @click="map_show = false">取 消</el-button>
                </span>
            </el-dialog>

            {{--<div class="vue-page">--}}
                {{--<div class="vue-center">--}}
                    {{--<el-button type="primary" @click="submitForm('form')">保存设置</el-button>--}}
                    {{--<el-button @click="goBack">返回</el-button>--}}
                {{--</div>--}}
            {{--</div>--}}
            <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>


        </div>
    </div>


    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>

{{--订单类型-动态加载js文件--}}
@foreach((new \app\backend\modules\order\services\OrderViewService())->importVue() as $routeKey => $vueRoute)
    @include($vueRoute['path'])
@endforeach

{{--引入售后组件--}}
@include('refund.component.refundOrderOperationBase')

@include('public.admin.tinymceee')

@include('public.admin.uploadfile')
    <script>

        var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
        var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
        var top_right_navigation = new BMap.NavigationControl({
            anchor: BMAP_ANCHOR_TOP_RIGHT,
            type: BMAP_NAVIGATION_CONTROL_SMALL
        }); //右上角，仅包含平移和缩放按钮
        /*缩放控件type有四种类型:
        BMAP_NAVIGATION_CONTROL_SMALL：仅包含平移和缩放按钮；BMAP_NAVIGATION_CONTROL_PAN:仅包含平移按钮；BMAP_NAVIGATION_CONTROL_ZOOM：仅包含缩放按钮*/

        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    is_pdf:false,
                    is_invoice_open: '{!! app('plugins')->isEnabled('invoice') !!}',
                    street: '{!! \Setting::get("shop.trade")['is_street'] !!}',
                    id:0,
                    getDataUrl: '{!! yzWebFullUrl('order.detail.get-data') !!}',//获取订单数据链接
                    goodsEditLink: '{!! yzWebFullUrl('goods.goods.edit') !!}', //订单商品编辑页跳转链接
                    order_id:0,
                    orderDetail:{}, //订单
                    yz_member:{},
                    div_from:{},
                    total_discount_price:0,
                    expressCompanies:[],//快递公司
                    //退款
                    refundApply:{}, //退款信息
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
                    refundLogistics:[], //退货物流信息
                    //订单物流信
                    dispatch:{}, //订单物流信息
                    modal_dispatch_info: false,
                    get_goods_show:false, //查看商品
                    get_goods_list:[],
                    //订单发票
                    form:{
                    merchant_remark:'',//商家备注
                        thumb_url:'',//发票地址
                        thumb:'',//发票地址
                    },
                    //图片文件上传组件
                    uploadShow:false,
                    chooseImgName:'',
                    submit_url:'',
                    showVisible:false,
                    loading: false,
                    uploadImg1:'',

                    // 修改收货信息
                    province_list:[],
                    city_list:[],
                    district_list:[],
                    street_list:[],
                    modal_order_address_show:false,
                    updateAddress:{
                        street_id:'',
                        city_id:"",
                    },
                    modal_update_show:false,// 地址修改记录
                    update_list:[],
                    areaLoading:false,

                    // 支付记录
                    modal_pay_show:false,
                    pay_list:[],

                    //订单操作组件
                    operationType:'',
                    operationOrder:{},
                    dialog_show:0,

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
                    // 多包裹发货
                    more_send_show:false,
                    order_goods_send_list:[],
                    send_order_goods_ids:[],
                    map: "",
                    marker: "",
                    centerParam: [113.275995, 23.117055],
                    zoomParam: "",
                    markersParam: [113.275995, 23.117055],
                    pointNew: "",
                    choose_center: [],
                    choose_marker: [],
                    map_show:false,
                    map_search_show:false,
                    map_change_show:false,
                    map_keyword: '',
                    city_delivery_modal: {
                        show: false,
                    },
                    invoiceInfo:{},//发票信息
                    addressRules: {
                        realname: [
                            { required: true, message: '请输入收件人', trigger: 'blur' },
                        ],
                        phone: [
                            { required: true, message: '请输入联系方式', trigger: 'blur' }
                        ],
                        address: [
                            { required: true, message: '请输入详细地址', trigger: 'blur' }
                        ],
                    },
                }
            },
            created() {
                let result = this.viewReturn();
                this.__initial(result);
            },
            mounted() {
                if(this.id) {
                    this.getData();
                }
            },
            methods: {
                //视图返回数据
                viewReturn() {
                    return {!! $data?:'{}' !!};
                },
                //初始化页面数据，请求链接
                __initial(data) {
                    if (data.requestInputs.id) {
                        this.id = data.requestInputs.id;
                    }
                    if (data.detail_url) {
                        this.getDataUrl = data.detail_url;
                    }
                    console.log(data);
                },
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});

                    this.$http.post(this.getDataUrl,{id:this.id}).then(function (response) {
                            if (response.data.result){
                                this.orderDetail = response.data.data.order;
                                // console.log(this.orderDetail);
                                //订单固定跳转链接
                                if (this.orderDetail.fixed_link) {
                                    //商品编辑页地址
                                    if (this.orderDetail.fixed_link.goods_edit_link) {
                                        this.goodsEditLink = this.orderDetail.fixed_link.goods_edit_link;
                                    }
                                }

                                this.invoiceInfo = this.orderDetail.order_invoice;

                                this.order_id = response.data.data.order.id;

                                this.yz_member = response.data.data.yz_member;

                                this.div_from = response.data.data.div_from;

                                this.refundApply = response.data.data.refundApply;

                                if (this.refundApply) {
                                    this.refund_apply_goods_price = this.refundApply.apply_price;
                                    this.refund_price = this.refundApply.price;
                                }
                                // console.log(this.refundApply != null && Object.keys(this.refundApply).length > 0);
                                //console.log(this.refundApply);

                                //优惠金额
                                if (this.orderDetail.discounts != null && this.orderDetail.discounts.length > 0) {
                                    let aaa;
                                    for (let i = 0; i < this.orderDetail.discounts.length;i++) {
                                        this.total_discount_price = parseFloat(this.total_discount_price) + parseFloat(this.orderDetail.discounts[i].amount);
                                    }
                                }

                                if (response.data.data.dispatch) {
                                    this.dispatch = response.data.data.dispatch;
                                }

                                this.expressCompanies = response.data.data.expressCompanies;

                                if (response.data.data.order.has_one_order_remark) {
                                    this.form.merchant_remark = response.data.data.order.has_one_order_remark.remark;
                                }

                                if (response.data.data.order.invoice) {
                                    this.form.thumb_url = response.data.data.order.invoice;
                                    this.form.thumb = response.data.data.order.invoice;
                                    let url = this.form.thumb_url.split('.')
                                    console.log(url)

                                    if(url[url.length-1] == 'pdf') {
                                        this.is_pdf = true;
                                    }
                                    else {
                                        this.is_pdf = false;
                                    }
                                }

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
                    this.$http.post('{!! yzWebFullUrl('refund.detail.express') !!}',{refund_id:this.refundApply.id,refund_value:value}).then(function (response) {
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
                        refund_id: this.refundApply.id,
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
                    this.operationRefund = this.refundApply;
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
                refundChangePriceShow() {
                    this.refund_change_freight_price = this.refundApply.freight_price;
                    this.refund_change_other_price = this.refundApply.other_price;
                    this.refund_change_show = true;
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
                //确认修改退款金额
                refundChangePrice() {

                    // if(this.refund_change_price === '') {
                    //     this.$message.error("修改金额不能为空！");
                    //     return false;
                    // }


                    let json = {
                        'order_id':this.id,
                        'refund_id':this.refundApply.id,
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
                        this.getData(); //重新获取数据刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.refund_change_show = false;
                    });
                },

                //订单操作
                orderConfirm(operationType, order) {

                    console.log(operationType, order);
                    this.dialog_show++;
                    this.operationOrder = order;
                    this.operationType = operationType;

                },
                //显示订单地址修改记录
                getUpdateIndex() {
                    this.modal_update_show = true;
                    let loading = this.$loading({target:document.querySelector("#update-list"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.address-update.index') !!}',{order_id:this.orderDetail.id}).then(function (response) {
                            if (response.data.result){
                                this.update_list = response.data.data;
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
                // 支付记录
                getPayList() {
                    this.modal_pay_show = true;
                    let loading = this.$loading({target:document.querySelector("#update-list"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.orderPay.vue') !!}',{order_id:this.order_id}).then(function (response) {
                            if (response.data.result){
                                // console.log(response.data.data);
                                this.pay_list = response.data.data;
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
                //显示修改订单收货地址
                showAddressUpdate() {
                    this.modal_order_address_show = true;
                    this.initProvince();
                },
                //修改订单收货地址
                addressUpdate(address) {
                    let json = {
                        order_id:this.orderDetail.id,
                        realname:this.updateAddress.realname,
                        phone:this.updateAddress.phone,
                        address:this.updateAddress.address,
                        province_id: this.updateAddress.province_id ? this.updateAddress.province_id : 0,
                        city_id:this.updateAddress.city_id ? this.updateAddress.city_id : 0,
                        district_id	:this.updateAddress.district_id ? this.updateAddress.district_id : 0,
                        street_id:this.updateAddress.street_id ? this.updateAddress.street_id : 0,
                    };
                    this.$refs[address].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector("#update-address"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post('{!! yzWebFullUrl('order.address-update.update') !!}',{data:json}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '地址修改成功!'});
                                    location.reload();
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                                this.modal_order_address_show = false;
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                                this.modal_order_address_show = false;
                            })
                        } else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                //获取地址信息
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
                    this.updateAddress.city_id = "";
                    this.updateAddress.district_id = "";
                    this.updateAddress.street_id = "";
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
                    this.updateAddress.district_id = "";
                    this.updateAddress.street_id = "";
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
                    this.updateAddress.street_id = "";
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
                //保存商家备注
                saveMerchantRemark() {
                    let json = {
                        order_id:this.orderDetail.id,
                        remark:this.form.merchant_remark,
                    };

                    console.log(json);

                    if (this.form.merchant_remark === '') {
                        this.$message({type: 'error',message: '商家备注不能为空'});
                       return false;
                    }

                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.vue-operation.remarks') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '保存成功!'});
                            location.reload();
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    })

                },
                //上传发票
                saveInvoice() {
                    let json = {
                        order_id:this.orderDetail.id,
                        invoice:this.form.thumb,
                    };

                    console.log(json);

                    if (this.form.thumb_url == '' || this.form.thumb_url == '0') {
                        this.$message({type: 'error',message: '未找到上传图片!'});
                        return false;
                    }

                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.vue-operation.invoice') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '保存成功!'});
                            location.reload();
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    })
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
                gotoGoods(id) {
                    window.open(this.goodsEditLink +`&id=`+id);
                    {{--window.location.href = `{!! yzWebFullUrl('goods.goods.edit') !!}`+`&id=`+id;--}}
                },
                submitForm(formName) {
                    console.log(this.form);
                    let that = this;
                    let json = {
                        name:this.form.name,
                        alias:this.form.alias,
                        logo:this.form.logo,
                        is_recommend:this.form.is_recommend || 0,
                        desc:this.form.desc,
                    };
                    let json1 = {
                        brand:json
                    }
                    if(this.id) {
                        json1.id = this.id
                    }
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.submit_url,json1).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    this.goBack();
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                            },response => {
                                loading.close();
                            });
                        }
                        else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
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

                goBack() {
                    history.go(-1)
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
                openMap(longitude, latitude,map_search_show,map_change_show) {
                    this.centerParam = [longitude, latitude];
                    this.markersParam = [longitude, latitude];
                    this.map_search_show = map_search_show===false? false : true;
                    this.map_change_show = map_change_show===false? false : true;
                    this.map_show = true;
                    setTimeout(() => {
                        this.initMap();
                    }, 100);
                    this.map_keyword = "";
                },
                searchMap() {
                    console.log(this.marker);
                    let that = this;
                    geo.getPoint(this.map_keyword, function (point) {

                        that.choose_marker = [point.lng, point.lat];
                        that.choose_center = [point.lng, point.lat];
                        console.log(point)
                        that.map.panTo(point);
                        that.marker.setPosition(point);
                        that.marker.setAnimation(BMAP_ANIMATION_BOUNCE);
                        setTimeout(function () {
                            that.marker.setAnimation(null)
                        }, 3600);
                    });

                },
                sureMap() {
                    let that = this;
                    this.markersParam = [];
                    this.centerParam = [];
                    this.markersParam = this.choose_marker.length <= 0 ? [113.275995, 23.117055] : this.choose_marker;
                    this.centerParam = this.choose_center.length <= 0 ? [113.275995, 23.117055] : this.choose_center;
                    this.form.store_longitude = this.markersParam[0];
                    this.form.store_latitude = this.markersParam[1];
                    console.log(this.centerParam);
                    console.log(this.markersParam);
                    that.map_show = false;

                },
                //创建和初始化地图函数：
                initMap() {
                    let that = this;
                    // [FF]切换模式后报错
                    if (!window.BMap) {
                        return;
                    }

                    console.log(this.$refs['ditucontent']);
                    for (let i in this.$refs) {
                        console.log(i)
                    }
                    this.createMap(); //创建地图
                    this.setMapEvent(); //设置地图事件
                    this.addMapControl(); //向地图添加控件
                    geo = new BMap.Geocoder();


                    // 创建标注

                    var point = new BMap.Point(this.markersParam[0], this.markersParam[1]);
                    this.marker = new BMap.Marker(point);
                    this.marker.enableDragging();

                    this.map.addOverlay(this.marker); // 将标注添加到地图中
                    this.marker.addEventListener('dragend', function (e) {//拖动标注结束
                        that.pointNew = e.point;
                        var point = that.marker.getPosition();
                        geo.getLocation(point, function (address) {
                            console.log(address.address);
                            that.map_keyword = address.address;
                        });
                        console.log(e);
                        console.log("使用拖拽获取的百度坐标" + that.pointNew.lng + "," + that.pointNew.lat);
                        that.choose_marker = [that.pointNew.lng, that.pointNew.lat];
                        that.choose_center = [that.pointNew.lng, that.pointNew.lat];
                    });
                    if(this.map_change_show){
                        this.marker.setLabel(new BMap.Label('请您移动此标记，选择您的坐标！', {'offset': new BMap.Size(10, -20)}));
                    }
                    if (parent.editor && parent.document.body.contentEditable == "true") {
                        //在编辑状态下
                        setMapListener(); //地图改变修改外层的iframe标签src属性
                    }
                },

                //创建地图函数：
                createMap() {
                    this.map = new BMap.Map(this.$refs['ditucontent']); //在百度地图容器中创建一个地图
                    // this.centerParam = '116.712617,24.778619';
                    // var centerArr = this.centerParam.split(",");
                    var point = new BMap.Point(
                        this.centerParam[0],
                        this.centerParam[1]
                    ); //

                    this.zoomParam = 12;
                    this.map.centerAndZoom(point, parseInt(this.zoomParam)); //设定地图的中心点和坐标并将地图显示在地图容器中
                },

                //地图事件设置函数：
                setMapEvent() {
                    // this.map.disableDragging(); //启用地图拖拽事件，默认启用(可不写)
                    this.map.enableScrollWheelZoom(); //启用地图滚轮放大缩小
                    this.map.enableDoubleClickZoom(); //启用鼠标双击放大，默认启用(可不写)
                    this.map.enableKeyboard(); //启用键盘上下左右键移动地图
                },

                //地图控件添加函数：
                addMapControl() {
                    this.map.addControl(new BMap.NavigationControl());
                    this.map.addControl(top_left_control);
                    this.map.addControl(top_left_navigation);
                    this.map.addControl(top_right_navigation);
                },

                setMapListener() {
                    var editor = parent.editor,
                        containerIframe,
                        iframes = parent.document.getElementsByTagName("iframe");
                    for (var key in iframes) {
                        if (iframes[key].contentWindow == window) {
                            containerIframe = iframes[key];
                            break;
                        }
                    }
                    if (containerIframe) {
                        this.map.addEventListener("moveend", mapListenerHandler);
                        this.map.addEventListener("zoomend", mapListenerHandler);
                        this.marker.addEventListener("dragend", mapListenerHandler);
                    }

                    function mapListenerHandler() {
                        var zoom = this.map.getZoom();
                        this.center = this.map.getCenter();
                        this.marker = window.marker.getPoint();
                        containerIframe.src = containerIframe.src
                            .replace(
                                new RegExp("([?#&])center=([^?#&]+)", "i"),
                                "$1center=" + center.lng + "," + center.lat
                            )
                            .replace(
                                new RegExp("([?#&])markers=([^?#&]+)", "i"),
                                "$1markers=" + this.marker.lng + "," + this.marker.lat
                            )
                            .replace(new RegExp("([?#&])zoom=([^?#&]+)", "i"), "$1zoom=" + zoom);
                        editor.fireEvent("saveScene");
                    }
                },
            },
        })

    </script>
@endsection


