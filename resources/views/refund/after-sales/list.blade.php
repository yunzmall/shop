@extends('layouts.base')
@section('title', "售后列表")
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
            {{--@include('order.typeTabs')--}}

            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">售后筛选</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">

                        <el-form-item label="">
                            <el-input v-model="search_form.order_sn" placeholder="订单编号"></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input v-model="search_form.refund_sn" placeholder="售后编号"></el-input>
                        </el-form-item>

                        <el-form-item label="">
                            <el-input v-model="search_form.member_id" placeholder="购买会员ID"></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input style="width: 270px"  placeholder="昵称/姓名/手机号" v-model="search_form.member_info">
                                <el-select placeholder="选择按" style="width:90px;" v-model="search_form.member_type" clearable slot="prepend">
                                    <el-option label="昵称" value="1"></el-option>
                                    <el-option label="姓名" value="2"></el-option>
                                    <el-option label="手机号" value="3"></el-option>
                                </el-select>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input v-model="search_form.goods_id" placeholder="商品ID"></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input v-model="search_form.goods_title" placeholder="商品名称"></el-input>
                        </el-form-item>

                        <el-form-item label="">
                            <el-select v-model="search_form.refund_type" clearable placeholder="售后类型" style="width:150px">
                                <el-option label="退款" value="0"></el-option>
                                <el-option label="退款退货" value="1"></el-option>
                                <el-option label="换货" value="2"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="">
                            <el-select v-model="search_form.status" clearable placeholder="售后状态" style="width:150px">
                                <el-option label="待审核" value="0"></el-option>
                                <el-option label="待退货" value="1"></el-option>
                                <el-option label="商户待收货" value="2"></el-option>
                                <el-option label="待买家收货" value="4"></el-option>
                                <el-option label="已完成" value="99"></el-option>
                                <el-option label="已驳回" value="-1"></el-option>
                                <el-option label="取消申请" value="-2"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select v-model="search_form.time_field" clearable placeholder="操作时间" style="width:150px">
                                <el-option label="申请时间" value="create_time"></el-option>
                                <el-option label="退款完成时间" value="refund_time"></el-option>
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
                        <el-form-item label="">
                            <el-button type="primary" @click="search(1)">搜索</el-button>
                            <el-button  @click="export1()">导出</el-button>
                        </el-form-item>
                    </el-form>

                </div>
            </div>
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="" style="text-align:left;font-size:14px;color:#999">
                            <span>售后总数：[[count.total]]</span>&nbsp;&nbsp;&nbsp;
                            <span>退款金额：[[count.total_price]]</span>&nbsp;&nbsp;&nbsp;
                        </div>
                        <div class="vue-main-title-button">
                        </div>
                    </div>
                    <div v-for="(item,index) in list"
                         style="border:1px solid #e9e9e9;border-radius:10px;margin-bottom:10px">
                        <div class="list-info">
                            <div style="display:flex;flex-wrap:wrap">
                                <div  class="vue-ellipsis" style="color:#999;max-width:240px">
                                    <strong>编号：</strong>[[item.id]]&nbsp;&nbsp;&nbsp;
                                </div>
                                <div v-if="item.refund_sn" class="vue-ellipsis" style="color:#999;max-width:240px">
                                    <strong>售后编号：</strong>[[item.refund_sn]]&nbsp;&nbsp;&nbsp;
                                </div>
                                <div v-if="item.order" class="vue-ellipsis" style="color:#999;max-width:240px">
                                    <strong>订单编号：</strong>[[item.order.order_sn]]&nbsp;&nbsp;&nbsp;
                                </div>
                                <div class="vue-ellipsis" style="color:#999;max-width:230px">
                                    <strong>申请时间：</strong>[[item.create_time]]&nbsp;&nbsp;&nbsp;
                                </div>
                                <div class="vue-ellipsis" style="color:#29BA9C;max-width:230px">
                                    <strong>[[item.order_type_name]]</strong>&nbsp;&nbsp;&nbsp;
                                </div>
                                <div v-if="item.part_refund == 4" class="vue-ellipsis" style="color:red;max-width:200px">
                                    <strong>退款并关闭</strong>&nbsp;&nbsp;&nbsp;
                                </div>

                            </div>
                            <div style="flex:1;text-align:right;min-width:150px;">
                                {{--<a @click="closeOrder(item.id,item)" style="color:#29BA9C;font-size:13px;font-weight:600" v-if="item.fixed_button.close.is_show">关闭订单</a>--}}
                            </div>
                        </div>
                        <div class="list-con">
                            <div style="flex:3;min-width:400px">
                                <div v-for="(item1,index1) in item.refund_order_goods" class="list-con-goods">
                                    <div class="list-con-goods-img" style="width:80px">
                                        <el-image :src="item1.goods_thumb" style="width:70px;height:70px"></el-image>
                                    </div>
                                    <div class="list-con-goods-text"
                                         :style="{justifyContent:(item1.goods_option_title?'':'center')}">
                                        <div class="list-con-goods-title" style="color:#29BA9C;cursor: pointer;"
                                             @click="gotoGoods(item1.goods_id,item.order)">[[item1.goods_title]]
                                        </div>
                                        <div class="list-con-goods-option" v-if="item1.goods_option_title">
                                            规格：[[item1.goods_option_title]]
                                        </div>
                                    </div>
                                    <div class="list-con-goods-price">
                                        {{--<div>申请金额：[[item1.refund_price]]</div>--}}
                                        <div>数量：[[item1.refund_total]]</div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-con-member-info vue-ellipsis">
                                <div v-if="item.has_one_member" style="min-width:70%;margin:0 auto">
                                    <div @click="gotoMember(item.has_one_member.uid)"
                                         style="line-height:32px;color:#29BA9C;cursor: pointer;" class="vue-ellipsis">
                                        <strong>[[item.has_one_member.nickname]] </strong>
                                    </div>
                                    <div>[[item.has_one_member.realname]]</div>
                                    <div>[[item.has_one_member.mobile]]</div>
                                </div>
                                <div v-else style="min-width:70%;margin:0 auto">
                                    <div>会员([[item.uid]])已被删除</div>
                                </div>
                            </div>
                            <div class="list-con-member-info vue-ellipsis" style="text-align:center;min-width: 90px;">
                                <div><strong v-if="item.order">订单金额：￥[[item.order.price]]</strong></div>
                                <div><strong v-if="item.order">订单状态：[[item.order.status_name]]</strong></div>
                            </div>
                            <div class="list-con-member-info vue-ellipsis" style="min-width: 120px;">
                                <div style="min-width:70%;margin:0 auto">
                                    <div>退款金额：￥[[item.price]]</div>
                                </div>
                            </div>
                            <div class="list-con-member-info vue-ellipsis" style="text-align:center">
                                <div style="min-width:70%;margin:0 auto">
                                    <div style="color:#29BA9C">[[item.refund_type_name]] : [[item.status_name]]</div>
                                </div>
                            </div>
                            <div class="list-con-member-info vue-ellipsis"
                                 style="text-align:center;min-width: 80px;border-right:0">
                                {{--操作按钮--}}
                                {{--<div v-for="(item1,index1) in item.backend_button_models" :key="index1">--}}
                                {{--<el-button @click="orderConfirm(item1.value,item)" size="mini" :type="item1.type" style="width:80%;margin:0 auto;margin-bottom:5px;">--}}
                                {{--[[item1.name]]--}}
                                {{--</el-button>--}}
                                {{--</div>--}}

                            </div>
                        </div>
                        <div class="list-member">
                            <div style="display:flex;flex-wrap:wrap;justify-content: flex-start">
                                <template  v-if="item.order.address && item.order.address !== null ">
                                [[item.order.address.realname]]&nbsp;&nbsp;&nbsp;[[item.order.address.mobile]]&nbsp;&nbsp;&nbsp;[[item.order.address.address]]
                                </template>
                            </div>
                            <div style="justify-content: flex-end;flex:1;display:flex">
                                <el-tooltip class="item" effect="dark" content="打印小票" placement="bottom">
                                    <a style="color:#29BA9C;margin-right: 10px;" @click="orderPrinter(item)">
                                        <img src="{!! \app\common\helpers\Url::shopUrl('static/images/printer.png') !!}" style="width: 20px;">
                                    </a>
                                </el-tooltip>
                                <div>
                                    <a @click="gotoDetail(item)" style="color:#29BA9C">查看详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


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
        </div>
    </div>

    <script>
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {

                return {
                    order_id:"",
                    listUrl: '{!! yzWebFullUrl('refund.after-sales.get-list') !!}',//获取订单数据链接
                    exportUrl: '{!! yzWebFullUrl('refund.after-sales.export') !!}',//订单数据导出链接
                    detailUrl: '{!! yzWebFullUrl('refund.after-sales.detail') !!}',
                    goodsEditUrl: '', //列表所以商品跳转指定链接
                    list:[],
                    count:{},
                    times:[],

                    //页面返回全部参数
                    responseResults:{},

                    //搜索条件参数
                    search_form:{
                        member_id:"",
                    },


                    //分页
                    current_page:1,
                    total:1,
                    per_page:1,
                }
            },
            created() {
                let result = this.viewReturn();
                this.__initial(result);
                //this.search_form.member_id = this.getParam('member_id')
            },
            mounted() {
                this.getData(1);
            },
            methods: {
                //视图返回数据
                viewReturn() {
                    return {!! $data?:'{}' !!};
                },
                //初始化页面数据，请求链接
                __initial(data) {

                    if (data.listUrl) {
                        this.listUrl = data.listUrl;
                    }

                    if (data.exportUrl) {
                        this.exportUrl = data.exportUrl;
                    }

                    if (data.detailUrl) {
                        this.detailUrl = data.detailUrl;
                    }

                    if (data.goodsEditUrl) {
                        this.goodsEditUrl = data.goodsEditUrl;
                    }

                    this.responseResults = data;

                    console.log(data);
                },
                getData(page) {
                    //console.log(this.times);

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
                search(val) {
                    this.getData(val);
                },

                export1(){
                    let that = this;
                    console.log(that.search_form);

                    let json = that.search_form;

                    if(this.times && this.times.length>0) {
                        json.start_time = this.times[0];
                        json.end_time = this.times[1];
                    }

                    let url = this.exportUrl;

                    for(let i in json){
                        if (json[i]) {
                            url+="&search["+i+"]="+ json[i]
                        }
                    }

                    console.log(url);
                    window.location.href = url;
                },

                gotoDetail(item) {
                    let link = this.detailUrl +'&id='+item.id;
                    //let link = item.fixed_button.detail.api +'&id='+item.id+'&order_id='+item.id;
                    // window.location.href = link;
                    window.open(link);
                },

                gotoGoods(id,order) {

                    if (this.goodsEditUrl) {
                        let link = this.goodsEditUrl;
                        window.location.href = link+`&id=`+id;
                        return;
                    }

                    if(!order.fixed_button.goods_detail.is_show) {
                        return
                    }
                    let link = order.fixed_button.goods_detail.api;
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
                },
                orderPrinter(row) {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('order.order-list.orderPrinter') !!}',{order_id: row.order.id}).then( (response) => {
                            if (response.data.result) {
                                this.$message({type: 'success',message: response.data.msg});
                            }
                            else{
                                this.$message({type: 'error',message: response.data.msg});
                            }
                            loading.close();
                        }, (response) => {
                            this.$message({type: 'error',message: response.data.msg});
                            loading.close();
                        }
                    );
                }
            },
        })
    </script>
@endsection