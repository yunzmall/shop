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
                    <div class="vue-main-title-content">会员优惠券</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input v-model="search_form.member_id" placeholder="会员ID"></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input v-model="search_form.member" placeholder="会员昵称/姓名/手机号"></el-input>
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
                        <el-table-column label="会员ID" align="center" prop="uid" width="80"></el-table-column>
                        <el-table-column label="会员" align="center" width="150">
                            <template slot-scope="scope">
                                <div v-if="scope.row.member">
                                    <el-image v-if="scope.row.member&&scope.row.member.avatar" :src="scope.row.member.avatar" style="width:50px;height:50px"></el-image>
                                    <div v-if="scope.row.member&&scope.row.member.nickname">[[scope.row.member.nickname]]</div>
                                </div>
                                <div v-else>
                                    未更新
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="已发放总数" align="center" prop="">
                            <template slot-scope="scope">
                                <div>
                                    <div>[[scope.row.get_total]]</div>
                                    <el-button type="text" size="medium" @click="get_total(scope.row.uid)">查看</el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="已领取总数" align="center" prop="">
                            <template slot-scope="scope">
                                <div>
                                    <div>[[scope.row.get_from_total]]</div>
                                    <el-button type="text" size="medium" @click="get_from_total(scope.row.uid)">查看</el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="分享领取总数" align="center" prop="">
                            <template slot-scope="scope">
                                <div>
                                    <div>[[scope.row.receive_total]]</div>
                                    <el-button type="text" size="medium" @click="receive_total(scope.row.uid)">查看</el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="已使用总数" align="center" prop="">
                            <template slot-scope="scope">
                                <div>
                                    <div>[[scope.row.used_total]]</div>
                                    <el-button type="text" size="medium" @click="used_total(scope.row.uid)">查看</el-button>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="未使用总数" align="center" prop="">
                            <template slot-scope="scope">
                                <div>
                                    <div>[[scope.row.unused_total]]</div>
                                    <el-button type="text" size="medium" @click="unused_total(scope.row.uid)">查看</el-button>
                                </div>
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
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return {
                    activeName:'7',
                    uid:uid,
                    list:[],
                    search_form:{

                    },

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
                    let that = this;
                    let json = {
                        page:page,
                        member_id:this.search_form.member_id,
                        member:this.search_form.member,
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.member-coupon.get-data') !!}',{page:page,search:json}).then(function(response) {
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

                //已发放
                get_total(member_id) {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.log-view') !!}`+'&member_id='+member_id+'&getfrom=0';
                },
                //已领取
                get_from_total(member_id){
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.log-view') !!}`+'&member_id='+member_id+'&getfrom=1';
                },
                //分享领取
                receive_total(member_id){
                    window.location.href = `{!! yzWebFullUrl('coupon.share-coupon.log') !!}`+'&receive_uid='+member_id;
                },
                //已使用
                used_total(member_id){
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon-use.index') !!}`+'&member_id='+member_id;
                },
                //未使用
                unused_total(member_id) {
                    window.location.href = `{!! yzWebFullUrl('coupon.member-coupon.index') !!}`+'&member_id='+member_id+'&type=1';
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
