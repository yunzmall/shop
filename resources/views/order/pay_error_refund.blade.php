@extends('layouts.base')
@section('title', "支付失败退款")
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
    </style>
    <div class="all">
        <div id="app" v-cloak>

            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">回调异常退款</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <div>
                        <el-form :inline="true" :model="search_form" class="demo-form-inline">
                            <el-form-item label="" style="width: 200px">
                                <el-input v-model="search_form.pay_sn" placeholder="支付号"></el-input>
                            </el-form-item>
                            <el-form-item label="" style="width: 200px">
                                <el-input v-model="search_form.order_sn" placeholder="订单号"></el-input>
                            </el-form-item>

                            <el-form-item label="">
                                <el-select v-model="search_form.status" clearable placeholder="状态" style="width:120px">
                                    <el-option label="未处理" value="0"></el-option>
                                    <el-option label="已处理" value="1"></el-option>
                                    <el-option label="退款失败" value="-1"></el-option>
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
                                <el-button type="primary" icon="el-icon-search" @click="search(1)">搜索
                                </el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </div>
            <div class="vue-main">
                <div class="vue-main-title" style="">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">
                        记录列表
                        <span style="text-align:left;font-size:14px;color:#999">
                            <span>总数：[[total]]</span>&nbsp;&nbsp;&nbsp;
                        </span>
                    </div>
                    <div class="vue-main-title-button">
                        <span style="text-align:left;font-size:14px;color:red">
                            <span>注意：状态为退款失败的，需要手动操作原路退款</span>&nbsp;&nbsp;&nbsp;
                        </span>

                    </div>
                </div>
                <div class="vue-main-title" style="margin-bottom:20px">
                    <el-table v-loading="loading" :data="list" style="width: 100%">
                        <el-table-column width="170px" label="记录时间" align="center">
                            <template slot-scope="scope">
                                <span style="margin-left: 10px">[[scope.row.record_at]]</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="支付号" align="center">
                            <template slot-scope="scope">
                                <span style="margin-left: 10px">
                                  [[scope.row.pay_sn]]
                                </span>
                            </template>
                        </el-table-column>

                        <el-table-column label="支付类型" align="center">
                            <template slot-scope="scope">
                                <span style="margin-left: 10px">[[scope.row.pay_type_name]]</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="异常提示" align="center">
                            <template slot-scope="scope">
                                <span style="margin-left: 10px">[[scope.row.error_msg]]</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="状态" align="center">
                            <template slot-scope="scope">
                                <span style="margin-left: 10px">[[scope.row.status_name]]</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="操作" align="center" width="200px">
                            <template slot-scope="scope">
                                <el-button @click="showOrders(scope.row.orders)" size="mini">
                                    查看订单
                                </el-button>
                                <el-button v-if="scope.row.status != 1 && scope.row.error_code == 1" @click="manualRefund(scope.row.id)" size="mini" type="warning">
                                    原路退款
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>


            <!-- 分页 -->
            <div class="vue-page" v-if="total>0">
                <el-row>
                    <el-col align="right">
                        <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                                       :page-size="per_size" :current-page="current_page" background
                        ></el-pagination>
                    </el-col>
                </el-row>
            </div>

            <!-- 支付记录 -->
            <el-dialog :visible.sync="orderListDialogVisible" width="800px"  title="订单记录">
                <div style="overflow:auto">
                    <el-table :data="orderList" style="width: 100%;min-height:300px;overflow:auto" id="order-list">
                        <el-table-column label="ID" prop="id" align="center"></el-table-column>
                        <el-table-column label="订单号">
                            <template slot-scope="scope">
                                <a :href="'{{ yzWebUrl('order.detail.vue-index', array('id' => '')) }}'+[[scope.row.id]]" target="_blank">
                                    [[scope.row.order_sn]]
                                </a>
                            </template>
                        </el-table-column>
                        <el-table-column prop="price" label="支付金额"></el-table-column>
                        <el-table-column prop="status_name" label="状态"></el-table-column>
                        <el-table-column prop="create_time" label="创建时间"></el-table-column>
                        <el-table-column prop="cancel_time" label="关闭时间">
                            <template slot-scope="scope">
                               <span v-show="scope.row.cancel_time.cancel_time != '1970-01-01 08:00:00'">[[scope.row.cancel_time]]</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="orderListDialogVisible = false">取 消</el-button>
                </span>
            </el-dialog>

        </div>
    </div>

    <script>
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {

                return {

                    search_form:{},
                    times:[],

                    orderListDialogVisible:false,
                    orderList:[],


                    list: [],
                    //页码数
                    current_page: 0,
                    //一页显示数据
                    per_size: 0,
                    //总数
                    total: 0,
                    //加载
                    loading: false,

                }
            },
            created() {
                let result = this.viewReturn();
                this.__initial(result);
                this.search(1);
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

                    // this.times = [
                    //     this.formatTime(new Date(new Date(new Date().toLocaleDateString()).getTime())),
                    //     this.formatTime(new Date(new Date(new Date().toLocaleDateString()).getTime() + (24 * 60 * 60 * 1000 - 1)))
                    // ];

                    //this.setList(data.list);

                },

                setList(response) {
                    this.list = response.data;
                    this.total = response.total;
                    this.current_page = response.current_page;
                    this.per_size = response.per_page;
                },

                search(page) {
                    let that = this;

                    if(this.times && this.times.length>0) {
                        this.$set(this.search_form,'start_time',this.times[0]);
                        this.$set(this.search_form,'end_time',this.times[1]);
                    } else {
                        this.$set(this.search_form,'start_time','');
                        this.$set(this.search_form,'end_time','');
                    }


                    that.loading = true;
                    that.$http.post("{!!yzWebFullUrl('order.order-pay.exception-list')!!}", {
                        page: page,
                        search:this.search_form,
                    }).then(response => {
                        // console.log(response);
                        if (response.data.result == 1) {
                            that.setList(response.data.data);
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.loading = false;
                    }), function (res) {
                        console.log(res);
                        that.loading = false;
                    };
                },

                showOrders(orders) {
                    this.orderListDialogVisible = true;
                    this.orderList = orders;
                },

                manualRefund(id) {
                    this.$confirm('确定手动操作原路退款吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        this.$http.post("{!!yzWebFullUrl('order.order-pay.pay-error-refund')!!}",{id:id}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: response.data.msg});
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                this.$emit('search');
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
                },


                getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },


                //时间转化
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

            },
        })
    </script>
@endsection