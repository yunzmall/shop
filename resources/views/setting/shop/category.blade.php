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
            border-radius: 8px;
            min-height:100vh;
            background-color:#fff;
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
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                        <el-form-item label="分类级别"  >
                            <template>
                                <el-radio-group v-model="form.cat_level">
                                    <el-radio label="2">二级</el-radio>
                                    <el-radio label="3">三级</el-radio>
                                </el-radio-group>
                            </template>
                        </el-form-item>
                        <el-form-item label="推荐品牌是否隐藏">
                            <template>
                                <el-switch
                                        v-model="form.cat_brand"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="推荐分类是否隐藏"  >
                            <template>
                                <el-switch
                                        v-model="form.cat_class"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="分类绑定规格"  >
                            <template>
                                <el-switch
                                        v-model="form.category_to_option"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div class="tip">不同分类绑定的规格前端进入分类页显示对应规格图片，进入详情页默认选中该规格并显示规格图片+其他图片，如果没有绑定规格按照原来展示</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>推荐分类页面广告设置</b></div>
                        <el-form-item label="推荐分类广告" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('cat_adv_img',1,'one')" v-if="!form.cat_adv_img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('cat_adv_img',1,'one')" class="upload-boxed" v-if="form.cat_adv_img_url" style="height:75px;">
                                <img :src="form.cat_adv_img_url" alt="" style="width:150px;height:75px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('cat_adv_img')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">分类页面中，推荐分类的广告图，建议尺寸640*320</div>
                        </el-form-item>
                        <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img> -->
                        <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                        <el-form-item label="推荐分类广告链接">
                            <el-input v-model="form.cat_adv_url" placeholder="请填写指向的链接" style="width:60%;"></el-input><el-button @click="show=true" style="margin-left:10px;">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="小程序链接">
                            <el-input v-model="form.small_cat_adv_url" placeholder="请填写指向的链接" style="width:60%;"></el-input><el-button @click="pro=true" style="margin-left:10px;">选择小程序链接</el-button>
                        </el-form-item>
                </el-form>
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
    @include('public.admin.uploadMultimediaImg')
    @include('public.admin.pop')
    @include('public.admin.program')
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    type:'',
                    selNum:'',
                    activeName: 'one',
                    show:false,//是否开启公众号弹窗
                    pro:false ,//是否开启小程序弹窗
                    level:[],
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    form:{
                        cat_brand:'1',
                        cat_class:'1',
                        cat_level:'2',
                        cat_adv_img:'',
                        cat_adv_url:'',
                        small_cat_adv_url:'',
                        category_to_option:'0',
                    },
                }
            },
            mounted () {

                this.getData();
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
                openUpload(str,type,sel) {
                    console.log(type,'111111111111111')
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
                //弹窗显示与隐藏的控制
                changeProp1(item){
                    this.show=item;
                },
                //当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    this.form.cat_adv_url=child;

                },
                changeprogram(item){
                    this.pro=item;
                },
                parpro(child,confirm){
                    this.pro=confirm;
                    this.form.small_cat_adv_url=child;
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.category') !!}').then(function (response){
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
                    this.$http.post('{!! yzWebFullUrl('setting.shop.category') !!}',{'category':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        loading.close();
                    })
                },
            },
        });
    </script>
@endsection
