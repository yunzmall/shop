@extends('layouts.base')
@section('title', '满额优惠设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>

    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav" style="margin-bottom:15px">
                <el-tabs v-model="active_name" @tab-click="handleClick">
                    <el-tab-pane label="满额优惠设置" name="1"></el-tab-pane>
                    <el-tab-pane label="包邮分类设置" name="2"></el-tab-pane>
                    <el-tab-pane label="满件优惠设置" name="3"></el-tab-pane>
                </el-tabs>
            </div>

            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">包邮分类设置</div>
                    <el-button type="primary" class="createCategory" @click="createMarketingQr">新增分类</el-button>
                </div>

                <div style="margin-bottom: 20px;">
                    <el-input class="createMarketingQr" v-model="name" placeholder="分类名称" clearable
                              style="width:20%;"></el-input>
                    <el-button type="primary" @click="searchCategory" style="margin: 20px 0px 0px 20px;">搜索</el-button>
                </div>

                <div class="vue-main-content" style="margin-bottom: 20px">
                    <el-table :data="records.data" align="center" style="width: 100%">
                        <el-table-column prop="id" label="ID" align="center"></el-table-column>
                        <el-table-column prop="sort" label="排序" align="center"></el-table-column>
                        <el-table-column prop="name" label="分类名称" align="center"></el-table-column>
                        <el-table-column prop="is_display" label="显示" align="center">
                            <template slot-scope="scope">
                                <el-switch v-model="scope.row.is_display"
                                           :active-value="1"
                                           :inactive-value="0"
                                           @change="setDisplay(scope.row.id, scope.row.is_display)">
                                </el-switch>
                            </template>
                        </el-table-column>
                        <el-table-column fixed="right" label="操作" align="center">
                            <template slot-scope="scope">
                                <el-button @click="categoryEdit(scope.row.id)" type="text" size="small"><i
                                            class="el-icon-edit current-icon"></i></el-button>
                                <el-button @click="categoryDel(scope.row.id)" type="text" size="small"><i
                                            class="el-icon-delete current-icon"></i></el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>

                <el-pagination style="margin-right: 50px"
                               background align="right"
                               layout="prev, pager, next"
                               :total="records.total"
                               :page-size="records.per_page"
                               :page-count="records.last_page"
                               :current-page="records.current_page"
                               @prev-click="handlePrevPage"
                               @next-click="handleNextPage"
                               @current-change="handleCurrentPage">
                </el-pagination>

            </div>
        </div>
    </div>

    <script>
        let app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    name: '',
                    records: [],
                    visible: false,
                    active_name: '2'
                }
            },
            mounted() {
                this.searchCategory()
            },
            methods: {
                createMarketingQr() {
                    // 跳转->生成营销码页面
                    window.location.href = `{!! yzWebFullUrl('enoughReduce.postage-included-category.generate') !!}`
                },
                searchCategory() {
                    this.$http.post(`{!! yzWebFullUrl('enoughReduce.postage-included-category.records') !!}`, {name: this.name}).then(function (response) {
                        if (response.data.result) {
                            this.records = response.data.data;
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    });
                },
                handleCurrentPage(page) {
                    this.$http.post(`{!! yzWebFullUrl('enoughReduce.postage-included-category.records') !!}`, {page: page}).then(function (response) {
                        if (response.data.result) {
                            this.records = response.data.data;
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    });
                },
                handlePrevPage(page) {
                    // 必要方法,无需执行任何代码
                },
                handleNextPage(page) {
                    // 必要方法,无需执行任何代码
                },
                categoryEdit(id) {
                    window.location.href = `{!! yzWebFullUrl('enoughReduce.postage-included-category.generate') !!}` + `&id=` + id
                },
                categoryDel(id) {
                    this.$confirm('删除该分类?', '确认信息', {
                        distinguishCancelAndClose: true,
                        confirmButtonText: '确认',
                        cancelButtonText: '取消'
                    }).then(() => {
                        this.$http.post(`{!! yzWebFullUrl('enoughReduce.postage-included-category.destroy') !!}`, {id: id}).then(function (response) {
                            if (response.data.result) {
                                location. reload()
                                this.$message({
                                    message: response.data.msg,
                                    type: 'success'
                                });
                            } else {
                                this.$message({
                                    message: response.data.msg,
                                    type: 'error'
                                });
                            }
                        }, function (response) {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        });
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消'
                        })
                    });
                },
                setDisplay(id, display) {
                    let params = {
                        id: id,
                        is_display: display
                    }
                    this.$http.post(`{!! yzWebFullUrl('enoughReduce.postage-included-category.update') !!}`, params).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message.error({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    });
                },
                handleClick(val) {
                    if (val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.index.index') !!}`;
                    } else if (val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.postage-included-category.index') !!}`;
                    } else if (val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.full-piece.index') !!}`;
                    }
                }
            }
        })
    </script>
    <style>
        .createCategory {
            background-color: #e5f6f1;
            color: #48c1a7;
            margin-right: 20px;
        }

        .createMarketingQr {
            background-color: #e5f6f1;
            color: #48c1a7;
        }

        .current-icon {
            color: grey;
        }
    </style>
@endsection