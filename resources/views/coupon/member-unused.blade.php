@extends('layouts.base')
@section('title', '优惠券使用记录')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
    <style>
        .edit-i{display:none;}
        .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
        .el-tabs__item,.is-top{font-size:16px}
        .el-tabs__active-bar { height: 3px;}
    </style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav" style="margin-bottom:15px">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="优惠券设置" name="1"></el-tab-pane>
                    <el-tab-pane label="优惠券列表" name="2"></el-tab-pane>
                    <el-tab-pane label="领取发放记录" name="3"></el-tab-pane>
                    <el-tab-pane label="分享领取记录" name="4"></el-tab-pane>
                    <el-tab-pane label="使用记录" name="5"></el-tab-pane>
                    <el-tab-pane label="领券中心幻灯片" name="6"></el-tab-pane>
                    <el-tab-pane label="会员优惠券" name="7"></el-tab-pane>
                </el-tabs>
            </div>
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">未使用优惠券</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input v-model="search_form.coupon_name" placeholder="优惠券名称"></el-input>
                        </el-form-item>

                        <el-form-item label="">
                            <el-button type="primary" @click="search(1)">搜索</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <div class="vue-main">
                <div class="vue-main-form">
                    <el-table :data="list" style="width: 100%">
                        <el-table-column label="ID" align="center" prop="belongs_to_coupon.id" width="80"></el-table-column>
                        <el-table-column label="优惠券名称" align="center" prop="belongs_to_coupon.name" width="280">
                            <template slot-scope="scope">
                                <div v-if="scope.row.belongs_to_coupon">
                                    <div>[[scope.row.belongs_to_coupon.name]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="未使用数量" align="center" prop="unused_total" width="330"></el-table-column>
                        <el-table-column label="操作" align="center" prop="" width="330">
                            <template slot-scope="scope">
                                <div v-if="scope.row.belongs_to_coupon" @click="del(scope.row)" style="line-height:32px;color:#ff1717;cursor: pointer;" class="el-icon-delete"></div>
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
                                       :page-size="per_page" :current-page="current_page" background
                        ></el-pagination>
                    </el-col>
                </el-row>
            </div>
        </div>
    </div>

    <script>
        let uid = {!! $uid?:'0' !!};
        let member_id = {!! $member_id?:'0' !!};
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return {
                    activeName:'7',
                    uid:uid,
                    member_id:member_id,
                    list:[],
                    search_form:{},
                    rules: {},
                    current_page:1,
                    total:1,
                    per_page:1,
                }
            },
            created() {
            },
            mounted() {
                this.getData(1);
            },
            methods: {
                getData(page) {
                    let json = {
                        page:page,
                        coupon_name:this.search_form.coupon_name,
                    };
                    if (this.member_id) {
                        json.member_id = this.member_id
                    }
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.member-coupon.get-unused') !!}',{page:page,search:json}).then(function(response) {
                        if (response.data.result) {
                            this.list = response.data.data.list.data;
                            this.current_page=response.data.data.list.current_page;
                            this.total=response.data.data.list.total;
                            this.per_page=response.data.data.list.per_page;
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                        loading.close();

                    }, function(response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },

                del(row) {
                    let json = {
                        id:row.belongs_to_coupon.id,
                        uid:row.uid,
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.member-coupon.deleteCoupon') !!}',json).then(function(response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            loading.close();
                            location.reload();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                        loading.close();

                    }, function(response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },

                search(val) {
                    this.getData(val);
                },

                handleClick(val) {
                    console.log(val.name)
                    if(val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('coupon.base-set.see') !!}`;
                    }
                    else if(val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('coupon.coupon.index') !!}`;
                    }
                    else if(val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('coupon.coupon.log-view') !!}`;
                    }
                    else if(val.name == 4) {
                        window.location.href = `{!! yzWebFullUrl('coupon.share-coupon.log') !!}`;
                    }
                    else if(val.name == 5) {
                        window.location.href = `{!! yzWebFullUrl('coupon.coupon-use.index') !!}`;
                    }
                    else if(val.name == 6) {
                        window.location.href = `{!! yzWebFullUrl('coupon.slide-show') !!}`;
                    }
                    else if(val.name == 7) {
                        window.location.href = `{!! yzWebFullUrl('coupon.member-coupon.index') !!}`;
                    }
                },

                // 字符转义
                escapeHTML(a) {
                    a = "" + a;
                    return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
                },

            },
        })
    </script>
@endsection