@extends('layouts.base')

@section('content')
@section('title', '商城收益')
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
    .el-icon-edit {
        font-size:30px;
        padding: 0;
        color: #666;
    }
    ..vue-main-form {
        margin-top: 0;
    }
</style>
<div id="app" v-cloak class="main">
    <div class="block">
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">收益广告</div>
                <div class="vue-main-title-button">
                    <el-button type="success" @click="addAdv"><b>+</b>添加广告</el-button>
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input
                                style="width: 270px"
                                placeholder="广告标题"
                                v-model="search_form.name"
                                clearable>
                        </el-input>
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
                    </div>
                </div>
                <el-table :data="list.data" style="width: 100%">
                    <el-table-column label="ID" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.id]]
                        </template>
                    </el-table-column>
                    <el-table-column label="标题" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.name]]
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            <el-switch
                                    v-model="scope.row.status"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    :active-value="1"
                                    :inactive-value="0"
                                    @change="setStatus(scope.row.id, scope.row.status)"
                            >
                            </el-switch>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" align="center" prop="" width="auto">
                        <template slot-scope="scope" >
                            <el-link @click="editAdv(scope.row.id)" ><i class="el-icon-edit" style="font-size: 30px!important;"></i></el-link>
                            <el-link @click="delAdv(scope.row.id)" style="margin-left:10px;font-size: 30px!important;"><i class="el-icon-delete"></i></el-link>
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
                    name: '',
                },
                list: [],
                total: 0,
                per_page: 0,
                current_page: 0,
                pageSize: 0,
                amount: 0,
                search_time: [],

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
                this.$http.post('{!! yzWebFullUrl('finance.advertisement.index') !!}', {
                    search: search,
                    page: page
                }).then(function (response) {
                    if (response.data.result) {
                        this.list = response.data.data.list
                        console.log(this.list)
                        this.total = response.data.data.list.total
                        this.per_page = response.data.data.list.per_page
                        this.current_page = response.data.data.list.current_page
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
            editAdv(id) {
                let url = '{!!yzWebUrl('finance.advertisement.edit')!!}'
                window.open(url + '&id=' + id)
            },
            delAdv(id) {
                    this.$confirm('确认删除？').then(_ => {
                            let loading = this.$loading({
                                target: document.querySelector(".content"),
                                background: 'rgba(0, 0, 0, 0)'
                            });
                            this.$http.get('{!! yzWebFullUrl('finance.advertisement.del') !!}' + "&id=" + id)
                                .then(function (response) {
                                    if (response.data.result) {
                                        this.$message({
                                            message: response.data.msg,
                                            type: 'success'
                                        })
                                        this.getData(this.current_page)
                                        loading.close();
                                    } else {
                                        this.$message({
                                            message: response.data.msg,
                                            type: 'error'
                                        })
                                        loading.close();
                                    }
                                })
                        })
                        .catch(_ => {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            })
                        });
            },
            setStatus(id, status) {
                let loading = this.$loading({
                    target: document.querySelector(".content"),
                    background: 'rgba(0, 0, 0, 0)'
                });
                this.$http.post('{!! yzWebFullUrl('finance.advertisement.setStatus') !!}',{
                    id:id,
                    status:status
                })
                    .then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            })
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            })
                            loading.close();
                        }
                    })
            },
            addAdv() {
                let url = '{!!yzWebUrl('finance.advertisement.add')!!}'
                window.open(url)
            }
        },
    })
</script>
@endsection