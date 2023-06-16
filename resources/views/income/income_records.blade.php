@extends('layouts.base')
@section('title', '收入明细')
@section('content')
    <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">明细管理</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    placeholder="会员ID"
                                    v-model="search_form.member_id"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    placeholder="姓名/昵称/手机号"
                                    v-model="search_form.realname"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.class" placeholder="业务类型">
                                <el-option
                                        v-for="(item,index) in income_type_comment"
                                        :key="item.index"
                                        :label="item.title"
                                        :value="item.class">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.status" placeholder="提现状态">
                                <el-option
                                        v-for="(item,index) in income_status"
                                        :key="item.index"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.pay_status" placeholder="提现状态">
                                <el-option
                                        v-for="(item,index) in pay_status"
                                        :key="item.index"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-date-picker
                                    clearable
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
                        <el-form-item label="">
                            <el-button type="primary" @click="exportList()">导出 EXCEL</el-button>
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
                               总数：[[page_list.total]] &nbsp;
                               金额总合计：[[amount]]
                            </span>
                        </div>
                    </div>
                    <el-table :data="page_list.data" style="width: 100%">
                        <el-table-column label="时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.created_at]]
                            </template>
                        </el-table-column>
                        <el-table-column label="会员ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.member_id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="粉丝" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.member.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-button type="text" @click="memberNav(scope.row.member_id)">
                                        [[scope.row.member.nickname]]
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column label="姓名/手机号" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.member.realname]]<br>
                                [[scope.row.member.mobile]]
                            </template>
                        </el-table-column>
                        <el-table-column label="收入金额" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.amount]]
                            </template>
                        </el-table-column>
                        <el-table-column label="业务类型" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.type_name]]
                            </template>
                        </el-table-column>
                        <el-table-column label="提现状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.status_name]]
                            </template>
                        </el-table-column>
                        <el-table-column label="打款状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.pay_status_name]]
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
                        status: '',
                        member_id: '',
                        realname: '',
                        class: '',
                        pay_status: '',
                        time: {
                            start: 0,
                            end: 0
                        }

                    },
                    page_list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
                    amount: 0,
                    income_status: [
                        {
                            value: 0,
                            label: '未提现'
                        },
                        {
                            value: 1,
                            label: '已提现'
                        },
                    ],
                    pay_status: [
                        {
                            value: -1,
                            label: '无效'
                        },
                        {
                            value: 0,
                            label: '未审核'
                        },
                        {
                            value: 1,
                            label: '未打款'
                        },
                        {
                            value: 2,
                            label: '已打款'
                        },
                        {
                            value: 3,
                            label: '已驳回'
                        },
                    ],
                    income_type_comment: [],
                    search_time: []
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
                    if (this.search_time) {
                        search.time.start = this.search_time[0] ? this.search_time[0] : ''
                        search.time.end = this.search_time[1] ? this.search_time[1] : ''
                    } else {
                        search.time.start = ''
                        search.time.end = ''
                    }

                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('income.income-records.index') !!}', {
                        search: search,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            this.page_list = response.data.data.pageList
                            this.search_form = response.data.data.search
                            this.total = response.data.data.pageList.total
                            this.per_page = response.data.data.pageList.per_page
                            this.current_page = response.data.data.pageList.current_page
                            this.income_type_comment = response.data.data.income_type_comment
                            this.amount = response.data.data.amount
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
                exportList() {
                    let that = this;
                    console.log(that.search_form);

                    let json = {
                        realname: that.search_form.realname,
                        pay_status: that.search_form.pay_status,
                        status: that.search_form.status,
                        member_id: that.search_form.member_id,
                        class:that.search_form.class,
                        time: [this.search_time[0] ? this.search_time[0] : '', this.search_time[1] ? this.search_time[1] : '']
                    };

                    let url = '{!! yzWebFullUrl('income.income-records.export') !!}';

                    for (let i in json) {
                        if (json[i] || json[i] === 0) {
                            if (i === 'time') {
                                if(json[i][0] && json[i][1]) {
                                    url += "&search[" + i + "]=" + json[i]
                                }
                            } else {
                                url += "&search[" + i + "]=" + json[i]
                            }
                        }
                    }
                    window.location.href = url;
                },
            },
        })
    </script>
@endsection