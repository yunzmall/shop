    @extends('layouts.base')
    @section('title', '会员资料设置')
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
            padding:10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
            background-color:#fff;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
            margin-bottom:10px;
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
            justify-content:space-between;
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
        .el-form-item__label{
            margin-right:30px;
        }
        .add{
            width: 100px;
            height: 36px;
            border-radius: 4px;
            border: solid 1px #29ba9c;
            color:#29ba9c;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .list-wrap{
            padding-left:10%;
        }
        .list{
            width: 911px;
            height: 159px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-bottom:20px;
            box-sizing:border-box;
            padding:40px 0;
            position:relative;
        }
        .list .delete{
            width: 26px;
            height: 26px;
            background-color: #dee2ee;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            position:absolute;
            right:0;
            top:0;
            font-size:16px;
        }

        b{
            font-size:14px;
        }
    </style>
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 基础信息 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">基本信息</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="注册填写">
                                <template>
                                    <el-switch
                                            v-model="form.basic_register"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启，开启后用户在注册会员、绑定手机号时需要填写自定义字段信息，开启手机验证码快速登录不显示该信息！关闭则仅在设置-会员资料中显示！</div>
                            </el-form-item>
                            <el-form-item label="基本信息">
                                <span style="margin: 0 30px 0 20px; font-size: 14px; color: #666;font-weight: 400;">名称</span>
                                <span style="margin: 0 30px 0 50px; font-size: 14px; color: #666;font-weight: 400;">是否必填</span>
                            </el-form-item>
                            <el-form-item label="">
                                <el-checkbox v-model="form.name" style="margin: 0 30px 0 20px; font-size: 14px; color: #666;font-weight: 400;">姓名</el-checkbox>
                                <el-switch
                                        v-model="form.name_must"
                                        active-value="1"
                                        inactive-value="0"
                                        style="margin: 0 30px 0 50px; font-size: 14px; color: #666;font-weight: 400;"
                                >
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="">
                                <el-checkbox v-model="form.sex" style="margin: 0 30px 0 20px; font-size: 14px; color: #666;font-weight: 400;">性别</el-checkbox>
                                <el-switch
                                        v-model="form.sex_must"
                                        active-value="1"
                                        inactive-value="0"
                                        style="margin: 0 30px 0 50px; font-size: 14px; color: #666;font-weight: 400;"
                                >
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="">
                                <el-checkbox v-model="form.address" style="margin: 0 0 0 20px; font-size: 14px; color: #666;font-weight: 400;">详细地址</el-checkbox>
                                <el-switch
                                        v-model="form.address_must"
                                        active-value="1"
                                        inactive-value="0"
                                        style="margin: 0 30px 0 50px; font-size: 14px; color: #666;font-weight: 400;"
                                >
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="">
                                <el-checkbox v-model="form.birthday" style="margin: 0 30px 0 20px; font-size: 14px; color: #666;font-weight: 400;">生日</el-checkbox>
                                <el-switch
                                        v-model="form.birthday_must"
                                        active-value="1"
                                        inactive-value="0"
                                        style="margin: 0 30px 0 50px; font-size: 14px; color: #666;font-weight: 400;"
                                >
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="">
                                <el-checkbox v-model="form.idcard" style="margin: 0 30px 0 20px; font-size: 14px; color: #666;font-weight: 400;">身份证</el-checkbox>
                                <el-switch
                                        v-model="form.idcard_must"
                                        active-value="1"
                                        inactive-value="0"
                                        style="margin: 0 30px 0 34px; font-size: 14px; color: #666;font-weight: 400;"
                                >
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="">
                                <el-checkbox v-model="form.idcard_addr" style="margin: 0 30px 0 20px; font-size: 14px; color: #666;font-weight: 400;">身份证地址</el-checkbox>
                                <el-switch
                                        v-model="form.idcard_addr_must"
                                        active-value="1"
                                        inactive-value="0"
                                        style="margin: 0 30px 0 6px; font-size: 14px; color: #666;font-weight: 400;"
                                >
                                </el-switch>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 头像昵称 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">头像昵称</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="修改昵称头像">
                                <template>
                                    <el-switch
                                            v-model="form.change_info"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启，用户可以在会员中心-设置中修改头像昵称</div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <!-- 自定义字段-固定 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">自定义字段-固定</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="自定义字段-固定">
                                <template>
                                    <el-switch
                                            v-model="form.is_custom"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">提示：开启后，在会员中心--设置中显示字段，用户编辑资料填写！会员列表可搜索！</div>
                            </el-form-item>
                            <el-form-item label="注册填写">
                                <template>
                                    <el-switch
                                            v-model="form.is_custom_register"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启，开启后用户在注册会员、绑定手机号时需要填写自定义字段信息，开启手机验证码快速登录不显示该信息！关闭则仅在设置-会员资料中显示！</div>
                            </el-form-item>
                            <el-form-item label="自定义字段显示名称">
                                <el-input v-model="form.custom_title" style="width:70%;" placeholder=""></el-input>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 自定义 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">自定义</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="自定义必填开启">
                                <template>
                                    <el-switch
                                            v-model="form.form_open"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">开启后，自定义字段必填</div>
                            </el-form-item>
                            <el-form-item label="注册填写">
                                <template>
                                    <el-switch
                                            v-model="form.form_register"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启，开启后用户在注册会员、绑定手机号时需要填写自定义字段信息，开启手机验证码快速登录不显示该信息！关闭则仅在设置-会员资料中显示！</div>
                            </el-form-item>
                            <el-form-item label="前端禁止编辑">
                                <template>
                                    <el-switch
                                            v-model="form.form_edit"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">开启后，前端会员设置不可编辑</div>
                            </el-form-item>
                            <el-form-item label="自定义字段">
                                <el-button type="primary"  @click="addBlock">添加</el-button>
                            </el-form-item>
                            <div class="list-wrap">
                                <div class="list" v-for="(item,index) in form.form">
                                    <div>
                                        <span style="display:inline-block;margin-right:30px;width:150px;text-align:right;">排序</span>
                                        <el-input v-model="item.sort" style="width:60%;"></el-input>
                                    </div>
                                    <div style="margin-top:20px;">
                                        <span style="display:inline-block;margin-right:30px;width:150px;text-align:right;">自定义字段名称</span>
                                        <el-input v-model="item.name" style="width:60%;"></el-input>
                                    </div>
                                    <a style="color:#666;"><div class="delete" @click="deleteBlock(index)">x</div></a>
                                </div>
                            </div>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 自定义表单 -->
                    <div class="block">
                        <div class="vue-title" v-show="{!! app('plugins')->isEnabled('diyform') !!}">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">自定义表单</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%" v-show="{!! app('plugins')->isEnabled('diyform') !!}">
                            <el-form-item label="自定义表单">
                                <template>
                                    <el-select v-model="form.form_id" name="form_id" clearable>
                                        <el-option
                                                v-for="item in diyForm"
                                                :label="item.title"
                                                :value="item.id">
                                        </el-option>
                                    </el-select>
                                </template>
                            </el-form-item>
                            <el-form-item label="注册填写">
                                <template>
                                    <el-switch
                                            v-model="form.form_id_register"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div class="tip">默认开启，开启后用户在注册会员、绑定手机号时需要填写自定义字段信息，开启手机验证码快速登录不显示该信息！关闭则仅在设置-会员资料中显示！</div>
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

    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                let diyForm = {!! json_encode(($diyForm?:[])) !!};
                return {
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                    diyForm:diyForm,
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
                // 消息模板开关
                changeModal(name) {
                    let url_open = "{!! yzWebUrl('setting.default-notice.index') !!}"
                    let url_close = "{!! yzWebUrl('setting.default-notice.cancel') !!}"
                    let url = "";
                    if(this.form[name+'_default']==1) {
                        url = url_close;
                    } else {
                        url = url_open;
                    }
                    let json = {
                        notice_name: name,
                        setting_name: "relation_base",
                    }
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result == 1) {
                            this.$message({type: 'success',message: '操作成功!'});
                            if(this.form[name+'_default'] == 0) {
                                this.form[name+'_default'] = 1;
                                this.form[name] = response.data.id;
                            }  else {
                                this.form[name+'_default'] = 0;
                                this.form[name] = '';
                            }
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                            this.form[name+'_default'] = 0;
                        }
                        loading.close();
                    },response => {
                        loading.close();
                    });
                },
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.information-store') !!}';
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
                addBlock(){
                    this.form.form.push({name:"",sort:""});
                },
                deleteBlock(index){
                    this.form.form.splice(index,1)
                },
            }
        })
    </script>
    @endsection