@extends('layouts.base')
@section('content')
    <style>
        .panel{
            margin-bottom:10px!important;
            padding-left: 20px;
            border-radius: 10px;
        }
        .panel .active a {
            background-color: #29ba9c!important;
            border-radius: 18px!important;
            color:#fff;
        }
        .panel a{
            border:none!important;
            background-color:#fff!important;
        }
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            min-height:100vh;
            background-color:#fff;
        }
        .con .setting .block{
            padding:10px;
            margin-bottom:10px;
            border-radius:8px;
        }
        .con .setting .block .title{
            display:flex;
            align-items:center;
            margin-bottom:15px;
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
        b{
            font-size:14px;
        }
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"> <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                        <el-form-item label="短信图形验证码">
                            <template>
                                <el-switch
                                        v-model="form.status"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="国家区号"  >
                            <template>
                                <el-switch
                                        v-model="form.country_code"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block" >
                        <div class="title"> <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>通道设置</b></div>
                        <el-form-item label="短信设置" >
                            <template>
                                <el-radio-group v-model="form.type">
                                    <el-radio label="1">互亿无线</el-radio>
                                    <el-radio label="2">阿里大鱼</el-radio>
                                    <el-radio label="3">阿里云</el-radio>
                                    <el-radio label="5">腾讯云</el-radio>
                                </el-radio-group>
                            </template>
                        </el-form-item>
                        <el-form-item label="国内短信账号" v-if="form.type==1">
                            <el-input v-model="form.account"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="国内短信密码" v-if="form.type==1">
                            <el-input v-model="form.password"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="国际短信账号" v-if="form.type==1">
                            <el-input v-model="form.account2"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="国际短信密码" v-if="form.type==1">
                            <el-input v-model="form.password2"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item v-if="form.type==2" style="margin-top:-30px;">
                            <span>提示：请到 阿里大鱼 去申请开通,短信模板中必须包含code和product,请参考默认用户注册验证码设置</span>
                        </el-form-item>
                        <el-form-item label="AppKey" v-if="form.type==2">
                            <el-input v-model="form.appkey"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="secret" v-if="form.type==2">
                            <el-input v-model="form.secret"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="短信签名" v-if="form.type==2">
                            <el-input v-model="form.signname"  style="width:70%;" placeholder="例如注册验证"></el-input>
                        </el-form-item>

                        <el-form-item label="注册短信模板ID" v-if="form.type==2">
                            <el-input v-model="form.templateCode"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="注册模板变量" v-if="form.type==2">
                            <el-input v-model="form.product"  style="width:70%;" placeholder="product=xx商城"></el-input>
                        </el-form-item>
                        <el-form-item label="找回密码短信模板ID" v-if="form.type==2">
                            <el-input v-model="form.templateCodeForget"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="找回密码变量" v-if="form.type==2">
                            <el-input v-model="form.forget"  style="width:70%;" placeholder="product=xx商城"></el-input>
                        </el-form-item>
                        <el-form-item label="登录短信模板ID" v-if="form.type==2">
                            <el-input v-model="form.templateCodeLogin"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="登录变量" v-if="form.type==2">
                            <el-input v-model="form.login"  style="width:70%;" placeholder="product=xx商城"></el-input>
                        </el-form-item>
                        <el-form-item v-if="form.type==3" style="margin-top:-30px;">
                            <span>请到 阿里云 去申请开通,短信模板中必须包含number；阿里云默认模板为code，将code改为number，请参考默认用户注册验证码设置。</span>
                        </el-form-item>
                        <el-form-item label="AccessKeyId" v-if="form.type==3">
                            <el-input v-model="form.aly_appkey"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="AccessKeySecret" v-if="form.type==3">
                            <el-input v-model="form.aly_secret"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="短信签名" v-if="form.type==3">
                            <el-input v-model="form.aly_signname"  style="width:70%;" placeholder="例如注册验证"></el-input>
                        </el-form-item>
                        <el-form-item label="注册模板编号" v-if="form.type==3">
                            <el-input v-model="form.aly_templateCode"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="找回密码模板编号" v-if="form.type==3">
                            <el-input v-model="form.aly_templateCodeForget"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="登录模板编号" v-if="form.type==3">
                            <el-input v-model="form.aly_templateCodeLogin"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="余额定时提醒" v-if="form.type==3">
                            <el-input v-model="form.aly_templateBalanceCode"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="商品发货提醒" v-if="form.type==3">
                            <el-input v-model="form.aly_templateSendMessageCode"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="会员充值提醒" v-if="form.type==3">
                            <el-input v-model="form.aly_templatereChargeCode"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                        </el-form-item>
                        @if(app('plugins')->isEnabled('audit-debt'))
                            <el-form-item label="审核成功提醒" v-if="form.type==3">
                                <el-input v-model="form.audit_debt_confirm"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                                <div>短信参数：number--姓名</div>
                            </el-form-item>
                            <el-form-item label="审核驳回提醒" v-if="form.type==3">
                                <el-input v-model="form.audit_debt_reject"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                                <div>短信参数：number--姓名，remark--驳回原因</div>
                            </el-form-item>
                            <el-form-item label="提交资料短信验证码" v-if="form.type==3">
                                <el-input v-model="form.audit_debt_submit"  style="width:70%;" placeholder="例如:SMS_5057806"></el-input>
                                <div>短信参数：number--验证码</div>
                            </el-form-item>
                        @endif
                        <el-form-item v-if="form.type==5" style="margin-top:-30px;">
                            <span>请到 腾讯云 去申请开通。</span>
                        </el-form-item>
                        <el-form-item label="SDKAppID" v-if="form.type==5">
                            <el-input v-model="form.tx_sdkappid"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="AppKey" v-if="form.type==5">
                            <el-input v-model="form.tx_appkey"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="短信签名" v-if="form.type==5">
                            <el-input v-model="form.tx_signname"  style="width:70%;" placeholder="例如注册验证"></el-input>
                        </el-form-item>
                        <el-form-item label="注册模板ID" v-if="form.type==5">
                            <el-input v-model="form.tx_templateCode"  style="width:70%;" placeholder="例如5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="找回密码模板ID" v-if="form.type==5">
                            <el-input v-model="form.tx_templateCodeForget"  style="width:70%;" placeholder="例如5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="登录模板ID" v-if="form.type==5">
                            <el-input v-model="form.tx_templateCodeLogin"  style="width:70%;" placeholder="例如5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="余额定时提醒" v-if="form.type==5">
                            <el-input v-model="form.tx_templateBalanceCode"  style="width:70%;" placeholder="例如5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="商品发货提醒" v-if="form.type==5">
                            <el-input v-model="form.tx_templateSendMessageCode"  style="width:70%;" placeholder="例如5057806"></el-input>
                        </el-form-item>
                        <el-form-item label="会员充值提醒" v-if="form.type==5">
                            <el-input v-model="form.tx_templatereChargeCode"  style="width:70%;" placeholder="例如5057806"></el-input>
                        </el-form-item>
                        

                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary"  @click="submit">提交</el-button>
            </div>
            </el-form>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    activeName: 'one',
                    level:[],
                    form:{
                        account:'',
                        password:'',
                        account2:'',
                        password2:'',
                        aly_appkey:'',
                        aly_secret:'',
                        aly_signname:'',
                        aly_templateCode:'',
                        aly_templateCodeForget:'',
                        aly_templateCodeLogin:'',
                        aly_templateBalanceCode:'',
                        aly_templateSendMessageCode:'',
                        aly_templatereChargeCode:'',
                        audit_debt_submit:'',
                        audit_debt_confirm:'',
                        audit_debt_reject:'',
                        tx_sdkappid:'',
                        tx_appkey:'',
                        tx_signname:'',
                        tx_templateCode:'',
                        tx_templateCodeForget:'',
                        tx_templateCodeLogin:'',
                        tx_templateBalanceCode:'',
                        tx_templateSendMessageCode:'',
                        tx_templatereChargeCode:'',
                        status:'0',
                        country_code:'0',
                        type:'2',
                        appkey:'',
                        secret:'',
                        signname:'',
                        templateCode:'',
                        product:'',
                        templateCodeForget:'',
                        forget:'',
                        templateCodeLogin:'',
                        login:'',
                        time:1,
                    },
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.sms') !!}').then(function (response){
                        if (response.data.result) {
                            if(response.data.data.set){
                                for(let i in response.data.data.set){
                                    this.form[i]=response.data.data.set[i]
                                }
                            }
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.sms') !!}',{'sms':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },

            },
        });
    </script>
@endsection
