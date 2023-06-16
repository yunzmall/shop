@extends('layouts.base')
@section('title', '充值详情')
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
                            详细记录
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
                        <el-table-column label="会员ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.member_id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="会员" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.member.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-button type="text" @click="memberNav(scope.row.member.uid)">
                                        [[scope.row.member.nickname]]
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="充值数量" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.amount]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">

                                    <label v-if="scope.row.status" class="label label-success">成功</label>
                                    <label v-else class="label label-danger">失败</label>
                            </template>
                        </el-table-column>
                        <el-table-column label="备注" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.remark]]
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
                this.id = this.getParam('recharge_id')
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
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('excelRecharge.detail.index') !!}', {
                        page: page,
                        recharge_id:this.id
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
                },
                memberNav(uid) {
                    let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                    window.open(url + "&id=" + uid)
                }
            },
        })
    </script>
@endsection
