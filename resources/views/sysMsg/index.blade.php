@extends('layouts.base')
@section('title', '消息通知')
<style>
    .content{padding:0 !important;}
    .vue-content{display:flex;margin: 0;width: 100%;font-weight: 500;}
    .vue-nav{position:fixed;width:170px;flex: 0 0 170px;overflow-y: auto;overflow-x: hidden;z-index:2;box-shadow: 0 10px 10px -12px rgba(0, 0, 0, 0.42), 0 4px 10px 0px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.2);height: calc(100vh - 50px);}
    .nav-li{padding-left:15px;line-height: 35px;margin:5px 0;cursor: pointer;color:#333;color:#000;margin:5px 0;font-weight:300;font-family: arial, 'Hiragino Sans GB', 'Microsoft Yahei', '微软雅黑', '宋体', \5b8b\4f53, Tahoma, Arial, Helvetica, STHeiti;}
    .nav-li i{margin:0 5px;font-weight:600}
    .nav-li-tips{display:inline-block;background:#ff485d;color:#fff;border-radius: 8px 8px 8px 1px;position: relative;top:-10px;height: 15px;line-height: 15px;padding:0 5px;font-size: 12px;font-weight: 500;}
    .nav-li-selected{font-weight: 600;background: #f5f5f5;}
    .message{flex:1;background: #f5f5f5;margin-left: 170px;}
    .message-top{margin:15px;background:#fff;border-radius: 8px;padding:15px;}
    .message-top-title{line-height: 18px;border-left: 5px solid #29ba9c;padding-left:20px;flex:1;font-size:14px;font-weight: 600;}
    .message-top-read{text-align: right;flex:0 0 150px;cursor: pointer;font-size:14px;font-weight: 600;margin-right:20px}
    .message-top-read:hover{color:#29ba9c}
    .search{margin-top:30px;}
    .message-content{display: flex;justify-content: start;flex-wrap: wrap;margin-bottom: 55px;}
    .message-li{width: 50%;margin:15px 0;}
    .message-li-col{display:flex;flex-direction: column;margin:0 15px;padding:15px;border-radius: 8px;height: 150px;overflow: hidden;background: #fff;}
    .message-li-one{display:flex;align-items: center;font-size: 15px;overflow: hidden;}
    .message-li-one-icon{width:60px;height:60px;line-height:60px;border-radius: 50px;position: relative;}
    .green{background: #29ba9c;}
    .yellow{background: #ffba00;}
    .red{background: #ff0084;}
    .blue{background: #00a2ff;}
    .purple{background: #632dff;}
    .orange{background: #ff6a28;}
    .message-li-one-icon i{font-size: 26px;color:#fff;padding-left: 17px;}
    .red-rod{background:#f00;width: 8px;height: 8px;border-radius: 50px;position: absolute;top:0;right: 0;}
    .message-li-one-con{display:flex;flex-direction: column;align-items: flex-start;flex:1;margin-left:20px;overflow: hidden;text-overflow:ellipsis;margin-right: 20px; }
    .message-li-one-title{font-weight: 600;color:#000;}
    .message-li-one-hh{text-overflow:ellipsis;height:23.33px; overflow: hidden;text-overflow: ellipsis;-webkit-text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp:1;-webkit-box-orient: vertical;}
    .message-li-one-order{display:flex;flex-wrap:wrap;width:100%;font-size:12px;color:#999;overflow: hidden;line-height: 23.33px;}
    .message-li-one-order1{width:48%;overflow: hidden;word-break: keep-all;text-overflow: ellipsis;}
    .message-li-one-order2{width:39%;overflow: hidden;text-overflow: ellipsis;white-space:nowrap}

    .message-li-two{flex:1;align-items: flex-end;display:flex;}


    .message-li-two-time{flex:1;color:#999;font-size:12px;}
    .message-li-two-more{margin-right: 20px;color:#ffae01;cursor: pointer;}
    .message-li-two-more a{color:#ffae01}
    [v-cloak] {display: none;}
</style>
@section('content')
    <div class="all">
        <div id="app" v-cloak v-loading="loading">
            <link rel="stylesheet" href="//at.alicdn.com/t/font_432132_fpp3d6mr6kk.css">
            <div class="vue-content">
                <div class="vue-nav">
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==1}" @click="chooseMenu(1)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_allmessage" style="padding-right:5px"></i>全部消息
                        </div>
                        <div class="nav-li-tips" v-if="msgType[0].has_many_log_count>0">[[msgType[0].has_many_log_count]]</div>
                    </div>
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==2}" @click="chooseMenu(2)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_system" style="padding-right:5px"></i>系统通知
                        </div>
                        <div class="nav-li-tips" v-if="msgType[1].has_many_log_count>0">[[msgType[1].has_many_log_count]]</div>
                    </div>
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==3}" @click="chooseMenu(3)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_apply" style="padding-right:5px"></i>申请通知
                        </div>
                        <div class="nav-li-tips" v-if="msgType[4].has_many_log_count>0">[[msgType[4].has_many_log_count]]</div>
                    </div>
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==4}" @click="chooseMenu(4)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_goods" style="padding-right:5px"></i>商品通知
                        </div>
                        <div class="nav-li-tips" v-if="msgType[5].has_many_log_count>0">[[msgType[5].has_many_log_count]]</div>
                    </div>
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==5}" @click="chooseMenu(5)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_coupons" style="padding-right:5px"></i>优惠券
                        </div>
                        <div class="nav-li-tips" v-if="msgType[6].has_many_log_count>0">[[msgType[6].has_many_log_count]]</div>
                    </div>
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==6}" @click="chooseMenu(6)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_order" style="padding-right:5px"></i>订单通知
                        </div>
                        <div class="nav-li-tips" v-if="msgType[2].has_many_log_count>0">[[msgType[2].has_many_log_count]]</div>
                    </div>
                    <div class="nav-li" :class="{'nav-li-selected':nav_index==7}" @click="chooseMenu(7)">
                        <div style="display:inline-block">
                            <i class="iconfont icon-ht_list_line_tixian" style="padding-right:5px"></i>提现通知
                        </div>
                        <div class="nav-li-tips" v-if="msgType[3].has_many_log_count>0">[[msgType[3].has_many_log_count]]</div>
                    </div>
                </div>
                <div class="message" v-if="view">
                    <div class="message-top">
                        <div class="" style="display:flex;align-items: center;">
                            <div class="message-top-title">系统消息通知</div>
                            <div class="message-top-read" @click="readAll">全部标记为已读</div>
                        </div>
                        <div class="search">
                            <el-form :inline="true" :model="search_form" ref="search_form" style="margin-left:10px;">
                                <el-row>
                                    <el-form-item label="" prop="">
                                        <el-date-picker v-model="search_form.times" type="datetimerange" value-format="timestamp" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">
                                    </el-date-picker>
                                    </el-form-item>
                                    <el-form-item label="" prop="">
                                        <el-select v-model="search_form.is_read" placeholder="请选择消息状态" clearable>
                                            <el-option v-for="item in status_list" :key="item.id" :label="item.word" :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <a href="#">
                                        <el-button type="primary" icon="el-icon-search" @click="search(1)">搜索
                                        </el-button>
                                    </a>
                                    </el-col>
                                </el-row>
                            </el-form>
                        </div>
                    </div>
                    <div class="message-content"> 
                        <div class="message-li" v-for="(item,index) in list">
                            <div class="message-li-col">
                                <div class="message-li-one">
                                    <div class="message-li-one-icon" :class="item.bgcolor">
                                        <i class="iconfont" :class="item.belongs_to_type.icon_src"></i>
                                        <div class="red-rod" v-if="item.is_read==0"></div>
                                    </div>
                                    <div class="message-li-one-con">
                                        <div class="message-li-one-title message-li-one-hh">[[item.title]]</div>
                                        <div class="message-li-one-order" v-if="item.type_id!=1">
                                            <div class="message-li-one-order1" v-for="(item1,index1) in item.message" :key="index1">[[item1]]</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="message-li-two">
                                    <div class="message-li-two-time">[[item.created_at]]</div>
                                    <div class="message-li-two-more" @click="read(item)">查看详情>></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="list.length<=0" style="text-align:center;width:100%;font-size:32px;font-weight:600;margin:30px 0">~~暂无更多~~</div>
                        
                        <div style="position:fixed;padding:15px;background:#fff;width:calc(100vw - 266px);text-align:center;bottom:0;box-shadow: 0 2px 9px rgba(51, 51, 51, 0.1);">
                            <el-pagination layout="prev, pager, next,jumper" 
                                @current-change="search" :total="total" 
                                :page-size="per_page" 
                                :current-page="current_page" 
                                background
                            ></el-pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let all_url = '{!! yzWebFullUrl('sysMsg.system-msg.all-message') !!}';
        let system_url = '{!! yzWebFullUrl('sysMsg.system-msg.sys-message') !!}';
        let order_url = '{!! yzWebFullUrl('sysMsg.system-msg.order-message') !!}';
        let withdraw_url = '{!! yzWebFullUrl('sysMsg.system-msg.withdrawal-message') !!}';
        let apply_url = '{!! yzWebFullUrl('sysMsg.system-msg.apply-message') !!}';
        let stock_url = '{!! yzWebFullUrl('sysMsg.system-msg.stock-message') !!}';
        let coupon_url = '{!! yzWebFullUrl('sysMsg.system-msg.coupon-message') !!}';
        console.log(all_url)
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    nav_index:1,
                    search_form:{},
                    status_list:[{id:'',word:'全部'},{id:0,word:'未读'},{id:1,word:'已读'}],
                    list:[],
                    data_url : '',
                    all_total:0,
                    msgType:[{},{},{},{},{},{},{}],
                    
                    // more
                    total:0,
                    current_page:1,
                    per_page:1,
                    loading:false,

                    view:false,

                }
            },
            created() {
                this.data_url = all_url;
                this.getData(this.data_url);
            },
            mounted() {
                
            },
            methods: {
                search(page) {
                    console.log(this.search_form)
                    let start = '';
                    let end = '';
                    if(this.search_form.times) {
                        start = this.search_form.times[0]/1000;
                        end = this.search_form.times[1]/1000;
                    }
                    let json = {
                        search:{
                            time:{start:start,end:end},
                            is_read:this.search_form.is_read
                        },
                        page:page
                    }
                    if(!json.search.time.start) {
                        json.search.time = {}
                    }
                    console.log(json)
                    this.getData(this.data_url,json);
                },
                chooseMenu(index) {
                    if(this.nav_index == index) {
                        return;
                    }
                    this.nav_index = index;
                    this.search_form = {};
                    switch(index){
                        case 1:
                            this.data_url = all_url;
                            break;
                        case 2:
                            this.data_url = system_url;
                            break;
                        case 3:
                            this.data_url = apply_url;
                            break;
                        case 4:
                            this.data_url = stock_url;
                            break;
                        case 5:
                            this.data_url = coupon_url;
                            break;
                        case 6:
                            this.data_url = order_url;
                            break;
                        case 7:
                            this.data_url = withdraw_url;
                            break;
                        default:
                            break;
                    }
                    this.getData(this.data_url);
                },
                getData(url,json) {
                    this.loading = true;
                    this.$http.post(url,json).then(function (response) {
                        if (response.data.result){
                            this.loading = false;
                            this.view = true;
                            this.list = response.data.data.list.data;
                            this.list.forEach((item,index) => {
                                this.list[index].message = [];
                                this.list[index].message = item.content.split("||");
                                if(item.type_id==1){
                                    this.list[index].bgcolor = 'green'
                                }
                                if(item.type_id==2){
                                    this.list[index].bgcolor = 'yellow'
                                }
                                if(item.type_id==3){
                                    this.list[index].bgcolor = 'red'
                                }
                                if(item.type_id==4){
                                    this.list[index].bgcolor = 'blue'
                                }
                                if(item.type_id==5){
                                    this.list[index].bgcolor = 'purple'
                                }
                                if(item.type_id==6){
                                    this.list[index].bgcolor = 'orange'
                                }
                            });
                            console.log(this.list);
                            this.total = response.data.data.list.total;
                            this.per_page = response.data.data.list.per_page;
                            this.current_page = response.data.data.list.current_page;
                            if(!this.msgType[0].has_many_log_count || this.msgType[0].has_many_log_count==0) {
                                this.msgType = response.data.data.msgType;
                            }
                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                            this.loading = false;
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        this.loading = false;
                    }
                );
                },
                read(item) {
                    console.log('{!! yzWebFullUrl('sysMsg.system-msg.read-system-message') !!}'+'&id='+item.id)
                    // return;
                    this.loading = true;
                    let json = {
                        id:item.id,
                        type:0,
                    }
                    this.$http.post('{!! yzWebFullUrl('sysMsg.system-msg.read-log') !!}',json).then(function (response) {
                        if (response.data.result){
                            this.loading = false;
                            if(item.redirect_url) {
                                window.location.href=item.redirect_url;
                            }
                            else {
                                window.location.href='{!! yzWebFullUrl('sysMsg.system-msg.read-system-message') !!}'+'&id='+item.id;
                            }
                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                            this.loading = false;
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        this.loading = false;
                    })
                },
                readAll() {
                    this.loading = true;
                    let json = {
                        type:1,
                    }
                    this.$http.post('{!! yzWebFullUrl('sysMsg.system-msg.read-log') !!}',json).then(function (response) {
                        if (response.data.result){
                            this.loading = false;
                            window.location.reload();
                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                            this.loading = false;
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        this.loading = false;
                    })
                }
                
            },

        })

    </script>
@endsection

