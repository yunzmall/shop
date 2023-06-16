    @extends('layouts.base')
    @section('title', '注册/登录设置')
    @section('content')
    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style scoped>
        .main-panel{
            margin-top:50px;
        }
        .main-panel #re_content {
            padding: 10px;
        }
        .panel{
            margin-bottom:10px!important;
            border-radius: 10px;
            padding-left: 20px;
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
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .block{
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
        .el-checkbox__inner{
            border:solid 1px #56be69!important;
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
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 注册/登录设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">注册/登录设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="注册状态">
                                <template>
                                    <el-switch
                                            v-model="form.get_register"
                                            active-value="0"
                                            inactive-value="1"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">关闭后手机端不显示注册入口</div>
                            </el-form-item>
                            <el-form-item label="注册是否需要填写密码">
                                <template>
                                    <el-switch
                                            v-model="form.is_password"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启，关闭后用户注册/绑定手机号不需要设置密码！</div>
                            </el-form-item>
                            <el-form-item label="登录方式">
                                <el-checkbox-group v-model="form.login_mode" text-color="#29ba9c">
                                    <el-checkbox label="mobile_code">手机验证码</el-checkbox>
                                    <el-checkbox label="password">密码</el-checkbox>
                                </el-checkbox-group>
                                <div class="tip">
                                    勾选手机验证码登录，则H5前端不再显示注册入口，未注册用户使用手机验证码登录即可完成注册+登录操作。<br>
                                    同时勾选手机验证码、密码登录，则优先使用手机验证码登录，同时支持切换密码登录。
                                </div>
                            </el-form-item>
                            <el-form-item label="微信授权登录">
                                <template>
                                    <el-switch
                                            v-model="form.wechat_login_mode"
                                            active-value="0"
                                            inactive-value="1"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启；关闭后用户在微信端访问H5页面，则不请求微信授权登录，规则同H5端，同时不能使用微信公众号相关能力，包括微信支付。</div>
                            </el-form-item>
                            <el-form-item label="强制绑定手机" class="bind-phone">
                                <template>
                                    <el-radio-group v-model="form.is_bind_mobile">
                                        <el-radio label="0">否</el-radio>
                                        <el-radio label="1">全局强制绑定</el-radio>
                                        <el-radio label="2">指定页面</el-radio>
        {{--                                <el-radio label="2">会员中心强制绑定</el-radio>--}}
        {{--                                <el-radio label="3">商品页面强制绑定</el-radio>--}}
        {{--                                <el-radio label="4">推广中心页面强制绑定</el-radio>--}}
                                    </el-radio-group>
                                    <el-checkbox-group v-model="form.bind_mobile_page" text-color="#29ba9c" v-if="form.is_bind_mobile==2">
                                        <el-checkbox label="member_center">会员中心</el-checkbox>
                                        <el-checkbox label="goods_detail">商品详情页</el-checkbox>
                                        <el-checkbox label="promotion_center">推广中心</el-checkbox>
                                    </el-checkbox-group>
                                </template>
                                <div style="font-size:12px;">进入商城是否强制绑定手机号，指定页面才强制绑定手机</div>
                            </el-form-item>

                            <el-form-item label="强制设置默认地址" class="bind-address">
                                <template>
                                    <el-radio-group v-model="form.is_bind_address">
                                        <el-radio label="0">否</el-radio>
                                        <el-radio label="1">全局强制设置</el-radio>
                                        <el-radio label="2">指定页面</el-radio>
                                        {{--                                <el-radio label="2">会员中心强制绑定</el-radio>--}}
                                        {{--                                <el-radio label="3">商品页面强制绑定</el-radio>--}}
                                        {{--                                <el-radio label="4">推广中心页面强制绑定</el-radio>--}}
                                    </el-radio-group>
                                    <el-checkbox-group v-model="form.bind_address_page" text-color="#29ba9c" v-if="form.is_bind_address==2">
                                        <el-checkbox label="member_center">会员中心</el-checkbox>
                                        <el-checkbox label="goods_detail">商品详情页</el-checkbox>
                                        <el-checkbox label="promotion_center">推广中心</el-checkbox>
                                    </el-checkbox-group>
                                </template>
                                <div style="font-size:12px;">进入商城是否强制设置默认地址，指定页面才强制设置默认地址</div>
                            </el-form-item>

                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 注册页面设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">注册页面设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="注册/绑定手机页面顶部图片">
                                <div class="upload-box" @click="openUpload('top_img','1','one')" v-if="!form.top_img_url">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('top_img','1','one')" class="upload-boxed" v-if="form.top_img_url" style="height:150px;">
                                    <img :src="form.top_img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                    <div class="upload-boxed-text">点击重新上传</div>
                                    <i class="el-icon-close" @click.stop="clearImg('top_img')" title="点击清除图片"></i>
                                </div>
                                <div class="tip">会员默认头像（会员自定义头像>微信头像>商城默认头像）</div>
                            </el-form-item>
                            <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum"  @replace="changeProp" @sure="sureImg"></upload-multimedia-img>

                            <el-form-item label="注册/登录引导主标题">
                                <el-input v-model="form.title1" style="width:70%;" placeholder="单行输入"></el-input>
                                <div style="font-size:12px;">默认为 "欢迎来到[商城名称]"</div>
                            </el-form-item>
                            <el-form-item label="注册/登录引导副标题">
                                <el-input v-model="form.title2" style="width:70%;" placeholder="单行输入"></el-input>
                                <div style="font-size:12px;">默认为 "登录尽享各种优惠权益！"</div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 登录页面设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">登录页面设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item  label="登录页模板">
                                <el-radio-group style="margin-top:5px;" v-model="form.login_page_mode">
                                    <el-radio :label="0">模板一</el-radio>
                                    <el-radio :label="1">模板二</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="登录页图片" v-show="form.login_page_mode == 1">
                                <div class="upload-box" @click="openUpload('login_banner','1','one')" v-if="!form.login_banner_url" style="width:250px;height:150px;">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('login_banner','1','one')" class="upload-boxed" v-if="form.login_banner_url" style="width:250px;height:150px;">
                                    <img :src="form.login_banner_url" alt="" style="width:250px;height:150px;border-radius: 5px;cursor: pointer;">
                                    <div class="upload-boxed-text">点击重新上传</div>
                                    <i class="el-icon-close" @click.stop="clearImg('login_banner')" title="点击清除图片"></i>
                                </div>
                                <div class="tip">登录页图片,建议尺寸640*320</div>
                            </el-form-item>
                            <el-form-item label="自定义跳转连接" v-show="form.login_page_mode == 1">
                                <el-input v-model="form.login_diy_url" placeholder="请填写指向的链接" style="width:60%;"></el-input><el-button @click="show=true" style="margin-left:10px;">选择链接</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                    <pop :show="show" @replace="changeProp1" @add="parHref"></pop>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 会员合并 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">会员合并</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item  label="会员合并">
                                <el-radio-group style="margin-top:5px;" v-model="form.is_member_merge">
                                    <el-radio :label="1">全自动合并</el-radio>
                                    <el-radio :label="0">新会员自动合并</el-radio>
                                </el-radio-group>
                                <div class="select_text_box">
                                    新会员自动合并：合并规则上，如果被合并的会员是新注册的会员，则自动合并；如果不是，在提示会员数据异常，请联系客服处理，由后台选择需要保留的会员ID。</br>
                                    全自动合并：按照会员保留优先级保留会员，其他会员登录信息自动同步到对应的会员ID上，除保留的会员登录凭证外，其他被同步的会员数据 （订单、下级、佣金等）全部无法合并。
                                </div>
                            </el-form-item>
                            <el-form-item  label="会员保留优先级">
                                <el-radio-group style="margin-top:5px;" v-model="form.is_merge_save_level">
                                    <el-radio :label="0">注册时间</el-radio>
                                    <el-radio :label="1">手机号</el-radio>
                                    <el-radio :label="2">公众号</el-radio>
                                    <el-radio :label="3">微信小程序</el-radio>
                                    <el-radio :label="4">APP微信登录</el-radio>
                                    <el-radio :label="5">支付宝登录</el-radio>
                                </el-radio-group>
                                <div class="select_text_box" >
                                    会员合并时，保留所选优先级对应的会员ID，其他的会员登录平台将合并到该会员ID上；</br>
                                    如果不存在优先级设置的会员，则按注册时间优先合并。
                                </div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 邀请码 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">邀请码</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
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
                                <div style="font-size:12px;">提示： 默认关闭，使用该功能必须开启邀请页面</div>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </div>

        </div>
    </div>
    @include('public.admin.uploadMultimediaImg')
    @include('public.admin.pop')

    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                return {
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    show:false,//是否开启公众号弹窗
                    chooseImgListName:'',
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                }
            },
            created() {
            },
            mounted () {
                console.log(this.form,111);
            },
            methods: {
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type
                    this.selNum = sel
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,uploadShow,fileList) {
                    if(fileList.length <= 0) {
                        return
                    }
                    console.log(name)
                    console.log(fileList)
                    this.form[name] =fileList[0].attachment;
                    this.form[name+'_url'] = fileList[0].url;
                    console.log(this.form[name],'aaaaa')
                    console.log( this.form[name+'_url'],'bbbbb')
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
                //弹窗显示与隐藏的控制
                changeProp1(item){
                    this.show=item;
                },
                //当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    this.form.login_diy_url=child;

                },
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.register-and-login-store') !!}';
                    let json = {
                        set : this.form
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});

                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        // location.reload();
                    },response => {
                        loading.close();
                    });
                },
            }
        })
    </script>
    @endsection