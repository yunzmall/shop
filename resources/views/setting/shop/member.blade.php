@extends('layouts.base')

@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .main-panel{
            margin-top:50px;
        }
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
            padding:10px!important;
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
        b{
            font-size:14px;
        }
        .upload-boxed .el-icon-close {
            position: absolute;
            top: -5px;
            right: -5px;
            color: #fff;
            background: #333;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>基础设置
                            </b></div>
                        <el-form-item label="会员默认头像">
                            <div class="upload-box" @click="openUpload('headimg')" v-if="!form.headimg_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('headimg')" class="upload-boxed" v-if="form.headimg_url" style="height:150px;">
                                <img :src="form.headimg_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('headimg')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">会员默认头像（会员自定义头像>微信头像>商城默认头像）</div>

                        </el-form-item>
                        <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>
                        <el-form-item label="注册状态">
                            <template>
                                <el-switch
                                        v-model="form.get_register"
                                        active-value="0"
                                        inactive-value="1"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="手机验证码登录">
                            <template>
                                <el-switch
                                        v-model="form.mobile_login_code"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div>开启后，会员使用手机验证码快速登录商城，而不再使用密码登录！</div>
                        </el-form-item>
                        <el-form-item label="微信端登录方式">
                            <template>
                                <el-radio-group v-model="form.wechat_login_mode">
                                    <el-radio label="1">手机号码</el-radio>
                                    <el-radio label="0">自动授权登录</el-radio>
                                </el-radio-group>
                            </template>
                            <div>选择手机号号码登录，将不再使用微信公众号授权登录，微信端等同于WAP端，微信公众号相关功能失效！</div>
                        </el-form-item>
                        <el-form-item label="强制绑定手机" class="bind-phone">
                            <template>
                                <el-radio-group v-model="form.is_bind_mobile">
                                    <el-radio label="0">否</el-radio>
                                    <el-radio label="1">全局强制绑定</el-radio>
                                    <el-radio label="2">会员中心强制绑定</el-radio>
                                    <el-radio label="3">商品页面强制绑定</el-radio>
                                    <el-radio label="4">推广中心页面强制绑定</el-radio>
                                </el-radio-group>
                            </template>
                            <div style="font-size:12px;">进入商城是否强制绑定手机号，指定页面才强制绑定手机</div>
                        </el-form-item>
                        <el-form-item label="自定义字段">
                            <template>
                                <el-switch
                                        v-model="form.is_custom"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示：开启后，在会员中心--设置中显示字段，用户编辑资料填写！</div>
                        </el-form-item>
                        <el-form-item label="自定义字段显示名称"  >
                            <el-input v-model="form.custom_title" placeholder="请输入自定义字段显示名称" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="自定义表单" v-if="is_diyform == true">
                            <template>
                                <el-select v-model="form.form_id"  @change="getVal" name="form_id">
                                    <el-option

                                            v-for="item in  diyForm"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="会员中心显示余额">
                            <template>
                                <el-switch
                                        v-model="form.show_balance"

                                        active-value="0"
                                        inactive-value="1"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示：会员中心是否显示会员余额字样</div>
                        </el-form-item>
                        <el-form-item label="会员中心显示积分">
                            <template>
                                <el-switch
                                        v-model="form.show_point"

                                        active-value="0"
                                        inactive-value="1"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示：会员中心是否显示会员积分！</div>
                        </el-form-item>
                        <el-form-item label="会员中心显示会员ID">
                            <template>
                                <el-switch
                                        v-model="form.show_member_id"

                                        active-value="0"
                                        inactive-value="1"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示：会员中心是否显示会员ID！</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>会员等级设置</b><span style="margin-left:15px;color:#999;font-size:12px;">设置后可在会员>会员等级列表中添加会员等级！</span></div>

                        <el-form-item label="默认会员级别名称"  >
                            <el-input v-model="form.level_name" placeholder="请输入会员级别名称" style="width:70%;"></el-input>
                            <div style="font-size:12px;">会员默认等级名称，不填写默认“普通会员”</div>
                        </el-form-item>
                        <el-form-item label="会员等级权益页面是否显示" >
                            <template>
                                <el-switch
                                        v-model="form.display_page"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示： 开启，用户在商品详情页和会员中心点击会员等级都可以进入等级权益页面</div>
                        </el-form-item>
                        <el-form-item label="会员等级升级依据">
                            <template>
                                <el-radio-group v-model="form.level_type">
                                    <el-radio label="0">订单金额</el-radio>
                                    <el-radio label="1">订单数量</el-radio>
                                    <el-radio label="2">指定购买商品</el-radio>
                                    <el-radio label="3">团队业绩(自购+一级+二级)</el-radio>
                                    <el-radio label="4">一次性充值余额</el-radio>
                                </el-radio-group>
                            </template>
                        </el-form-item>
                        <el-form-item label=" ">
                            <template>
                                <el-radio-group v-model="form.level_after">
                                    <el-radio label="1">付款后</el-radio>
                                    <el-radio label="0">完成后</el-radio>

                                </el-radio-group>
                            </template>
                            <div style="font-size:12px;">如果选择付款后，只要用户下单付款满足升级依据，即可升级；如果选择完成后，则表示需要订单完成状态才能升级</div>
                        </el-form-item>
                        <el-form-item label="会员等级时间限制" >
                            <template>
                                <el-switch
                                        v-model="form.term"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="会员等级优惠计算方式 ">
                            <template>
                                <el-radio-group v-model="form.level_discount_calculation">
                                    <el-radio label="0">折扣方式</el-radio>
                                    <el-radio label="1">成本比例方式</el-radio>

                                </el-radio-group>
                            </template>
                            <div style="font-size:12px;"> 折扣方式：按打折计算最高10折，折扣价格= 商品现价 * (1 - 折扣率/10 ) <br/>
                                成本比例方式：按百分比计算最高999%, 折扣价格 = 商品成本价 * (比例/100) <br/></div>
                        </el-form-item>
                        <el-form-item label="商品详情会员折扣" >
                            <template>
                                <el-switch
                                        v-model="form.discount"
                                        active-value="1"
                                        inactive-value="2"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="商品详情已添加数量" >
                            <template>
                                <el-switch
                                        v-model="form.added"
                                        active-value="1"
                                        inactive-value="2"
                                >
                                </el-switch>
                            </template>

                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>邀请码</b></div>

                        <el-form-item label="邀请码" >
                            <template>
                                <el-switch
                                        v-model="form.is_invite"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="邀请码是否必填" >
                            <template>
                                <el-switch
                                        v-model="form.required"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="默认邀请码"  >
                            <el-input v-model="form.default_invite" placeholder="默认邀请码" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="邀请页面" >
                            <template>
                                <el-switch
                                        v-model="form.invite_page"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示： 邀请页面与强制绑定手机页面不能同时启用</div>
                        </el-form-item>
                        <el-form-item label="邀请页面总店强制修改" >
                            <template>
                                <el-switch
                                        v-model="form.is_bind_invite"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;">提示： 默认关闭</div>
                        </el-form-item>
                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
        </div>
        </el-form>
    </div>
    </div>
    @include('public.admin.uploadImg')
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    activeName: 'one',
                    diyFormData : '',
                    is_diyform : false,
                    diyForm : [],
                    form:{
                        headimg:'',
                        mobile_login_code:'0',
                        wechat_login_mode:'1',
                        is_bind_mobile:'0',
                        is_custom:'0',
                        custom_title:'',
                        show_balance:'0',
                        show_point:'0',
                        show_member_id:'0',
                        is_invite:'0',
                        required:'0',
                        default_invite:'',
                        invite_page:'0',
                        is_bind_invite:'0',
                        term:'0',
                        discount:'1',
                        added:'1',
                        display_page:'0',
                        level_type:'0',
                        level_after:'0',
                        form_id:'',
                        get_register: 1,
                        level_name:'普通会员',
                        level_discount_calculation:'0'
                    },
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                openUpload(str) {

                    this.chooseImgName = str;
                    this.uploadShow = true;
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,image,image_url) {
                    this.form[name] = image;
                    this.form[name+'_url'] = image_url;

                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.member') !!}').then(function (response){
                        if(response.data.data.set){
                            for(let i in response.data.data.set){
                                this.form[i]=response.data.data.set[i]
                            }

                        }
                        this.is_diyform=response.data.data.is_diyform
                        this.diyForm=response.data.data.diyForm
                        this.diyForm.unshift({id:'',title:'请选择表单'})
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                clearImg(str,type,index) {
                    if(!type) {
                        this.form[str] = "";
                        this.form[str+'_url'] = "";
                    }
                    else {
                        this.form[str].splice(index,1);
                        this.form[str+'_url'].splice(index,1);
                    }
                    this.$forceUpdate();
                },
                submit() {
                    this.getInfoSub()
                },
                getVal(val){
                    this.form.form_id=val
                    this.$forceUpdate()
                },
                getInfoSub(){
                    let that = this;
                    let loading = that.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    that.$http.post('{!! yzWebFullUrl('setting.shop.checkInviteCode') !!}',{'invite_code':that.form.default_invite}).then(function (response){
                        if (response.data.result) {
                            loading.close();
                            location.reload();
                            that.$http.post('{!! yzWebFullUrl('setting.shop.member') !!}',{'member':that.form}).then(function (response){
                                if (response.data.result) {
                                    that.$message({message:  response.data.msg,type: 'success'});
                                    loading.close();
                                    location.reload();
                                }else {
                                    that.$message({message: response.data.msg,type: 'error'});
                                    loading.close();
                                }
                            },function (response) {
                                that.$message({message: response.data.msg,type: 'error'});
                            })
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })

                }

            },
        });
    </script>
@endsection('content')
