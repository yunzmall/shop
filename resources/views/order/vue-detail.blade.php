@extends('layouts.base')
@section('title', '订单详情')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
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
                                    <div>会员折扣价</div>
                                    <div>￥[[orderDetail.order_goods_price]]</div>
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
                                    <div>-￥[[orderDetail.discount_price]]</div>
                                </div>
                                <div class="order-sum-li">
                                    <div>抵扣</div>
                                    <div>-￥[[orderDetail.deduction_price]]</div>
                                </div>
                                <div class="order-sum-li">
                                    <div>运费</div>
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
                        
                        
                    </div>
                </div>
                <div v-if="orderDetail.address != null" class="vue-head">
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
                <div v-if="refundApply != null && Object.keys(refundApply).length > 0" class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">售后申请</div>
                        {{--<el-button size="small" plain type="success">success</el-button>--}}
                        {{--<el-button size="small" plain type="warning">warning</el-button>--}}
                        {{--<el-button size="small" plain type="danger">danger</el-button>--}}
                        {{--<el-button size="small" plain type="primary">primary</el-button>--}}
                        {{--<el-button size="small" plain type="info">info</el-button>--}}
                    </div>
                    <div class="vue-main-form">
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

                                        <template v-if="process_item1.value == 30 && refundApply.resend_express != null">
                                            <div>快递名称：[[refundApply.resend_express.express_company_name]]</div>
                                            <div>快递单号：[[refundApply.resend_express.express_sn]]</div>
                                            <el-button plain size="mini" type="primary" @click="selectRefundLogistics(process_item1.value)">查看物流</el-button>
                                        </template>



                                    </template>
                                </el-step>
                            </el-steps>
                        </el-form-item>
                        <el-form-item label="售后类型" prop="">
                            <div>[[refundApply.refund_type_name]]</div>
                        </el-form-item>
                        <el-form-item v-if="refundApply.refund_type != 2" label="退款金额" prop="">
                            <div>
                                ￥[[refundApply.price]]
                                <el-tag v-if="refundApply.change_log != null" size="small" type="danger">改价</el-tag>
                                <template v-if="refundApply.status < 6 && orderDetail.plugin_id == 0">
                                    <a style="color:#29BA9C;padding-left: 15px" @click="refund_change_show = true">修改金额</a>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item label="退款原因" prop="">
                            <div>[[refundApply.reason]]</div>
                        </el-form-item>
                        <el-form-item label="说明" prop="">
                            <div>[[refundApply.content]]</div>
                        </el-form-item>
                        <el-form-item v-if="refundApply.images != null && refundApply.images.length > 0" label="图片凭证" prop="">
                            <div>
                                <el-image style="width: 150px" v-for="(refund_img,imgskey3) in refundApply.images" :src="refund_img"></el-image>
                            </div>
                        </el-form-item>

                    </div>
                </div>
                <!-- 发票 todo 这里的页面感觉需要优化一下-->
                <div v-if="orderDetail.collect_name || (orderDetail.order_invoice && orderDetail.order_invoice.collect_name)" style="display:flex;">
                    <div class="vue-head" style="flex:5">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">发票信息</div>
                        </div>
                        <div class="vue-main-form">
                            <el-form label-width="25%">
                                <el-form-item label="发票类型：" prop="">
                                    <div>[[orderDetail.invoice_type == 1 ? '纸质发票' : '电子发票' ]]</div>
                                </el-form-item>
                                <el-form-item label="发票抬头：" prop="">
                                    <div>[[orderDetail.rise_type == 1 ? '个人' : '单位' ]]</div>
                                </el-form-item>
                                <el-form-item label="单位/抬头名称：" prop="">
                                    <div>[[orderDetail.collect_name]]</div>
                                </el-form-item>
                                <el-form-item label="邮箱：" prop="">
                                    <div>[[orderDetail.email]]</div>
                                </el-form-item>
                                <el-form-item label="纳税人识别号：" prop="">
                                    <div>[[orderDetail.company_number]]</div>
                                </el-form-item>
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
                                    <!-- <embed :src="form.thumb_url" width="100%" height="100%" :href="form.thumb_url"></embed> -->
                                    <img :src="form.thumb_url" alt="" style="width:150px;height:82px;border-radius: 5px;cursor: pointer;">
                                    <i class="el-icon-close" @click.stop="clearImg('advert_one','img')" title="点击清除图片"></i>
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
                                    <!-- <div class="list-con-goods-text" :style="{justifyContent:(scope.row.goods_option_title?'':'center')}"> -->
                                        <div class="list-con-goods-title"
                                             style="color:#29BA9C;cursor: pointer;"
                                             @click="gotoGoods(scope.row.goods_id)">
                                            [[scope.row.title]]
                                        </div>
                                        <div class="list-con-goods-option">规格： [[scope.row.goods_option_title]]</div>
                                        <div class="list-con-goods-option">数量： [[scope.row.total]]</div>
                                    <!-- </div> -->
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
                            {{--<el-table-column prop="down_time" label="手续费" min-width="180" align="center" class="edit-cell">--}}
                                {{--<template slot-scope="scope">--}}
                                    {{--<div style="display:flex;justify-content: center;">--}}
                                        {{--<div style="flex:1;text-align:right">服务费：</div>--}}
                                        {{--<div style="flex:1;text-align:left">￥123.3333</div>--}}
                                    {{--</div>--}}
                                {{--</template>--}}
                            {{--</el-table-column>--}}
                            
                            
                        </el-table>
                    
                        
                    </div>
                </div>
            </el-form>
            <!-- 修改收货地址 -->
            <el-dialog :visible.sync="modal_order_address_show" width="900px"  title="修改收货信息">
                <div style="overflow:auto">
                    <div style="overflow:auto" id="update-address">
                        <el-form ref="updateAddress" :model="updateAddress" label-width="15%">
                            <el-form-item label="收件人" prop="">
                                <el-input v-model="updateAddress.realname" placeholder="收件人姓名" style="width:70%"></el-input>
                            </el-form-item>
                            <el-form-item label="联系方式">
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
                            <el-form-item label="详细地址">
                                <el-input v-model="updateAddress.address" placeholder="详细地址" style="width:70%"></el-input>
                            </el-form-item>
                        </el-form>

                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="modal_order_address_show = false">取 消</el-button>
                    <el-button type="primary" @click="addressUpdate">确 认</el-button>
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
                    <!-- <el-button type="primary" @click="">确 认</el-button> -->
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

                                    {{--<div style="padding:5px 0">[2020-12-12 09:12:23] 物流信息更新物流信息更新物流信息更新物流信息更新物流信息更新物流信息更新</div>--}}
                                    {{--<div style="padding:5px 0">[2020-12-12 09:12:23] 物流信息更新物流信息更新物流信息更新物流信息更新物流信息更新物流信息更新</div>--}}
                                    {{--<div style="padding:5px 0">[2020-12-12 09:12:23] 物流信息更新物流信息更新物流信息更新物流信息更新物流信息更新物流信息更新</div>--}}
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                <el-button @click="modal_dispatch_info = false">关 闭</el-button>
                {{--<el-button type="primary" @click="confirmRefundReject()">确 认</el-button>--}}
            </span>
            </el-dialog>
            <!-- 取消发货 -->
            <el-dialog :visible.sync="cancel_send_show" width="750px"  title="取消发货">
                <div style="height:300px;overflow:auto" id="cancel-send">
                    <div style="color:#000;font-weight:500">取消发货原因</div>
                    <el-input v-model="cancel_send_con" :rows="10" type="textarea"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                <el-button @click="cancel_send_show = false">取 消</el-button>
                <el-button type="primary" @click="sureCancelSend">取消发货 </el-button>
            </span>
            </el-dialog>
            <!-- 确认发货 -->
            <el-dialog :visible.sync="confirm_send_show" width="750px"  title="确认发货">
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
                            <el-select v-model="send.express_code" clearable filterable placeholder="快递公司" style="width:70%;">
                                <el-option label="其他快递" value=""></el-option>
                                <el-option v-for="(item,index) in expressCompanies" :key="index" :label="item.name" :value="item.value"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <el-input v-model="send.express_sn" style="width:70%;"></el-input>
                        </el-form-item>
                    </el-form>

                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="confirm_send_show = false">取 消</el-button>
                    <el-button type="primary" @click="sureconfirmSend">确认发货 </el-button>
                </span>
            </el-dialog>
            <!-- 多包裹确认发货 -->
            <el-dialog :visible.sync="more_send_show" width="750px"  title="分批发货">
                <div id="separate-send">
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
            <!-- 驳回售后申请 -->
            <el-dialog :visible.sync="refund_reject_show" width="750px"  title="驳回售后申请">
                <div style="height:300px;overflow:auto" id="refund-reject">
                    <div style="color:#000;font-weight:500">驳回原因</div>
                    <el-input v-model="refund_reject_reason" :rows="10" type="textarea"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                <el-button @click="refund_reject_show = false">取 消</el-button>
                <el-button type="primary" @click="confirmRefundReject()">确 认</el-button>
            </span>
            </el-dialog>
            <!-- 通过申请 -->
            <el-dialog :visible.sync="refund_pass_show" width="650px"  title="通过申请">
                <el-form ref="refund_pass_form" :model="refund_pass_form">
                    <el-form-item label="">
                        退货地址：
                        <el-select v-model="refund_pass_form.refund_address" clearable placeholder="请选择退货地址" style="width:150px">
                            <el-option v-for="(vr, kr) in refund_pass_address" :label="vr.address_name" :key="kr" :value="vr.id">
                                <span v-if="vr.is_default">[[vr.address_name]](默认地址)</span>
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        留言：
                        <el-input v-model="refund_pass_form.message" :rows="8" type="textarea"></el-input>
                    </el-form-item>
                </el-form>

                <span slot="footer" class="dialog-footer">
                <el-button @click="refund_pass_show = false">取 消</el-button>
                <el-button type="primary" @click="confirmRefundPass()">确 认</el-button>
            </span>
            </el-dialog>
            <!-- 换货确认发货 -->
            <el-dialog :visible.sync="refund_resend_show" width="650px"  title="确认发货">
                <div style="height:350px;overflow:auto" id="refund-resend">
                    <el-form ref="refund_resend" :model="refund_resend" label-width="15%">
                        <el-form-item label="快递公司">
                            <el-select v-model="refund_resend.express_code" clearable filterable placeholder="快递公司" style="width:70%;">
                                <el-option label="其他快递" value="其他快递"></el-option>
                                <el-option v-for="(vr1,kr1) in expressCompanies" :key="kr1" :label="vr1.name" :value="vr1.value"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <el-input v-model="refund_resend.express_sn" style="width:70%;"></el-input>
                        </el-form-item>
                    </el-form>

                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="refund_resend_show = false">取 消</el-button>
                    <el-button type="primary" @click="confirmRefundResend()">确认发货 </el-button>
                </span>
            </el-dialog>
            <!-- 查看退货、换货物流信息 -->
            <el-dialog :visible.sync="modal_refund_logistics" width="750px" center title="查看售后物流信息">
                <div style="height:400px;overflow:auto" id="dispatch-info">
                    <el-form label-width="15%">
                        <el-form-item label="物流公司" prop="">
                            [[refundLogistics.company_name]]
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <div style="display:inline-block;margin-right:20px">[[refundLogistics.express_sn]]</div>
                        </el-form-item>
                        <el-form-item label="物流情况" prop="">
                            <template v-if="refundLogistics.data != null">
                                <div v-for="(item3, k3) in refundLogistics.data"  :key="k3">[[item3.time]] [[item3.context]]</div>
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
            <el-dialog :visible.sync="refund_change_show" center  width="500px"  title="修改退款金额">
                <div style="height:350px;overflow:auto" id="refund-change">
                    <div class="tip" style="color:#f00;margin:10px 0;">提示：修改后退款金额不能小于0元</div>
                    <el-form label-width="15%">
                        <el-form-item label="加或减" prop="refund_change_price">
                            <el-input @input="inputPrice(refund_change_price)" v-model="refund_change_price" style="width:60%;"></el-input>
                        </el-form-item>
                    </el-form>

                    <div style="display:inline-block;color:#000;margin-right:20px;font-weight:500;">
                        <div style="display:flex;text-align:center;line-height:28px;align-items: center;">
                            <div style="margin-right:15px">
                                原退款金额<br>
                                ￥[[parseFloat(refund_price)]]
                            </div>
                            <div style="margin-right:15px">+</div>
                            <div style="margin-right:15px">
                                价格修改<br>
                                ￥[[(parseFloat(refund_change_price))]]
                            </div>
                            <div style="margin-right:15px">=</div>
                            <div style="margin-right:15px">
                                退款金额<br>
                                <strong style="color:#f00">￥[[parseFloat(parseFloat(refund_price)+(parseFloat(refund_change_price))).toFixed(2)]]</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="refund_change_show = false">取 消</el-button>
                    <el-button type="primary" @click="refundChangePrice()">确认修改 </el-button>
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
    <!-- <script src="{{resource_get('static/yunshop/tinymceTemplate.js')}}"></script> -->
    
    @include('public.admin.tinymceee')  
    <!-- @include('public.admin.uploadImg')   -->
    @include('public.admin.uploadfile')
    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    is_pdf:false,
                    street: '{!! \Setting::get("shop.trade")['is_street'] !!}',
                    id:0,
                    getDataUrl: '{!! yzWebFullUrl('order.detail.get-data') !!}',//获取订单数据链接

                    order_id:0,
                    orderDetail:{},
                    yz_member:{},
                    div_from:{},
                    refundApply:{}, //退款信息
                    refund_change_price:0,
                    refund_price:0,
                    refund_change_show:false, //修改退款金额
                    refundLogistics:{}, //退款物流信息
                    modal_refund_logistics: false,



                    dispatch:{}, //订单物流信息
                    modal_dispatch_info: false,

                    form:{
                    merchant_remark:'',//商家备注
                        thumb_url:'',//发票地址
                        thumb:'',//发票地址
                    },
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

                    // 地址修改记录
                    modal_update_show:false,
                    update_list:[],
                    areaLoading:false,

                    // 支付记录
                    modal_pay_show:false,
                    pay_list:[],

                    cancel_send_show:false,// 取消发货弹窗
                    cancel_send_con:"",//取消发货原因
                    cancel_send_id:'',
                    confirm_send_show:false,// 确认发货弹窗
                    confirm_send_id:"",
                    send:{
                        dispatch_type_id:1,
                        express_code:"",
                        express_sn:"",
                    },
                    send_rules:{},
                    address_info:{},
                    expressCompanies:[],//快递公司


                    //退款申请拒绝
                    refund_reject_show: false,
                    refund_reject_reason:'',

                    //通过退款申请
                    refund_pass_show: false,
                    refund_pass_address: [],
                    refund_pass_form:{},

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
                    // 查看商品
                    get_goods_show:false,
                    get_goods_list:[],
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
                    console.log(data);
                },
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});

                    this.$http.post(this.getDataUrl,{id:this.id}).then(function (response) {
                            if (response.data.result){
                                this.orderDetail = response.data.data.order;
                                this.order_id = response.data.data.order.id;
                                this.yz_member = response.data.data.yz_member;

                                this.div_from = response.data.data.div_from;

                                this.refundApply = response.data.data.refundApply;

                                if (this.refundApply) {
                                    this.refund_price = this.refundApply.price;
                                }
                                // console.log(this.refundApply != null && Object.keys(this.refundApply).length > 0);
                                //console.log(this.refundApply);

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

                //显示退款物流信息
                selectRefundLogistics(value) {
                    console.log(value);
                    this.modal_refund_logistics = true;
                    let loading = this.$loading({target:document.querySelector("#update-list"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.detail.refund-express') !!}',{order_id:this.order_id,refund_value:value}).then(function (response) {
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

                //退款操作
                refundConfirm(operationType) {
                    console.log(operationType);
                    if (operationType == -1) {
                        return this.refundReject();
                    } else if (operationType == 1) {
                        return this.refundPay();
                    } else if (operationType == 2) {
                        return this.refundConsensus()
                    } else if (operationType == 3) {
                        return this.refundPass()
                    } else if (operationType == 5) {
                        return this.refundResend()
                    }  else if (operationType == 10) {
                        return this.refundClose()
                    }
                    this.$message.error(operationType + "操作方式不存在！");
                },
                //正则过滤掉除\-\.0-9以外的字符，不会写
                inputPrice(refund_change_price) {

                },
                //确认修改退款金额
                refundChangePrice() {

                    if(this.refund_change_price === '') {
                        this.$message.error("修改金额不能为空！");
                        return false;
                    }

                    // if((parseFloat(this.refundApply.price) + parseFloat(this.refund_change_price)) < 1) {
                    //     this.$message.error("退款金额不能必须大于0！");
                    //     return false;
                    // }


                    let json = {
                        'order_id':this.id,
                        'refund_id':this.refundApply.id,
                        'change_price':this.refund_change_price,
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
                //同意退款
                refundPay() {
                    this.$confirm('是否同意此订单退款？', '提示', {confirmButtonText: '同意',cancelButtonText: '取消',type: 'warning'}).then(() => {

                        let url = '{!! yzWebFullUrl('refund.pay.index') !!}'

                        url += '&refund_id=' + this.refundApply.id;

                        window.location.href = url;

                        {{--let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});--}}
                        {{--this.$http.post('{!! yzWebFullUrl('refund.vue-operation.pay') !!}',{refund_id:this.refundApply.id}).then(function (response) {--}}
                                {{--if (response.data.result) {--}}
                                    {{--this.$message({type: 'success',message: '操作成功'});--}}
                                {{--} else{--}}
                                    {{--this.$message({type: 'error',message: response.data.msg});--}}
                                {{--}--}}
                                {{--loading.close();--}}
                                {{--this.getData(); //重新获取数据刷新页面--}}
                            {{--},function (response) {--}}
                                {{--this.$message({type: 'error',message: response.data.msg});--}}
                                {{--loading.close();--}}
                            {{--}--}}
                        {{--);--}}
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
                },
                //手动退款
                refundConsensus() {
                    this.$confirm('确认此订单手动退款完成吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('refund.vue-operation.consensus') !!}',{refund_id:this.refundApply.id}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功'});
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                                this.getData(); //重新获取数据刷新页面
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
                },
                //通过申请
                refundPass() {
                    this.refund_pass_show = true;
                    this.getRefundAddress();
                },
                //通过申请
                confirmRefundPass() {

                    if(!this.refund_pass_form.refund_address) {
                        this.$message.error("请选择退货地址！");
                        return false;
                    }
                    let json = {
                        'refund_id':this.refundApply.id,
                        'message':this.refund_pass_form.message,
                        'refund_address': this.refund_pass_form.refund_address,
                    };
                    let loading = this.$loading({target:document.querySelector("#refund-pass"),background: 'rgba(0, 0, 0, 0)'});

                    this.$http.post('{!! yzWebFullUrl('refund.vue-operation.pass') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功!'});
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.refund_pass_show = false;
                        this.getData(); //重新获取数据刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.refund_pass_show = false;
                    });
                },
                //获取退款地址
                getRefundAddress() {
                    this.$http.post('{!! yzWebFullUrl('goods.return-address.ajax-all-address') !!}').then(function (response) {
                        if (response.data.result) {
                            this.refund_pass_address = response.data.data;
                            console.log(this.refund_pass_address);
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                    });
                },
                //换货确认发货
                refundResend() {
                    this.refund_resend_show = true;
                },
                //换货确认发货
                confirmRefundResend() {
                    let json = {
                        refund_id:this.refundApply.id,
                        express_code:this.refund_resend.express_code,
                        express_company_name:this.refund_resend.express_code,
                        express_sn:this.refund_resend.express_sn,
                    };
                    let loading = this.$loading({target:document.querySelector("#refund-pass"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('refund.vue-operation.resend') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功!'});
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.refund_resend_show = false;
                        this.getData(); //重新获取数据刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.refund_resend_show = false;
                    });
                },
                //换货完成关闭申请
                refundClose() {
                    this.$confirm('确认此订单换货完成吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('refund.vue-operation.close') !!}',{refund_id:this.refundApply.id}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功'});
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                                this.getData(); //重新获取数据刷新页面
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
                },
                //驳回退款申请
                refundReject() {
                    this.refund_reject_show = true;
                    this.refund_reject_reason = "";
                },
                //驳回退款申请
                confirmRefundReject() {

                    let json = {
                        'refund_id':this.refundApply.id,
                        'reject_reason':this.refund_reject_reason,
                    };
                    let loading = this.$loading({target:document.querySelector("#refund-reject"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('refund.vue-operation.reject') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功!'});
                        }
                        else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.refund_reject_show = false;
                        this.getData(); //重新获取数据刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.refund_reject_show = false;
                    });
                },
                //订单操作
                orderConfirm(operationType, order) {
                    console.log(operationType);
                    if (operationType == 1) {
                        this.confirmPay(order.id);
                    } else if (operationType == 2) {
                        this.confirmSend(order.id, order);
                    } else if (operationType == 3) {
                        this.confirmReceive(order.id);
                    } else if (operationType == 'cancel_send') {
                        this.cancelSend(order.id);
                    } else if (operationType == 'separate_send') {
                        this.separateSend(order.id, order);
                    }

                },
                // 确认付款
                confirmPay(id) {
                    this.$confirm('确认此订单已付款吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('order.vue-operation.pay') !!}',{order_id:id}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功'});
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                                location.reload(); //刷新页面
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
                },
                // 确认发货
                confirmSend(id,item) {
                    this.confirm_send_show = true;
                    this.send = {
                        dispatch_type_id :1,
                        express_code:"",
                        express_sn:""
                    };
                    this.confirm_send_id = id;
                    this.address_info = item.address;
                },
                //确认发货
                sureconfirmSend() {

                    let json = {
                        dispatch_type_id:this.send.dispatch_type_id,
                        express_code:this.send.express_code,
                        express_sn:this.send.express_sn,
                        order_id:this.confirm_send_id,
                    };
                    console.log(json);
                    // if(this.send.express_sn == "") {
                    //     this.$message.error("快递单号不能为空！");
                    //     return;
                    // }
                    let loading = this.$loading({target:document.querySelector("#confirm-send"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.vue-operation.send') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '确认发货成功!'});
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.confirm_send_show = false;
                        location.reload(); //刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.confirm_send_show = false;
                    })
                },
                //多包裹发货
                separateSend(id,item) {
                    this.more_send_show = true;
                    this.confirm_send_id = id;
                    this.send = {
                        dispatch_type_id :1,
                        express_code:"",
                        express_sn:"",
                    };

                    this.address_info = item.address;
                    this.getSeparateSendOrderGoods(id);
                },
                // 多包裹确认发货 选择商品
                moreSendChange(selection) {
                    let arr = [];
                    for(let j = 0,len = selection.length; j < len; j++){
                        console.log(selection[j].id);
                        arr.push(selection[j].id);
                    }
                    this.send_order_goods_ids = arr;
                },
                // 获取可选择的商品 多包裹发货
                getSeparateSendOrderGoods(id) {
                    this.$http.post('{!! yzWebFullUrl('order.multiple-packages-order-goods.get-order-goods') !!}', {order_id:id}).then(function (response) {
                        if (response.data.result) {
                            this.order_goods_send_list = response.data.data;
                            // console.log(this.order_goods_send_list);
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                    });
                },
                //多包裹确认发货
                confirmMoreSend() {
                    let json = {
                        dispatch_type_id:this.send.dispatch_type_id,
                        express_code:this.send.express_code,
                        express_sn:this.send.express_sn,
                        order_id:this.confirm_send_id,
                        order_goods_ids:this.send_order_goods_ids,
                    };
                    console.log(json);
                    if(this.send_order_goods_ids == undefined || this.send_order_goods_ids.length <= 0) {
                        this.$message.error("请选择分批发货订单商品！");
                        return;
                    }

                    if(json.express_sn == "") {
                        this.$message.error("快递单号不能为空！");
                        return;
                    }

                    let loading = this.$loading({target:document.querySelector("#separate-send"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.vue-operation.separate-send') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '发货成功!'});
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.more_send_show = false;
                        location.reload(); //刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.more_send_show = false;
                    })
                },
                // 取消发货
                cancelSend(id) {
                    this.cancel_send_show = true;
                    this.cancel_send_con = "";
                    this.cancel_send_id = id;
                    console.log(id)
                },
                // 确认取消发货
                sureCancelSend() {
                    let json = {
                        // route:'order.operation.manualRefund',
                        order_id:this.cancel_send_id,
                        cancelreson:this.cancel_send_con,
                    };
                    console.log(json);
                    let loading = this.$loading({target:document.querySelector("#cancel-send"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.vue-operation.cancel-send') !!}',json).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '取消发货成功!'});
                        } else {
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.close_order_show = false;
                        location.reload(); //刷新页面
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                        this.close_order_show = false;
                    })
                },
                // 确认收货
                confirmReceive(id) {
                    this.$confirm('确认订单收货吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('order.vue-operation.receive') !!}',{order_id:id}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功'});
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                                location.reload(); //刷新页面
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
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
                addressUpdate() {

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

                    console.log(json);
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
                    window.open(`{!! yzWebFullUrl('goods.goods.edit') !!}`+`&id=`+id);
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
            },
        })

    </script>
@endsection


