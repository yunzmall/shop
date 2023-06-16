@extends('layouts.base')
@section('title', '基础设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .edit-i{display:none;}
        .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
        .el-tabs__item,.is-top{font-size:16px}
        .el-tabs__active-bar { height: 3px;}
        .description .el-form-item__label{line-height:24px}
    </style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav" style="margin-bottom:15px">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="基础设置" name="1"></el-tab-pane>
                    <el-tab-pane label="评价列表" name="2"></el-tab-pane>
                    <el-tab-pane label="审核列表" name="3"></el-tab-pane>
                </el-tabs>
            </div>
            <el-form ref="form" :model="form" label-width="15%">
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基础设置</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="评论审核" prop="is_comment_audit">
                            <el-switch v-model="form.is_comment_audit" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">开启后会员评论需要后台审核后才能在前端显示</div>
                        </el-form-item>

                        <el-form-item label="默认好评" prop="is_default_good_reputation">
                            <el-switch v-model="form.is_default_good_reputation" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">开启后订单确认收货15天后自动增加默认好评</div>
                        </el-form-item>
                        <el-form-item label="商品详情评论显示" prop="is_order_detail_comment_show">
                            <el-switch v-model="form.is_order_detail_comment_show" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">关闭后前端商品详情页不显示评论</div>
                        </el-form-item>
                        <el-form-item label="订单显示评论入口" prop="is_order_comment_entrance">
                            <el-switch v-model="form.is_order_comment_entrance" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">开启后已完成订单的列表页和订单详情页显示评论入口</div>
                        </el-form-item>

                        <el-form-item label="追评" prop="is_additional_comment">
                            <el-switch v-model="form.is_additional_comment" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">开启后评价过的订单/默认好评订单可进行追评</div>
                        </el-form-item>

                        <el-form-item label="评分维度" prop="is_score_latitude">
                            <el-switch v-model="form.is_score_latitude" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">开启后评价页面可对描述/包装,物流服务/配送,服务态度/质量进行评分</div>
                        </el-form-item>

                        <el-form-item label="评论置顶排序" prop="top_sort">
                            <el-radio v-model="form.top_sort" label="asc">正序</el-radio>
                            <el-radio v-model="form.top_sort" label="desc">倒序</el-radio>
                        </el-form-item>

                    </div>
                </div>
            </el-form>

            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    @include('public.admin.tinymceee')

    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    activeName:'1',
                    form:{
                        is_comment_audit:'0',
                        is_order_detail_comment_show:'1',
                        is_default_good_reputation:'0',
                        is_order_comment_entrance:'0',
                        is_additional_comment:'0',
                        is_score_latitude:'0',
                        top_sort:'asc',
                    },
                    loading: false,
                }
            },
            created() {
            },
            mounted() {
                this.getData();
            },
            methods: {
                handleClick(val) {
                    console.log(val.name)
                    if(val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('goods.comment.index') !!}`;
                    }
                    else if(val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('goods.comment.list') !!}`;
                    }
                    else if(val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('goods.comment.audit') !!}`;
                    }
                },
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.comment.saveSet') !!}').then(function (response) {
                            if (response.data.result){
                                let that = response.data.data.data;
                                if (that) {
                                    this.form.is_comment_audit = that.is_comment_audit ? that.is_comment_audit : '0';
                                    this.form.is_default_good_reputation = that.is_default_good_reputation ? that.is_default_good_reputation : '0';
                                    this.form.is_order_comment_entrance = that.is_order_comment_entrance ? that.is_order_comment_entrance : '0';
                                    this.form.is_additional_comment = that.is_additional_comment ? that.is_additional_comment : '0';
                                    this.form.is_score_latitude = that.is_score_latitude ? that.is_score_latitude : '0';
                                    this.form.top_sort = that.top_sort ? that.top_sort : 'asc';
                                    this.form.is_order_detail_comment_show=that.is_order_detail_comment_show?that.is_order_detail_comment_show:'1'
                                }
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    );
                },
                submitForm(formName) {
                    let json = {
                        is_comment_audit:this.form.is_comment_audit,
                        is_default_good_reputation:this.form.is_default_good_reputation,
                        is_order_detail_comment_show:this.form.is_order_detail_comment_show,
                        is_order_comment_entrance:this.form.is_order_comment_entrance,
                        is_additional_comment:this.form.is_additional_comment,
                        is_score_latitude:this.form.is_score_latitude,
                        top_sort:this.form.top_sort,
                    };

                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post('{!! yzWebFullUrl('goods.comment.saveSet') !!}',{form:json}).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                            },response => {
                                loading.close();
                            });
                        }
                        else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
            },
        })

    </script>
@endsection


