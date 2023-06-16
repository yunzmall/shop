@extends('layouts.base')
@section('title', '转让记录')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .el-form-item__label {
            width: 300px;
            text-align: right;
        }

        .alert.alert-warning {
            border: 1px;
            color: red;
            border-radius: 3px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4);
            background-color: #fcf4f4;
        }

        .note-span {
            width: 290px;
            text-align: right;
            margin-right: 10px;
        }

        .el-radio-group {
            display: inline-block;
            line-height: 1;
            vertical-align: inherit;
        }

        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .con {
            padding-bottom: 40px;
            position: relative;
            border-radius: 8px;
            min-height: 100vh;
        }

        .con .setting .block {
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
        }

        .con .setting .block .title {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .con .confirm-btn {
            width: calc(100% - 266px);
            position: fixed;
            bottom: 0;
            right: 0;
            margin-right: 10px;
            line-height: 63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px rgba(51, 51, 51, 0.3);
            background-color: #fff;
            text-align: center;
        }

        b {
            font-size: 14px;
        }

        .el-checkbox__inner {
            border: solid 1px #56be69 !important;
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
                    <div class="vue-main-title-content">转让记录</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    placeholder="转让者ID/昵称/手机号"
                                    v-model="search_form.transfer"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    placeholder="被转让者ID/昵称/手机号"
                                    v-model="search_form.recipient"
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
                            转让记录列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]] &nbsp;
                            </span>
                        </div>
                    </div>

                    <el-table :data="tansfer_list.data" style="width: 100%">
                        <el-table-column label="编号" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="转让人" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.transfer_info.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-button type="text" @click="memberNav(scope.row.transfer_info.uid)">
                                        [[scope.row.transfer_info.nickname]]
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="被转让人" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.recipient_info.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-button type="text" @click="memberNav(scope.row.recipient_info.uid)">
                                        [[scope.row.recipient_info.nickname]]
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column label="转让金额" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.money]]
                            </template>
                        </el-table-column>
                        <el-table-column label="转让时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.created_at]]
                            </template>
                        </el-table-column>
                        <el-table-column label="状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span v-if="scope.row.status==1" class='label label-success'>转让成功</span>
                                <span v-else class='label label-default'>转让失败</span>
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
                        transfer: '',
                        recipient: '',
                    },
                    tansfer_list: {},
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
                    let search = this.search_form
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('finance.balance.transfer-record') !!}', {
                        search: search,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            this.tansfer_list = response.data.data.tansferList
                            this.search_form = response.data.data.search
                            this.total = response.data.data.tansferList.total
                            this.per_page = response.data.data.tansferList.per_page
                            this.current_page = response.data.data.tansferList.current_page
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
                }
            },
        })
    </script>
@endsection