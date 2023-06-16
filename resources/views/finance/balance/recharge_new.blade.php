@extends('layouts.base')
@section('title', trans('会员充值'))
@section('content')
    <style>
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
        }

        .confirm-btn{
            width: calc(100% - 266px);
            position:fixed;
            bottom:0;
            right:0;
            margin-right:10px;
            line-height:63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px
            rgba(51, 51, 51, 0.3);
            background-color:#fff;
            text-align:center;
        }
        .cert{
            width: 115px;
            height: 42px;
            background-color: #29ba9c;
            border-radius: 4px;
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size: 16px;
            display: inline-block;
            text-align: center;
        }

        b{
            font-size:14px;
        }
        input[type=file]{
            display:none;
        }
    </style>
    <div id='re_content' >
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%" @submit.native.prevent>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额充值</b></div>
                        <el-form-item label="粉丝">
                            <div class="image" style="border:solid 1px #ccc;width:100px;height:100px;margin-left:30px;display: inline-block;">
                                <img :src="memberInfo.avatar" style="width: 100%;height:100%;">
                            </div>
                            <div style="display: inline-block;vertical-align: text-top;margin-left:10px;">[[memberInfo.nickname]]</div>
                        </el-form-item>
                        <el-form-item label="会员信息">
                            <div style="margin-left:30px;">
                                姓名:[[memberInfo.realname?memberInfo.realname:memberInfo.nicknamename]] / 手机号:[[memberInfo.mobile]]
                            </div>
                        </el-form-item>
                        <el-form-item :label="rechargeMenu.old_value">
                            <div style="margin-left:30px;">
                                [[memberInfo.credit2]]
                            </div>
                        </el-form-item>
                        <el-form-item :label="rechargeMenu.charge_value" required>
                            <el-input v-model="form.num" style="width:70%;"></el-input>
                        </el-form-item>

                        <el-form-item label="备注信息"  >
                            <el-input type="textarea" rows="5" v-model="form.remark" placeholder="" style="width:70%;" maxlength="50" show-word-limit></el-input>
                        </el-form-item>

                        <el-form-item label="充值说明"  v-if="charge_check_swich" required>
                            <el-input type="textarea" rows="5" v-model="form.explain" placeholder="" style="width:70%;"></el-input>
                        </el-form-item>

                        <el-form-item label="附件"  v-if="charge_check_swich">
                            [[form.enclosure_src]]
                            <el-upload
                                    class="upload-demo"
                                    action="{!! yzWebFullUrl('finance.balance-recharge-check.upload-file') !!}"
                                    name="file"
                                    :show-file-list="false"
                                    :on-success="uploadSuccess"
                                    :on-error="uploadFail"
                                    {{--:http-request="upload"--}}
                            >
                                <el-button type="primary">文件上传</el-button>
                            </el-upload>
                        </el-form-item>
                    </div>
                </el-form>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let memberInfo = {!! $memberInfo ? json_encode($memberInfo) : '{}'  !!};
                let rechargeMenu = {!! $rechargeMenu ? json_encode($rechargeMenu) : '{}'  !!};
                let charge_check_swich = {!! $charge_check_swich  !!};
                return {
                    activeName: 'first',
                    show:true,
                    Certshow:true,
                    weixin_secret_show:true,
                    weixin_apisecret_show:true,
                    alipay_transfer_private_show:true,

                    memberInfo:memberInfo,
                    rechargeMenu:rechargeMenu,
                    charge_check_swich:charge_check_swich,
                    form:{
                        num:'',
                        remark:"",
                        explain:'',
                        enclosure:"",
                        enclosure_src:"",
                        member_id:memberInfo.uid
                    },
                }
            },
            mounted () {

            },
            methods: {
                uploadFail(res){
                    this.$message({'上传失败': 'error'});
                },
                uploadSuccess(res){
                    console.log(res);
                    if(res.result){
                        this.$message({message:res.msg,type: 'success'});
                        this.form.enclosure = res.data.file;
                        this.form.enclosure_src = res.data.file_src;
                    }else{
                        this.$message({message:res.msg,type: 'error'});
                    }
                },
                submit() {
                    console.log(this.form);
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('balance.recharge.index') !!}',this.form).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                            location.reload();
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection