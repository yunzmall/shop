@extends('layouts.base')
@section('title', '充值记录')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">
                            充值记录
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               记录：[[total]]
                            </span>
                        </div>
                    </div>
                    <el-table :data="list.data" style="width: 100%">
                        <el-table-column label="主键ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.created_at]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值类型" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.sourceName]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.total]]
                            </template>
                        </el-table-column>
                        <el-table-column label="失败数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.failure]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值总额" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.amount]]
                            </template>
                        </el-table-column>
                        <el-table-column label="成功金额" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.success]]
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <el-button @click="navDetail(scope.row.id)">详细记录</el-button>
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
                    list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
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
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('excelRecharge.records.index') !!}', {
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            this.list = response.data.data.pageList
                            this.total = response.data.data.pageList.total
                            this.per_page = response.data.data.pageList.per_page
                            this.current_page = response.data.data.pageList.current_page
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
                navDetail(id) {
                    let url = '{!! yzWebFullUrl('excelRecharge.detail.index') !!}';
                    window.open(url + "&recharge_id=" + id)
                }
            },
        })
    </script>

@endsection
