@extends('layouts.base')
@section('title', '银行卡管理')
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
        margin: 13px 15px 0 0px;
        border-radius: 5px;
        vertical-align: text-bottom;
    }

    .inp-w {
        width: 600px
    }

    [v-cloak] {
        display: none;
    }
</style>
<div class="all">
    <div id="app" v-cloak>
        <!-- 头部 -->
        <div class="total-head head">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">银行卡管理</div>
            </div>
            <el-form label-width="100px">
                <!-- 表单列表 -->
                <div class="form_item_box">
                    <el-form-item label="粉丝">
                        <div>
                            <img class="head_portrait" :src="avatar" alt="">
                            <span style="font-weight:600;">[[nickname]]</span>
                        </div>
                    </el-form-item>
                    <el-form-item label="真实姓名">
                        <el-input v-focus v-model="member_name" placeholder="请输入姓名" class="inp-w"></el-input>
                    </el-form-item>
                    <el-form-item label="开户行">
                        <el-input v-model="bank_name" placeholder="请输入开户银行" class="inp-w"></el-input>
                    </el-form-item>
                    <el-form-item label="开户行省份">
                        <el-input v-model="bank_province" placeholder="请输入开户行省份" class="inp-w"></el-input>
                    </el-form-item>
                    <el-form-item label="开户城市">
                        <el-input v-model="bank_city" placeholder="请输入开户城市" class="inp-w"></el-input>
                    </el-form-item>
                    <el-form-item label="开户支行">
                        <el-input v-model="bank_branch" placeholder="请输入开户支行" class="inp-w"></el-input>
                    </el-form-item>
                    <el-form-item label="银行卡号">
                        <el-input v-model="bank_card" placeholder="请输入银行卡号" class="inp-w"></el-input>
                    </el-form-item>
                </div>
            </el-form>
        </div>
        <div class="fixed total-floo ">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button type="primary" @click="updatedEvent">更新</el-button>
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
        name: "edit",
        delimiters: ["[[", "]]"],
        data() {
            return {
                avatar: "",
                nickname: "",
                member_name: "",
                bank_name: "",
                bank_province: "",
                bank_city: "",
                bank_branch: "",
                bank_card: "",
                member_id: "",
                isCount: 0,
                isRes:""
            }
        },
        created() {
            let i = window.location.href.indexOf('id=');
            if (i !== -1) {
                let id = Number(window.location.href.slice(i + 3))
                this.member_id = id;
            }
            //银行卡信息
            this.postCardDateInfo()
        },
        // 自定义组件
        directives: {
            // 注册一个局部的自定义指令 v-focus
            focus: {
                // 指令的定义
                inserted: function(el) {
                    // 聚焦元素
                    el.querySelector('input').focus()
                }
            }
        },
        methods: {
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            postCardDateInfo() {
                this.$http.post("{!!yzWebFullUrl('member.bank-card.edit')!!}", {
                    member_id: this.member_id,
                    bank: this.isCount >= 1 ? {
                        member_name: this.member_name,
                        bank_name: this.bank_name,
                        bank_province: this.bank_province,
                        bank_city: this.bank_city,
                        bank_branch: this.bank_branch,
                        bank_card: this.bank_card
                    } : ""
                }).then(res => {
                    console.log(res);
                    this.isRes=res.data;
                    // this.updatedEvent(res);
                        const {
                            member
                        } = res.body.data;
                        // console.log(member, 44444);
                        //粉丝图片
                        this.avatar = member.avatar;
                        //粉丝昵称
                        this.nickname = member.nickname;
                        //真实姓名
                        this.member_name = member.bank_card.member_name;
                        //开户行
                        this.bank_name = member.bank_card.bank_name;
                        //开户行省份
                        this.bank_province = member.bank_card.bank_province;
                        //开户城市
                        this.bank_city = member.bank_card.bank_city;
                        //开户支行
                        this.bank_branch = member.bank_card.bank_branch;
                        //银行卡号
                        this.bank_card = member.bank_card.bank_card;
                })
            },
            //更新
            updatedEvent() {
                //提交信息
                this.isCount++;
                this.postCardDateInfo();
                if(this.isRes.result){
                     this.$message.success("银行卡信息更新"+this.isRes.msg);
                     history.go(0);
                }else{
                    this.$message.error("银行卡信息更新"+this.isRes.msg)
                }
            },
            //返回
            returnEvent() {
                history.back();
            }
        },
    })
</script>
@endsection