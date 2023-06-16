@extends('layouts.base')

@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }
        .el-dropdown-menu__item{
            padding: 0;
        }
        .el-dropdown-menu{
            padding: 0;
        }
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            @include('layouts.vueTabs')
        </div>
        <div class="block">
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">积分队列</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="会员ID"
                                    v-model="search_form.uid"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="会员姓名/昵称/手机号"
                                    v-model="search_form.member"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="队列ID"
                                    v-model="search_form.queue_id"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-date-picker
                                    value-format="timestamp"
                                    v-model="search_time"
                                    type="datetimerange"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label="">
                            <el-button type="primary" @click="search(1)">搜索</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </div>
        <div class="block">
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">
                            记录列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]]
                                赠送积分总数：[[amount]]
                            </span>
                        </div>
                    </div>

                    <el-table :data="record_list.data" style="width: 100%">
                        <el-table-column label="id" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span>[[scope.row.created_at]]</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="会员ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span>[[scope.row.uid]]</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="粉丝" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.member.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-button type="text" @click="memberNav(scope.row.uid)">
                                        [[scope.row.member.nickname]]
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="订单编号" align="center" prop="">
                            <template slot-scope="scope">
                                <el-link style="width: 100px;color: #29ba9c" type="link" @click="orderNav(scope.row.order.id)">
                                    [[scope.row.order.order_sn]]
                                </el-link>
                            </template>
                        </el-table-column>
                        <el-table-column label="赠送总数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.point_total]]
                            </template>
                        </el-table-column>
                        <el-table-column label="已赠送数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.finish_point]]
                            </template>
                        </el-table-column>
                        <el-table-column label="剩余数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.surplus_point]]
                            </template>
                        </el-table-column>
                        <el-table-column label="单次赠送数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.once_unit]]
                            </template>
                        </el-table-column>
                        <el-table-column label="最后一次赠送数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.last_point]]
                            </template>
                        </el-table-column>
                        <el-table-column label="状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.status_name]]
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>

        <!-- 分页 -->
        <div class="vue-page">
            <el-row>
                <el-col align="right">
                    <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                                   :page-size="per_page" :current-page="current_page" background
                    ></el-pagination>
                </el-col>
            </el-row>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: '#app',
            // 防止后端冲突,修改ma语法符号
            delimiters: ['[[', ']]'],
            data() {
                return {
                    search_form: {
                        queue_id:'',
                        uid:'',
                        member: '',
                        time: {
                            start: 0,
                            end: 0,
                        }
                    },
                    activeName: 'point_queue',
                    record_list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
                    amount: 0,
                    search_time: [],
                    tab_list:[],
                }
            },
            created() {
                this.search_form.queue_id = this.getParam('queue_id')
                this.getData(1)
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },
                getData(page) {
                    let search = this.search_form
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    if (!this.search_time) {
                        search.time.start = 0
                        search.time.end = 0
                    }else if(typeof this.search_time[0] != 'undefined' && typeof this.search_time[1] != 'undefined') {
                        search.time.start = this.search_time[0]/1000
                        search.time.end = this.search_time[1]/1000
                    }
                    this.$http.post('{!! yzWebFullUrl('point.queue.index') !!}', {
                        search: search,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            this.record_list = response.data.data.list
                            this.total = response.data.data.list.total
                            this.per_page = response.data.data.list.per_page
                            this.current_page = response.data.data.list.current_page
                            this.amount = response.data.data.amount
                            this.tab_list = response.data.data.tab_list
                            this.search_form = response.data.data.search
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                search(page) {
                    this.getData(page)
                },
                memberNav(uid) {
                    let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                    window.open(url + "&id=" + uid)
                },
                orderNav(order_id) {
                    let url = '{!! yzWebFullUrl('order.detail.vue-index') !!}';
                    window.open(url + "&id=" + order_id)
                },
                handleClick() {
                    window.location.href = this.getUrl()
                },
                getUrl() {
                    let url = ''
                    switch (this.activeName) {
                        case 'member_point' :
                            url = '{!! yzWebFullUrl('finance.point-member.index') !!}';
                            break;
                        case 'basic_set' :
                            url = '{!! yzWebFullUrl('finance.point-set.index') !!}';
                            break;
                        case 'recharge_record' :
                            url = '{!! yzWebFullUrl('point.recharge-records.index') !!}';
                            break;
                        case 'point_detailed' :
                            url = '{!! yzWebFullUrl('point.records.index') !!}';
                            break;
                        case 'point_queue' :
                            url = '{!! yzWebFullUrl('point.queue.index') !!}';
                            break;
                        case 'queue_detailed' :
                            url = '{!! yzWebFullUrl('point.queue-log.index') !!}';
                            break;
                        case 'superior_queue' :
                            url = '{!! yzWebFullUrl('point.queue-log.parentIndex') !!}';
                            break;
                    }
                    return url
                },

            },
        })
    </script>

@endsection