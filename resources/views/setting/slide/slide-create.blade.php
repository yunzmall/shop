@extends('layouts.base')
@section('content')
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .content{
            background: #eff3f6;
            padding: 10px!important;
            padding-top:20px!important;
        }
        .con{
            padding-bottom:20px;
            position:relative;
            border-radius: 8px;
            background-color:#fff;
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

        .vue-crumbs a {
  color: #333;
}
.vue-crumbs a:hover {
  color: #29ba9c;
}
.vue-crumbs {
  margin: 0 20px;
  font-size: 14px;
  color: #333;
  font-weight: 400;
  padding-bottom: 10px;
}
    </style>
    <div id='re_content' >
    <div class="vue-crumbs">
                <a @click="goBack">系统</a> > 商城设置 > 幻灯片 > 添加幻灯片
    </div>
        <div class="con">
            <div class="setting">
            <el-form ref="form" :model="form" label-width="15%">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                        <el-form-item label="排序">
                            <el-input v-model="form.display_order" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="幻灯片标题">
                            <el-input v-model="form.slide_name"  style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="幻灯片图片" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('thumb',1,'one')" v-if="!form.thumb_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('thumb',1,'one')" class="upload-boxed" v-if="form.thumb_url" style="height:75px;">
                                <img :src="form.thumb_url" alt="" style="width:150px;height:75px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('thumb')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:640 * 350 , 请将所有幻灯片图片尺寸保持一致</div>
                        </el-form-item>
                        <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img> -->
                        <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                        <el-form-item label="幻灯片链接">
                            <el-input v-model="form.link"  style="width:70%;"></el-input><el-button @click="show=true" style="margin-left:10px;">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="幻灯片小程序链接">
                            <el-input v-model="form.small_link"  style="width:70%;"></el-input><el-button @click="pro=true" style="margin-left:10px;">选择小程序链接</el-button>
                        </el-form-item>
                        <el-form-item label="是否显示">
                            <template>
                                <el-switch
                                        v-model="form.enabled"
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
            <pop :show="show" @replace="changeProp1" @add="parHref"></pop>
            <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
            </el-form>
        </div>
    </div>
    @include('public.admin.pop')
    @include('public.admin.program')
    @include('public.admin.uploadMultimediaImg')
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    type:'',
                    selNum:'',
                    activeName: 'first',
                    show:false,//是否开启公众号弹窗
                    pro:false ,//是否开启小程序弹窗
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    form:{
                        display_order:'',
                        slide_name:'',
                        thumb:'',
                        link:'',
                        small_link:'',
                        enabled:'1'
                    },
                }
            },
            mounted () {
            },
            methods: {
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
                goBack() {
                window.location.href = `{!! yzWebFullUrl('setting.shop.index') !!}`;
                },
                openUpload(str,type,sel) {
                   this.chooseImgName = str;
                   this.uploadShow = true;
                   this.type = type;
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
                //弹窗显示与隐藏的控制
                changeProp1(item){
                    this.show=item;
                },
                //当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    this.form.link=child;

                },
                changeprogram(item){
                    this.pro=item;
                },
                parpro(child,confirm){
                    this.pro=confirm;
                    this.form.small_link=child;
                },
                submit() {
                    this.$http.post('{!! yzWebFullUrl('setting.slide.create') !!}',{'slide':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                            window.location.href='{!! yzWebFullUrl('setting.slide.index') !!}'
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection('content')

