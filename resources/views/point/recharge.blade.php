@extends('layouts.base')
@section('title', '会员充值')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .el-dropdown-menu__item {
            padding: 0;
        }

        .el-dropdown-menu {
            padding: 0;
        }

        .el-form-item .el-form-item__label {
            width: 300px;
            text-align: right;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            <el-form ref="form" :model="form">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                [[rechargeMenu.title]]
                            </div>
                        </div>
                        <el-form-item label="粉丝">
                            <div style="display: flex;flex-direction: column;justify-content: center">
                                <div style="width: 100px; height: 100px">
                                    <el-image
                                            style="width: 100px; height: 100px"
                                            :src="memberInfo.avatar">
                                    </el-image>
                                </div>
                                <div style="width: 100px;text-align: center">
                                    <span class="demonstration">[[memberInfo.nickname]]</span>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="会员信息">
                            <div style="display: flex;flex-direction: column;justify-content: center">
                                <div>
                                    <span class="demonstration">姓名：[[memberInfo.realname ? memberInfo.realname : memberInfo.nickname]]</span>
                                </div>
                                <div>
                                    <span class="demonstration">手机号: [[memberInfo.mobile]]</span>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="当前积分">
                            <span class="demonstration">[[memberInfo.credit1]]</span>
                        </el-form-item>
                        <el-form-item label="充值积分">
                            <el-input style="width: 450px" v-model="form.point" placeholder="请输入内容"></el-input>
                        </el-form-item>
                        <el-form-item label="备注信息">
                            <el-input style="width: 600px" type="textarea" rows="8" v-model="form.remark" placeholder=""></el-input>
                        </el-form-item>
                    </div>
                    <div class="on-submit-div" style="margin-left: 300px">
                        <el-button type="primary" @click="onSubmit">提交</el-button>
                    </div>
                </div>
            </el-form>
        </div>
    </div>
    <script>
        let rechargeMenu = {!! json_encode($rechargeMenu) !!}
            let
        memberInfo = {!! json_encode($memberInfo) !!}
            var
        vm = new Vue({
            el: '#app',
            // 防止后端冲突,修改ma语法符号
            delimiters: ['[[', ']]'],

            data() {
                return {
                    rechargeMenu: rechargeMenu,
                    memberInfo: memberInfo,
                    form: {
                        point:'',
                        remark:''
                    }
                }
            },
            created() {
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                onSubmit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    let form = this.form
                    form.id = this.memberInfo.uid
                    this.$http.post('{!! yzWebFullUrl('point.recharge.index') !!}', form).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            location.reload()
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
                }
            },
        })
    </script>
@endsection
