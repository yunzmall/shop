@extends('layouts.base')
@section('title', '发放优惠券')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .vue-main{padding:0;}
</style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">优惠券管理</a> > 发放优惠券
            </div>
            <div class="vue-main">
                <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                    <div style="border-radius: 5px;padding: 10px;">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">发放优惠券</div>
                        </div>
                        <div class="vue-main-form">
                        
                            <el-form-item label="发送张数" prop="send_total">
                                <el-input v-model.number="form.send_total" style="width:70%;" placeholder="请输入发送张数"></el-input>
                                <div class="tip" style="color:red">该处发放张数为发放给单个用户的张数</div>
                            </el-form-item>
                        </div>
                    </div>
                    <div style="background:#f5f5f5;height:15px"></div>
                    <div style="border-radius: 5px;padding: 10px;">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">发放优惠券</div>
                        </div>
                        <div class="vue-main-form">
                            <el-form-item label="发送类型" prop="sendtype">
                                <el-radio v-model.number="form.sendtype" :label="1">按会员ID发送</el-radio>
                                <el-radio v-model.number="form.sendtype" :label="2">按用户等级发送 </el-radio>
                                <el-radio v-model.number="form.sendtype" :label="3">按用户分组发送 </el-radio>
                                <el-radio v-model.number="form.sendtype" :label="4">发送给全部用户 </el-radio>
                            </el-form-item>
                            <el-form-item label="会员ID" prop="send_memberid" v-if="form.sendtype == 1">
                                <el-input type="textarea" rows="5" v-model="form.send_memberid" placeholder="请用“半角逗号”隔开会员ID, 比如 1,2,3,注意不能有空格" style="width:70%;"></el-input>
                                <div class="tip">请用“半角逗号”隔开会员ID, 比如 1,2,3,注意不能有空格</div>
                            </el-form-item>
                            <el-form-item label="选择会员等级" prop="send_level" v-if="form.sendtype == 2">
                                <el-select v-model="form.send_level" style="width:70%" placeholder="请选择会员等级" >
                                    <el-option v-for="item in member_list" :key="item.id" :label="item.level_name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item label="选择会员分组" prop="send_group" v-if="form.sendtype == 3">
                                <el-select v-model="form.send_group" style="width:70%" placeholder="请选择会员分组" >
                                    <el-option v-for="item in groups_list" :key="item.id" :label="item.group_name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </div>
                </el-form>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>

            
        </div>
    </div>
    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                let id = {!! $id?:0 !!};
                console.log(id);
                return{
                    id:id,
                    form:{
                        sendtype:1,
                    },
                    member_list:[],
                    groups_list:[],
                    rules:{
                        send_total:[
                            { required: true, message: '请输入发送张数'},
                            {  type: 'number',min: 1,message: '请输入正确数字',},
                        ]
                    },


                }
            },
            created() {


            },
            mounted() {
                if(this.id) {
                    this.getData();
                }
                
            },
            methods: {
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.send-coupon.send-data') !!}',{id:this.id}).then(function (response) {
                            if (response.data.result){
                                
                                this.member_list = response.data.data.memberLevels || [];
                                this.groups_list = response.data.data.memberGroups || [];
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
                    let that = this;
                    let json = {
                        id:this.id,
                        send_total:this.form.send_total,
                        sendtype:this.form.sendtype,
                    };
                    if(this.form.sendtype == 1) {
                        if(!this.form.send_memberid) {
                            this.$message.error("请输入会员ID")
                            return false;
                        }
                        json.send_memberid = this.form.send_memberid
                    }
                    if(this.form.sendtype == 2) {
                        if(!this.form.send_level) {
                            this.$message.error("请选择会员等级")
                            return false;
                        }
                        json.send_level = this.form.send_level
                    }
                    if(this.form.sendtype == 3) {
                        if(!this.form.send_group) {
                            this.$message.error("请选择会员分组")
                            return false;
                        }
                        json.send_group = this.form.send_group
                    }
                    
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post('{!! yzWebFullUrl('coupon.send-coupon.send-data') !!}',json).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    this.goBack();
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
                
                goBack() {
                    history.go(-1)
                },
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.index') !!}`;
                },
                
            },
        })

    </script>
@endsection


