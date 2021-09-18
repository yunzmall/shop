@extends('layouts.base')
@section('title', trans('基础设置'))

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
                <el-form ref="form" :model="form" label-width="15%" >
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>

                        <el-form-item label="IOS虚拟支付一键关闭">
                            <template>
                                <el-switch
                                        v-model="form.ios_virtual_pay"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>微信支付</b></div>

                        <el-form-item label="是否开启">
                            <template>
                                <el-switch
                                        v-model="form.weixin"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size: 12px;">提示：标准微信支付、及其他微信支付接口（云收银）总开关。微信支付授权目录填写路径：域名/addons/yun_shop/</div>
                        </el-form-item>
                        <el-form-item label="标准微信支付">
                            <template>
                                <el-switch
                                        v-model="form.weixin_pay"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="微信H5支付">
                            <template>
                                <el-switch
                                        v-model="form.wechat_h5"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size: 12px;">提示：只支持手机浏览器，pc不支持</div>
                        </el-form-item>
                        <el-form-item label="微信扫码支付">
                            <template>
                                <el-switch
                                        v-model="form.wechat_native"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size: 12px;"></div>
                        </el-form-item>
                        <el-form-item label="身份标识(appId)"  >
                            <el-input v-model="form.weixin_appid" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="身份密钥(appSecret)"  >
                            <el-input v-model="form.weixin_secret" type="text"style="width:70%;"  v-if="weixin_secret_show"></el-input>
                            <span style="color:#29BA9C;font-size:12px;" v-if="form.weixin_secret && !weixin_secret_show ">已上传</span>
                            <el-button type="primary" @click="ResetValue('weixin_secret')" v-if="form.weixin_secret && !weixin_secret_show">重置</el-button>
                        </el-form-item>
                        <el-form-item label="微信支付商户号(mchId)"  >
                            <el-input v-model="form.weixin_mchid" style="width:70%;"></el-input>
                            <div>微信公众号以邮件形式告知</div>
                        </el-form-item>
                        <el-form-item label="微信支付密钥(apiSecret)"  >
                            <el-input v-model="form.weixin_apisecret" style="width:70%;" type="text"  v-if="weixin_apisecret_show" ></el-input>
                            <span style="color:#29BA9C;font-size:12px;" v-if="form.weixin_apisecret && !weixin_apisecret_show ">已上传</span>
                            <el-button type="primary" @click="ResetValue('weixin_apisecret')" v-if="form.weixin_apisecret && !weixin_apisecret_show">重置</el-button>
                            <div>获取路径：微信支付商户平台>账户设置>API安全--设置支付密钥（32位数）</div>
                        </el-form-item>
                        <el-form-item label="微信支付证书"  >
                            <template>
                                <el-radio-group v-model="form.weixin_version">
                                    <el-radio label="0">文件上传</el-radio>
                                    <el-radio label="1">文本上传</el-radio>
                                </el-radio-group>
                            </template>
                            <div style="font-size:12px;" >微信支付证书获取途径：登录微信商户平台--账户中心--API安全--下载证书</div>
                        </el-form-item>
                        <el-form-item label="CERT证书文件"  v-if="form.weixin_version==0">
                            <el-upload
                                    class="upload-demo"
                                    action="{!! yzWebFullUrl('setting.shop.newUpload') !!}"
                                    name="weixin_cert"
                                    :show-file-list="false"
                                    :on-success="uploadSuccess"
                                    :on-error="uploadfail"
                            >
                                <el-button type="primary">文件上传</el-button>
                            </el-upload>
                            <span style="color:#5adda2;font-size:12px;"  v-if="form.weixin_cert">已上传</span>
                            <div style="font-size:12px;">提示：下载证书 cert.zip 中的 apiclient_cert.pem 文件</div>
                        </el-form-item>
                        <el-form-item label="KEY密钥文件"  v-if="form.weixin_version==0">
                            <el-upload
                                    class="upload-demo"
                                    action="{!! yzWebFullUrl('setting.shop.newUpload') !!}"
                                    name="weixin_key"
                                    :show-file-list="false"
                                    :on-success="uploadkey"
                                    :on-error="uploadfail"
                            >
                                <el-button type="primary">文件上传</el-button>
                            </el-upload>
                            <div style="font-size:12px;">提示：下载证书 cert.zip 中的 apiclient_key.pem 文件</div>
                            <span style="color:#5adda2;font-size:12px;"  v-if="form.weixin_key">已上传</span>
                        </el-form-item>
                        <el-form-item label="CERT证书文件"  v-if="form.weixin_version==1">
                            <el-input v-model="form.new_weixin_cert"  type="textarea"  style="width:70%;" v-if="Certshow"></el-input>
                        </el-form-item>
                        <el-form-item label="KEY密钥文件"  v-if="form.weixin_version==1">
                            <el-input v-model="form.new_weixin_key"  type="textarea" style="width:70%;" v-if="Certshow"></el-input>
                        </el-form-item>
                        <el-form-item label=" " v-if="form.weixin_version==1&&!Certshow" >
                            <el-button type="primary" @click="certReset">重新设置</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>支付宝支付接口</b></div>
                        <el-form-item label="支付宝支付">
                            <template>
                                <el-switch
                                        v-model="form.alipay"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="接口方式"  >
                            <template>
                                <el-radio-group v-model="form.alipay_pay_api">
                                    <el-radio label="0">旧接口</el-radio>
                                    <el-radio label="1">新接口</el-radio>
                                </el-radio-group>
                            </template>
                        </el-form-item>
                        <el-form-item label="收款支付宝账号"  v-if="form.alipay_pay_api=='0'">
                            <el-input v-model="form.alipay_account" style="width:70%;"></el-input>
                            <div style="font-size: 12px;">提示：卖家支付宝账号</div>
                        </el-form-item>
                        <el-form-item label="合作者身份"  v-if="form.alipay_pay_api=='0'">
                            <el-input v-model="form.alipay_partner" style="width:70%;"></el-input>
                            <div style="font-size: 12px;">提示：签约的支付宝账号对应的支付宝唯一用户号,以 2088 开头的 16 位纯数字组成</div>
                        </el-form-item>
                        <el-form-item label="校验密钥"  v-if="form.alipay_pay_api=='0'" >
                            <el-input v-model="form.alipay_secret" style="width:70%;" type="text"></el-input>
                            <div style="font-size: 12px;">提示：支付宝开放平台--账户中心--mapi网关产品密钥--MD5密钥</div>
                        </el-form-item>

                        <el-form-item label="应用ID"  v-if="form.alipay_pay_api=='1'">
                            <el-input v-model="form.alipay_app_id" style="width:60%;"></el-input>
                        </el-form-item>
                        <el-form-item label="开发者私钥" v-if="form.alipay_pay_api=='1'">
                            <el-input v-model="form.rsa_private_key" type="textarea" style="width:70%;" v-if="show"></el-input>
                        </el-form-item>
                        <el-form-item label="支付宝公钥" v-if="form.alipay_pay_api=='1'">
                            <el-input v-model="form.rsa_public_key" type="textarea" style="width:70%;" v-if="show"></el-input>
                        </el-form-item>
                        <el-form-item label="" v-if="!show&&form.alipay_pay_api=='1'" >
                            <el-button type="primary" @click="Reset">重新设置公私钥</el-button>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>支付宝提现设置</b></div>
                        <el-form-item label="是否开启">
                            <template>
                                <el-switch
                                        v-model="form.alipay_withdrawals"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            {{--<div>开启后，需在支付宝支付接口--新接口处设置应用ID、开发者私钥、支付宝公钥</div>--}}
                        </el-form-item>
                        <div v-if="form.alipay_withdrawals=='1'">
                            <el-form-item label="转账接口版本">
                                <template>
                                    <el-radio-group v-model="form.alipay_transfer">
                                        <el-radio label="0">公钥模式</el-radio>
                                        <el-radio label="1">公钥证书模式</el-radio>
                                    </el-radio-group>
                                </template>
                                <div  v-if="form.alipay_transfer=='0'" style="font-size: 12px;">
                                    需在支付宝支付接口--新接口处设置应用ID、开发者私钥、支付宝公钥
                                </div>
                                <div v-if="form.alipay_transfer=='1'">需上传：应用公钥证书、支付宝公钥证书、支付宝根证书</div>
                            </el-form-item>

                            <el-form-item label="应用ID"  v-if="form.alipay_transfer =='1'">
                                <el-input v-model="form.alipay_transfer_app_id" style="width:60%;"></el-input>
                            </el-form-item>

                            <el-form-item label="应用私钥" v-if="form.alipay_transfer=='1'">
                                <el-input v-model="form.alipay_transfer_private" type="textarea" style="width:70%;" v-if="alipay_transfer_private_show"></el-input>
                                <span style="color:#29BA9C;font-size:12px;" v-if="form.alipay_transfer_private && !alipay_transfer_private_show ">已填写</span>
                                <el-button type="primary" @click="ResetValue('alipay_transfer_private')" v-if="form.alipay_transfer_private && !alipay_transfer_private_show">重置</el-button>
                            </el-form-item>
                            <el-form-item label="应用公钥证书"  v-if="form.alipay_transfer==1">
                                <el-upload
                                        class="upload-demo"
                                        action="{!! yzWebFullUrl('setting.shop.newUpload') !!}"
                                        name="alipay_app_public_cert"
                                        :show-file-list="false"
                                        :on-success="uploadSuccess"
                                        :on-error="uploadfail"
                                >
                                    <el-button type="primary">文件上传</el-button>
                                </el-upload>
                                <span style="color:#5adda2;font-size:12px;"  v-if="form.alipay_app_public_cert">已上传</span>
                                {{--<div style="font-size:12px;">提示：下载应用公钥证书</div>--}}
                            </el-form-item>
                            <el-form-item label="支付宝公钥证书"  v-if="form.alipay_transfer==1">
                                <el-upload
                                        class="upload-demo"
                                        action="{!! yzWebFullUrl('setting.shop.newUpload') !!}"
                                        name="alipay_public_cert"
                                        :show-file-list="false"
                                        :on-success="uploadSuccess"
                                        :on-error="uploadfail"
                                >
                                    <el-button type="primary">文件上传</el-button>
                                </el-upload>
                                <span style="color:#5adda2;font-size:12px;"  v-if="form.alipay_public_cert">已上传</span>
                                {{--<div style="font-size:12px;">提示：下载支付宝公钥证书</div>--}}
                            </el-form-item>
                            <el-form-item label="支付宝根证书"  v-if="form.alipay_transfer==1">
                                <el-upload
                                        class="upload-demo"
                                        action="{!! yzWebFullUrl('setting.shop.newUpload') !!}"
                                        name="alipay_root_cert"
                                        :show-file-list="false"
                                        :on-success="uploadSuccess"
                                        :on-error="uploadfail"
                                >
                                    <el-button type="primary">文件上传</el-button>
                                </el-upload>
                                <span style="color:#5adda2;font-size:12px;"  v-if="form.alipay_root_cert">已上传</span>
                                {{--<div style="font-size:12px;">提示：下载支付宝根证书</div>--}}
                            </el-form-item>
                        </div>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额支付</b></div>

                        <el-form-item label="是否开启">
                            <template>
                                <el-switch
                                        v-model="form.credit"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>找人代付</b></div>

                        <el-form-item label="是否开启">
                            <template>
                                <el-switch
                                        v-model="form.another"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size: 12px;">开启后,(买家)下单后，可将订单分享给小伙伴(朋友圈、微信群、微信好友)请他帮忙付款。</div>
                        </el-form-item>
                        <el-form-item label="发起人求助"  >
                            <el-input v-model="form.another_share_title" style="width:70%;"></el-input>
                            <div style="font-size: 12px;">提示：默认分享标题：土豪大大，跪求代付</div>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>银行转账</b></div>

                        <el-form-item label="是否开启">
                            <template>
                                <el-switch
                                        v-model="form.remittance"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size: 12px;">提示：前端转账支付页选择汇款支付，上传支付凭证，后台财务审核</div>
                        </el-form-item>
                        <el-form-item label="开户行"  >
                            <el-input v-model="form.remittance_bank" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="开户支行"  >
                            <el-input v-model="form.remittance_sub_bank" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="开户名"  >
                            <el-input v-model="form.remittance_bank_account_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="开户账号"  >
                            <el-input v-model="form.remittance_bank_account" style="width:70%;"></el-input>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>货到付款</b></div>

                        <el-form-item label="是否开启">
                            <template>
                                <el-switch
                                        v-model="form.COD"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>

                        </el-form-item>

                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
            </el-form>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {

                    let set = {!! $set ?: '{}'  !!}
                    let data = {!! $data ?: '{}' !!}
                    console.log(set);
                    return {
                        activeName: 'first',
                        show:true,
                        Certshow:true,
                        weixin_secret_show:true,
                        weixin_apisecret_show:true,
                        alipay_transfer_private_show:true,
                        set:set,
                        form:{
                            ios_virtual_pay: set.ios_virtual_pay,
                            weixin: set.weixin,
                            secret:1,
                            weixin_pay: set.weixin_pay,
                            weixin_appid: set.weixin_appid,
                            weixin_secret: set.weixin_secret,
                            weixin_mchid: set.weixin_mchid,
                            weixin_apisecret: set.weixin_apisecret,
                            weixin_version: set.weixin_version?set.weixin_version:'0',
                            weixin_cert:set.weixin_cert?set.weixin_cert:'',
                            weixin_key:set.weixin_key?set.weixin_key:'',
                            new_weixin_cert: set.new_weixin_cert,
                            new_weixin_key: set.new_weixin_key,
                            rsa_private_key: set.rsa_private_key,
                            alipay_app_id: set.alipay_app_id,
                            rsa_public_key: set.rsa_public_key,
                            alipay: set.alipay,
                            wechat_h5 : set.wechat_h5,
                            wechat_native : set.wechat_native,
                            alipay_pay_api: set.alipay_pay_api?set.alipay_pay_api:'0',
                            alipay_name: set.alipay_name,
                            alipay_account: set.alipay_account?set.alipay_account:'',
                            alipay_partner: set.alipay_partner?set.alipay_partner:'',
                            alipay_secret: set.alipay_secret?set.alipay_secret:'',
                            api_version: set.api_version?set.api_version:'2',
                            //支付宝提现
                            alipay_transfer:set.alipay_transfer,
                            alipay_transfer_app_id:set.alipay_transfer_app_id,
                            alipay_transfer_private:set.alipay_transfer_private,
                            //支付宝证书签名方式
                            alipay_app_public_cert: set.alipay_app_public_cert?set.alipay_app_public_cert:'',
                            alipay_public_cert: set.alipay_public_cert?set.alipay_public_cert:'',
                            alipay_root_cert: set.alipay_root_cert?set.alipay_root_cert:'',

                            credit: set.credit,
                            another: set.another,
                            another_share_title:set.another_share_title,
                            remittance:set.remittance,
                            COD: set.COD,
                            remittance_bank: set.remittance_bank,
                            remittance_sub_bank : set.remittance_sub_bank,
                            remittance_bank_account_name : set.remittance_bank_account_name,
                            remittance_bank_account : set.remittance_bank_account,
                            alipay_withdrawals : set.alipay_withdrawals,
                            ...data
                        },
                    }
            },
            mounted () {
                this.getShow();
                this.getCert();
            },
            methods: {
                uploadkey(res){
                    if(res.result){
                        this.$message({message:res.msg,type: 'success'});
                        this.form.weixin_key=res.data.data.data.weixin_key;

                    }else{
                        this.$message({message:res.msg,type: 'error'});
                    }
                },
                uploadfail(res){
                    this.$message({'上传失败': 'error'});
                },
                uploadSuccess(res){
                    if(res.result){
                        this.$message({message:res.msg,type: 'success'});
                        this.form[res.data.data.data.key] = res.data.data.data.value;

                    }else{
                        this.$message({message:res.msg,type: 'error'});
                    }
                },
                getCert(){
                    if(this.set.new_weixin_cert||this.set.new_weixin_key){
                        this.Certshow=false
                    }
                },
                getShow(){
                    if(this.set.rsa_private_key||this.set.rsa_public_key){
                        this.show=false
                    }
                    if(this.set.weixin_secret){
                        this.weixin_secret_show=false
                    }
                    if(this.set.weixin_apisecret){
                        this.weixin_apisecret_show=false
                    }
                    if(this.set.alipay_transfer_private){
                        this.alipay_transfer_private_show=false
                    }
                },
                certReset(){
                    this.Certshow=true
                    this.form.new_weixin_cert=''
                    this.form.new_weixin_key=''
                },
                Reset(){
                    this.show=true
                    this.form.rsa_private_key=''
                    this.form.rsa_public_key=''
                },
                ResetValue(str){
                    switch (str) {
                        case 'weixin_secret' :
                            this.form.weixin_secret = ''
                            this.weixin_secret_show = true
                            break;
                        case 'weixin_apisecret' :
                            this.form.weixin_apisecret = ''
                            this.weixin_apisecret_show = true
                            break;
                        case 'alipay_transfer_private' :
                            this.form.alipay_transfer_private = ''
                            this.alipay_transfer_private_show = true
                            break;

                    }
                },
                submit() {
                    console.log(this.form);
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.pay') !!}',{'pay':this.form}).then(function (response){
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