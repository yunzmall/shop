@extends('layouts.base')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
         .main-panel{
            margin-top:50px;
        }
        .main-panel #re_content {
            padding: 10px;
        }
        .panel{
            margin-bottom:10px!important;
            padding-left: 20px;
            border-radius: 10px;
        }
        .panel .active a {
            background-color: #29ba9c !important;
            border-radius: 18px !important;
            color: #fff;
        }

        .panel a {
            border: none !important;
            background-color: #fff !important;
        }

        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .con {
            padding-bottom: 20px;
            position: relative;
            border-radius: 8px;
            min-height: 100vh;
            background-color: #fff;
        }

        .con .setting .block {
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
        }

        .con .setting .block .title {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .confirm-btn {
            width: calc(100% - 266px);
            position: fixed;
            bottom: 0;
            right: 0;
            margin-right: 10px;
            line-height: 63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px rgba(51, 51, 51, 0.3);
            background-color: #fff;
            text-align: center;
        }

        b {
            font-size: 14px;
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
    <div id='re_content'>
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"><span
                                    style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b>
                        </div>
                        <el-form-item label="注册/绑定手机页面顶部图片">
                            <div class="upload-box" @click="openUpload('top_img',1,'one')" v-if="!form.top_img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('top_img',1,'one')" class="upload-boxed" v-if="form.top_img_url"
                                 style="height:150px;">
                                <img :src="form.top_img_url" alt=""
                                     style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('top_img')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸640*320</div>
                        </el-form-item>
                        <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp"
                                    @sure="sureImg"></upload-img> -->
                        <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                        <el-form-item label="是否设置密码">
                            <template>
                                <el-switch
                                        v-model="form.is_password"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="小程序引导授权主标题">
                            <el-input v-model="form.title1" style="width:70%;" placeholder="单行输入"></el-input>
                            <div style="font-size:12px;">默认为 "欢迎来到[商城名称]"</div>
                        </el-form-item>
                        <el-form-item label="小程序引导授权副标题">
                            <el-input v-model="form.title2" style="width:70%;" placeholder="单行输入"></el-input>
                            <div style="font-size:12px;">默认为 "登录尽享各种优惠权益！"</div>
                        </el-form-item>
                    </div>
                    <div class="block">
                        <div class="title"><span
                                    style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b>
                        </div>
                        <el-form-item label="是否启用">
                            <template>
                                <el-switch
                                        v-model="form.protocol.status"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="协议标题">
                            <el-input v-model="form.protocol.title" style="width:70%;" placeholder="会员注册协议"></el-input>
                        </el-form-item>
                        <el-form-item label="协议内容">
                            <tinymceee v-model="form.protocol.content" style="width:70%;"></tinymceee>
                        </el-form-item>
                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
            </el-form>
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    @include('public.admin.uploadMultimediaImg')
    @include('public.admin.tinymceee')
    
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let register={!!json_encode($register?:'{}') !!}
                let protocol={!!json_encode($protocol?:'{}') !!}
                return {
                    type:'',
                    selNum:'',
                    activeName: 'one',
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    form: {
                        top_img :register&&register.top_img ? register.top_img:'',
                        top_img_url :register&&register.top_img_url ? register.top_img_url:'',
                        is_password :register.hasOwnProperty('is_password')?register.is_password:'1',
                        title1 :register&&register.title1 ? register.title1:'',
                        title2 :register&&register.title2 ? register.title2:'',
                        protocol:{
                            title:protocol&&protocol.title ? protocol.title:'',
                            status:protocol.hasOwnProperty('status')?protocol.status:1,
                            content:protocol&&protocol.content ? protocol.content:'',

                    },

                },
                }
            },
            mounted() {
            },
            methods: {
                clearImg(str, type, index) {
                    if (!type) {
                        this.form[str] = "";
                        this.form[str + '_url'] = "";
                    }
                    else {
                        this.form[str].splice(index, 1);
                        this.form[str + '_url'].splice(index, 1);
                    }
                    this.$forceUpdate();
                },
                submit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('setting.shop.register') !!}', {'register': this.form}).then(function (response) {
                        if (response.data.result) {
                            this.$message({message: response.data.msg, type: 'success'});
                        } else {
                            this.$message({message: response.data.msg, type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    }, function (response) {
                        this.$message({message: response.data.msg, type: 'error'});
                    })
                },
                openUpload(str,type,sel) {

                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type
                    this.selNum = sel
                },
                changeProp(val) {
                    if (val == true) {
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

            },
        });
    </script>
@endsection('content')
