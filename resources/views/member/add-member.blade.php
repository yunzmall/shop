@extends('layouts.base')
@section('title', '添加会员详情')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
<style scoped>
    /* 标题 */
    .el-form-item__label {
        font-weight: 600;
    }

    /* 头部 */
    .head {
        position: relative;
        height: 800px;
    }

    /* 表单盒子 */
    .form_item_box {
        width: 1200px;
        margin: 50px auto
    }

    /* 头像 */
    .head_portrait {
        width: 90px;
        height: 90px;
        margin-top: 13px;
        border-radius: 5px;
    }

    /* 脚部 */
    /* .fixed {
        margin-left: -10px;
        width: 100%;
        padding: 0;
        padding-top: 10px;
        position: absolute;
        bottom: 0;
        box-shadow: 0px -1px 10px rgba(0, 0, 0, .1);
    } */

    /* 页脚盒子 */
    /* .fixed_box {
        width: 300px;
        height: 40px;
        margin: 0 auto;
    } */
    .inp-w{
        width:600px;
    }
</style>
<div class="all">
    <div id="app">
        <!-- 面包屑导航 -->
        <div style="margin-top:20px">
            <el-breadcrumb style="user-select:none;" separator-class="el-icon-arrow-right">
                <el-breadcrumb-item :to="{ path: '/' }"><span @click="hisGo(-1)">会员管理</span></el-breadcrumb-item>
                <el-breadcrumb-item :to="{ path: '/' }"><span @click="hisGo(-1)">全部会员</span></el-breadcrumb-item>
                <el-breadcrumb-item :to="{ path: '/' }">添加会员</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <!-- 头部 -->
        <div class="total-head head">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">添加会员</div>
            </div>
            <el-form label-width="100px" :model="ruleForm" style="cursor: pointer;" status-icon :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm">
                <!-- 表单列表 -->
                <div class="form_item_box">
                    <el-form-item label="粉丝">
                        <div>
                            <img  class="head_portrait" :src="avatar" alt="">
                        </div>
                    </el-form-item>
                    <el-form-item prop="bindPhone"  label="绑定手机">
                        <el-input clearable v-model=" ruleForm.bindPhone"  class="inp-w" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item prop="loginPad" label="登录密码">
                        <el-input clearable v-model="ruleForm.loginPad" type="password" class="inp-w" autocomplete="off">
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="affirmPad" label="确认密码">
                        <el-input clearable v-model="ruleForm.affirmPad" type="password" class="inp-w" autocomplete="off">
                        </el-input>
                    </el-form-item>
                </div>
            </el-form>
    </div>
    <div class="total-floo fixed">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button @click="sumbit('ruleForm')" type="primary">提交</el-button>
                        <el-button @click="returnEvent">返回</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</div>
<script>
    const vm = new Vue({
        el: "#app",
        name: "auth",
        delimiters: ["[[", "]]"],
        data() {
            //校验手机号
            let validatorPhone = function(rule, value, callback) {
                if (value === '') {
                    callback(new Error('手机号不能为空'))
                } else {
                    if (!value||isNaN(value)||value.length!=11) {
                        callback(new Error('手机号格式错误'))
                    }
                    callback();
                    return false;
                    let reg = /^[1][3,4,5,7,8][0-9]{9}$/;
                    if (!reg.test(value)) {
                        callback(new Error('手机号格式错误'))
                    };
                    callback();
                }
            };
            //校验密码
            let validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入密码'));
                } else {
                    if (this.ruleForm.affirmPad !== '') {
                        this.$refs.ruleForm.validateField('affirmPad');
                    }
                    callback();
                }
            };
            //校验确认密码
            let validatePass2 = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请再次输入密码'));
                } else if (value !== this.ruleForm.loginPad) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            }
            return {
                avatar: "",
                ruleForm: {
                    bindPhone: '',
                    loginPad: '',
                    affirmPad: ''
                },
                rules: {
                    bindPhone: [{
                        validator: validatorPhone,
                        trigger: 'blur'
                    }],
                    loginPad: [{
                        validator: validatePass,
                        trigger: 'blur'
                    }],
                    affirmPad: [{
                        validator: validatePass2,
                        trigger: 'blur'
                    }],
                }
            }
        },
        created() {
            this.postAddVip();
             //优化在不同设备固定定位挡住的现象设置父元素的内边距
            //  window.onload = function() {
            //         let all = document.querySelector(".all");
            //         let h = window.innerHeight * 0.05;
            //         all.style.paddingBottom = h + "px";
            //     }
        },
        methods: {
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            postAddVip(i) {
                this.$http.post("{!!yzWebFullUrl('member.member.add-member-data')!!}", {
                    item: i,
                    mobile: this.ruleForm.bindPhone,
                    password: this.ruleForm.loginPad,
                    confirm_password: this.ruleForm.affirmPad,
                }).then(res => {
                    console.log(res);
                    let {
                        data
                    } = res.body;
                    this.avatar = data.img
                    //弹框
                    // console.log(res.data.result);
                    // console.log(res.data.msg);
                    if (i === 1) {
                        if (res.data.result === 1) {
                            this.$message.success(res.data.msg);
                            let url = `{!! yzWebFullUrl('member.member.index') !!}`;
                            setTimeout(() => {
                                window.location.href = url;
                            }, 1000)
                        } else {
                            this.$message.error(res.data.msg + "请重新输入")
                        }
                    }
                })
            },
            sumbit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.postAddVip(1);
                    } else {
                        return false;
                    }
                });
            },
            returnEvent() {
                history.go(-1);
            }
        }
    })
</script>@endsection