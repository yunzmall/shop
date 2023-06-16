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
        .btn{
            width: 50px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            height: auto;
            overflow: hidden;
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
                    <div class="vue-main-title-content">队列明细</div>
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
                            明细列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]]
                                奖励金额合计：[[amount]]
                            </span>
                        </div>
                    </div>

                    <el-table :data="record_list.data" style="width: 100%">
                        <el-table-column label="id" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="队列ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                    <el-button type="text" @click="queueNav(scope.row.queue_id)">
                                        [[scope.row.queue_id]]
                                    </el-button>
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
                        <el-table-column label="奖励金额" align="center" prop="">
                            <template slot-scope="scope">
                                    [[scope.row.amount]]
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
                        uid: '',
                        member: '',
                    },
                    activeName: 'queue_detailed',
                    record_list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
                    amount: 0,
                    search_time: [],
                    tab_list: [],
                }
            },
            created() {
                this.getData(1)
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                getData(page) {
                    let search = this.search_form
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('point.queue-log.index') !!}', {
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
                queueNav(queue_id) {
                    let url = '{!! yzWebFullUrl('point.queue.index') !!}';
                    window.open(url + "&queue_id=" + queue_id)
                },
                orderNav(order_id) {
                    let url = '{!! yzWebFullUrl('order.detail') !!}';
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
                }
            },
        })
    </script>
@endsection