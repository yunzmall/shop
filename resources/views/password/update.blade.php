@extends('layouts.base')
@section('title', '密码管理')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<div class="all">
    <div id="app">
        <div class="vue-main">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">修改支付密码</div>
            </div>
            <el-form label-width="15%">
                <el-form-item label="会员">
                    <el-row>
                        <el-col :span="1">
                            <el-avatar :src="member.avatar_image" size="large"></el-avatar>
                        </el-col>
                        <el-col :span="23">
                            [[ member.nickname ]]</el-col>
                    </el-row>

                </el-form-item>
                <el-form-item label="密码">
                    {{--<el-input type="password" v-model="newPassword" placeholder="密码必须是6位纯数字" minlength="6" show-password></el-input>--}}
                    <el-input type="password" v-model="newPassword" placeholder="请输入密码" show-password></el-input>
                </el-form-item>
                <el-form-item label="确认密码">
                    {{--<el-input type="password" v-model="newPasswordConfirm" placeholder="密码必须是6位纯数字" minlength="6" show-password></el-input>--}}
                    <el-input type="password" v-model="newPasswordConfirm" placeholder="请输入密码" show-password></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="confirmChangePayPassword" :loading="saving">确认</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</div>

<script>
    new Vue({
        el: "#app",
        delimiters: ["[[", "]]"],
        data() {
            const member = JSON.parse(`{!! json_encode($member) !!}`);
            return {
                member,
                newPassword: null,
                newPasswordConfirm: null,
                saving: false
            }
        },
        methods: {
            confirmChangePayPassword() {
                if (!this.newPassword || !this.newPasswordConfirm) {
                    this.$message.error("请输入密码");
                    return;
                }
                if (this.newPassword != this.newPasswordConfirm) {
                    this.$message.error("确认密码不一致");
                    return;
                }

                this.saving = true;
                this.$http.post("{!! yzWebFullUrl('password.update.index') !!}", {
                    member_id: this.member.uid,
                    password: this.newPassword,
                    confirmed: this.newPasswordConfirm
                }).then(({
                    data: {
                        result,
                        msg
                    }
                }) => {
                    if (result == 0) {
                        this.$message.error(msg);
                        this.saving = false;
                        return;
                    }
                    this.$message.success("修改成功");
                    this.saving = false;
                }).catch(({
                    data: {
                        result,
                        msg
                    }
                }) => {
                    this.$message.success(msg);
                    this.saving = false;
                })
            }
        }
    })
</script>

@endsection