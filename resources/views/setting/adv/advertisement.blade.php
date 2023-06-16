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
        .el-button{
            margin-left:10px;
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
                            <b>广告位一
                            </b></div>
                        <el-form-item label="广告位一" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('1','one')" v-if="!form.advs[1].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('1','one')" class="upload-boxed" v-if="form.advs[1].img_url" >
                                <img :src="form.advs['1'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('1')" title="点击清除图片"></i>

                            </div>
                            <div class="tip">建议尺寸:339 * 282</div>
                        </el-form-item>
                        <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum"  @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                        <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img> -->
                        <el-form-item label="广告位一链接">
                            <el-input v-model="form.advs[1].link"  style="width:60%;"></el-input><el-button @click="openConnect('1')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位一小程序链接">
                            <el-input v-model="form.advs[1].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('1')">选择小程序链接</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>广告位二
                            </b></div>
                        <el-form-item label="广告位二" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('2','one')" v-if="!form.advs[2].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('2','one')" class="upload-boxed" v-if="form.advs[2].img_url" >
                                <img :src="form.advs['2'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('2')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:339 * 135</div>
                        </el-form-item>
                        <el-form-item label="广告位二链接">
                            <el-input v-model="form.advs[2].link"  style="width:60%;"></el-input><el-button @click="openConnect('2')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位二小程序链接">
                            <el-input v-model="form.advs[2].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('2')">选择小程序链接</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>广告位三
                            </b></div>
                        <el-form-item label="广告位三" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('3','one')" v-if="!form.advs[3].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('3','one')" class="upload-boxed" v-if="form.advs[3].img_url" >
                                <img :src="form.advs['3'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('3')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:339 * 135</div>
                        </el-form-item>
                        <el-form-item label="广告位三链接">
                            <el-input v-model="form.advs[3].link"  style="width:60%;"></el-input><el-button @click="openConnect('3')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位三小程序链接">
                            <el-input v-model="form.advs[3].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('3')">选择小程序链接</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>广告位四
                            </b></div>
                        <el-form-item label="广告位四" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('4','one')" v-if="!form.advs[4].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('4','one')" class="upload-boxed" v-if="form.advs[4].img_url" >
                                <img :src="form.advs['4'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('4')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:223 * 260</div>
                        </el-form-item>
                        <el-form-item label="广告位四链接">
                            <el-input v-model="form.advs[4].link"  style="width:60%;"></el-input><el-button @click="openConnect('4')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位四小程序链接">
                            <el-input v-model="form.advs[4].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('4')">选择小程序链接</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>广告位五
                            </b></div>
                        <el-form-item label="广告位五" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('5','one')" v-if="!form.advs[5].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('5','one')" class="upload-boxed" v-if="form.advs[5].img_url" >
                                <img :src="form.advs['5'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('5')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:223 * 260</div>
                        </el-form-item>
                        <el-form-item label="广告位五链接">
                            <el-input v-model="form.advs[5].link"  style="width:60%;"></el-input><el-button @click="openConnect('5')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位五小程序链接">
                            <el-input v-model="form.advs[5].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('5')">选择小程序链接</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>广告位六
                            </b></div>
                        <el-form-item label="广告位六" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('6','one')" v-if="!form.advs[6].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('6','one')" class="upload-boxed" v-if="form.advs[6].img_url" >
                                <img :src="form.advs['6'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('6')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:223 * 260</div>
                        </el-form-item>
                        <el-form-item label="广告位六链接">
                            <el-input v-model="form.advs[6].link"  style="width:60%;"></el-input><el-button @click="openConnect('6')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位六小程序链接">
                            <el-input v-model="form.advs[6].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('6')">选择小程序链接</el-button>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>广告位七
                            </b></div>
                        <el-form-item label="广告位七" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('7','one')" v-if="!form.advs[7].img_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('7','one')" class="upload-boxed" v-if="form.advs[7].img_url" >
                                <img :src="form.advs['7'].img_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('7')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">建议尺寸:223 * 260</div>
                        </el-form-item>
                        <el-form-item label="广告位七链接">
                            <el-input v-model="form.advs[7].link"  style="width:60%;"></el-input><el-button @click="openConnect('7')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="广告位七小程序链接">
                            <el-input v-model="form.advs[7].small_link"   style="width:60%;"></el-input><el-button @click="openSmall('7')">选择小程序链接</el-button>
                        </el-form-item>
                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary"  @click="submit">提交</el-button>
            </div>
            <pop :show="show" @replace="changeProp1" @add="parHref"></pop>
            <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
            </el-form>
        </div>
    </div>
    <!-- @include('public.admin.uploadImg') -->
    @include('public.admin.uploadMultimediaImg')
    @include('public.admin.pop')
    @include('public.admin.program')
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let adv = {!! $adv ?: '[]'  !!}
                    return {
                        type:'',
                        selNum:'',
                        typeImg:1,
                        activeName: 'first',
                        uploadShow:false,
                        chooseImgName:'',
                        uploadListShow:false,
                        chooseImgListName:'',
                        str:'',
                        smStr:'',
                        show:false,//是否开启公众号弹窗
                        pro:false ,//是否开启小程序弹窗
                        form:{
                            advs:[
                                {
                                    //兼容旧数据0留空
                                },
                                {
                                    img: adv?adv.advs['1'].img:'',
                                    link: adv?adv.advs['1'].link:'',
                                    small_link: adv?adv.advs['1'].small_link:'',
                                    img_url:adv?adv.advs['1'].img_url:''
                                },
                                {
                                    img: adv?adv.advs['2'].img:'',
                                    link: adv?adv.advs['2'].link:'',
                                    small_link:adv?adv.advs['2'].small_link:'',
                                    img_url:adv?adv.advs['2'].img_url:''
                                },
                                {
                                    img: adv?adv.advs['3'].img:'',
                                    link: adv?adv.advs['3'].link:'',
                                    small_link: adv?adv.advs['3'].small_link:'',
                                    img_url:adv?adv.advs['3'].img_url:''
                                },
                                {
                                    img: adv?adv.advs['4'].img:'',
                                    link: adv?adv.advs['4'].link:'',
                                    small_link: adv?adv.advs['4'].small_link:'',
                                    img_url:adv?adv.advs['4'].img_url:''
                                },
                                {
                                    img: adv?adv.advs['5'].img:'',
                                    link: adv?adv.advs['5'].link:'',
                                    small_link:adv?adv.advs['5'].small_link:'',
                                    img_url:adv?adv.advs['5'].img_url:''
                                },
                                {
                                    img: adv?adv.advs['6'].img:'',
                                    link: adv?adv.advs['6'].link:'',
                                    small_link:adv?adv.advs['6'].small_link:'',
                                    img_url:adv?adv.advs['6'].img_url:''
                                },
                                {
                                    img: adv?adv.advs['7'].img:'',
                                    link:adv?adv.advs['7'].link:'',
                                    small_link:adv?adv.advs['7'].small_link:'',
                                    img_url:adv?adv.advs['7'].img_url:''
                                },

                            ],
                        },
                    }
            },
            mounted () {
            },
            methods: {
                clearImg(str,type,index) {
                    if(!type) {
                        this.form.advs[str].img = "";
                        this.form.advs[str].img_url = "";
                    }
                    else {
                        this.form[str].splice(index,1);
                        this.form[str+'_url'].splice(index,1);
                    }
                    this.$forceUpdate();
                },
                openConnect(id){
                    this.show=true;
                    this.str=id
                },
                openSmall(smId){
                    this.pro=true,
                        this.smStr=smId
                },
                //弹窗显示与隐藏的控制
                changeProp1(item){
                    this.show=item;
                },
                //当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    this.form.advs[this.str].link=child;

                },
                changeprogram(item){
                    this.pro=item;
                },
                parpro(child,confirm){
                    this.pro=confirm;
                    this.form.advs[this.smStr].small_link=child;
                },
                openUpload(str,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = this.typeImg
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
                // sureImg(name,image,image_url) {
                //     console.log(name)
                //     this.form.advs[name].img=image
                //     this.form.advs[name].img_url= image_url;
                //     console.log(this.form)
                // },
                sureImg(name,uploadShow,fileList) {
                    
                    if(fileList.length <= 0) {
                        return 
                    }
                    console.log(name)
                    console.log(fileList)
                    this.form.advs[name].img =fileList[0].attachment;
                    this.form.advs[name].img_url = fileList[0].url;
                    console.log(this.form.advs[name].img,'aaaaa')
                    console.log(this.form.advs[name].img_url,'bbbbb')
                },
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop-advs.index') !!}',{'adv':this.form.advs}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: "提交成功",type: 'success'});
                        }
                        else {
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
