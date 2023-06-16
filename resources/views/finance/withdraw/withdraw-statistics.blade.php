@extends('layouts.base')
@section('content')

<link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
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
                <div class="vue-main-title-content">提现统计</div>
                <div class="vue-main-title-button">
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
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
                        <el-button type="primary" @click="search()">搜索</el-button>
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
                        统计列表
                        <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               金额总合计：[[amount]]
                            </span>
                    </div>
                </div>
                <el-table :data="page_list" style="width: 100%">
                    <el-table-column label="日期" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.time]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现到余额" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.balance]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现到微信" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.wechat]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现到支付宝" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.alipay]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现到手动提现" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.manual]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现到银行卡-HJ" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.converge_pay]]
                        </template>
                    </el-table-column>
                    <el-table-column v-if="high_light_open==1" label="提现到微信-高灯" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.high_light_wechat]]
                        </template>
                    </el-table-column>
                    <el-table-column v-if="high_light_open==1" label="提现到支付宝-高灯" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.high_light_alipay]]
                        </template>
                    </el-table-column>
                    <el-table-column v-if="high_light_open==1" label="提现到银行卡-高灯" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.high_light_bank]]
                        </template>
                    </el-table-column>
                    <el-table-column label="总计提现金额" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.amount]]
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>
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
                    time: {
                        start: 0,
                        end: 0
                    }

                },
                page_list: [],
                total: 0,
                per_page: 0,
                current_page: 0,
                pageSize: 0,
                amount: 0,
                income_type_comment: [],
                search_time: [],
                high_light_open: 0
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
            getData() {
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
                this.$http.post('{!! yzWebFullUrl('finance.withdraw-statistics.index') !!}', {
                    search: search,
                }).then(function (response) {
                    if (response.data.result) {
                        this.page_list = response.data.data.data
                        this.high_light_open = response.data.data.high_light_open
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
        },
    })
</script>
@endsection
