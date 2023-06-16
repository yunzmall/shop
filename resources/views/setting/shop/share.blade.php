@extends('layouts.base')
@section('content')
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
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
            padding-bottom:20px;
            position:relative;
            min-height:100vh;
            background-color:#fff;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .con .setting .block .title{
            font-size:14px;
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
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>关注设置</b></div>
                        <el-form-item label="启用会员关注"  >
                            <template>
                                <el-radio-group v-model="form.follow">
                                    <el-radio label="1">是</el-radio>
                                    <el-radio label="0">否</el-radio>
                                </el-radio-group>
                            </template>
                        </el-form-item>
                        <el-form-item label="关注引导页">
                            <template>
                                <el-radio-group v-model="form.type">
                                    <el-radio label="1">链接</el-radio>
                                    <el-radio label="0">图片</el-radio>
                                </el-radio-group>
                            </template>
                            <div><el-input v-model="form.follow_url" placeholder="请输入链接" style="width:70%;" v-if="form.type=='1'"></el-input></div>
                            <div v-if="form.type=='1'" ><span class='help-block'>用户未关注的引导页面，建议使用短链接：<a target="_blank" href="https://dwz.cn">短网址</a></div>
                        </el-form-item>
                        <el-form-item label="分享图标" prop="head_img_url" v-if="form.type=='0'">
                            <div class="upload-box" @click="openUpload('follow_img',1,'one')" v-if="!form.follow_img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('follow_img',1,'one')" class="upload-boxed" v-if="form.follow_img_url">
                                <img :src="form.follow_img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                            </div>
                            <div class="tip">正方型图片</div>
                        </el-form-item>
                </el-form>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>分享设置</b></div>
                <el-form-item label="分享标题">
                    <el-input v-model="form.title" placeholder="请输入分享标题" style="width:70%;"></el-input>
                    <span class="help-block">不填写默认商城名称</span>
                </el-form-item>
                <el-form-item label="分享图标" prop="head_img_url">
                    <div class="upload-box" @click="openUpload('icon',1,'one')" v-if="!form.icon_url">
                        <i class="el-icon-plus" style="font-size:32px"></i>
                    </div>
                    <div @click="openUpload('icon',1,'one')" class="upload-boxed" v-if="form.icon_url">
                        <img :src="form.icon_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                        <div class="upload-boxed-text">点击重新上传</div>
                    </div>
                    <div class="tip">正方型图片</div>
                </el-form-item>
                <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img> -->
                <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                <el-form-item label="分享描述">
                    <el-input v-model="form.desc" type="textarea" placeholder="请输入分享描述" style="width:70%;"></el-input>
                </el-form-item>
                <!--
                <el-form-item label="分享链接">
                    <el-input v-model="form.url"  placeholder="请填写指向的链接" style="width:70%;"></el-input><el-button @click="show=true" style="margin-left:10px;">选择链接</el-button>
                    <span class='help-block'>用户分享出去的链接，默认为首页</span>
                </el-form-item>
                -->
            </div>
        </div>
        <div class="confirm-btn">
            <el-button type="primary" @click="submit">提交</el-button>
        </div>
        <pop :show="show" @replace="changeProp1" @add="parHref"></pop>
        </el-form>
    </div>
    </div>
    @include('public.admin.pop')
    @include('public.admin.uploadMultimediaImg')

    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    type:'',
                    selNum:'',
                    activeName: 'one',
                    show:false,
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    form:{
                        follow_img:'',
                        follow:'1',
                        type:'1',
                        follow_url:'',
                        title:'',
                        icon:'',
                        desc:'',
                        url:'',
                    },
                }
            },
            mounted () {
                this.getData();
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
                //   弹窗显示与隐藏的控制
                changeProp1(item){
                    this.show=item;
                },
                // 当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    this.form.url=child;

                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.share') !!}').then(function (response){
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
                    this.$http.post('{!! yzWebFullUrl('setting.shop.share') !!}',{'share':this.form}).then(function (response){
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
