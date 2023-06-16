@extends('layouts.base')
@section('title', '优惠券幻灯片编辑')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<link rel="stylesheet" href="{{static_url('css/public-number.css')}}">

<style>
    .dialog-cover{z-index:2001}
    .dialog-content{z-index:2002}
    .link1 .el-input__inner{padding:0}
</style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">优惠券幻灯片管理</a> > 幻灯片编辑
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">幻灯片编辑</div>
                </div>
                <div class="vue-main-form">
                    
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        
                        <el-form-item label="排序" prop="sort">
                            <el-input v-model="form.sort" style="width:70%;" placeholder="请输入排序"></el-input>
                        </el-form-item>
                        <el-form-item label="幻灯片标题" prop="title">
                            <el-input v-model="form.title" style="width:70%;" placeholder="请输入幻灯片标题"></el-input>
                        </el-form-item>
                        <el-form-item label="幻灯片图片" prop="slide_pic">
                            <div class="upload-box" @click="openUpload('slide_pic',1,'one')" v-if="!form.slide_pic_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('slide_pic',1,'one')" class="upload-boxed" v-if="form.slide_pic_url">
                                <img :src="form.slide_pic_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                            </div>
                            <div class="tip">建议尺寸: 长方型图片</div>
                        </el-form-item>
                        <el-form-item label="幻灯片链接" prop="slide_link">
                            <el-input v-model="form.slide_link" style="width:70%;" placeholder=" 请填写指向的链接 (请以https://开头, 不填则不显示)"></el-input>
                            <el-button  @click="showLink('link','slide_link')">选择链接</el-button>
                        </el-form-item>
                        <el-form-item label="小程序链接" prop="mini_link">
                            <el-input v-model="form.mini_link" style="width:70%;"></el-input>
                            <el-button   @click="showLink('mini','mini_link')">选择小程序链接</el-button>
                        </el-form-item>
                        <el-form-item label="是否显示" prop="is_show">
                            <el-switch v-model="form.is_show" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>
            
            <upload-multimedia-img 
                :upload-show="uploadShow" 
                :type="type" 
                :name="chooseImgName" 
                :sel-Num="selNum"
                @replace="changeProp" 
                @sure="sureImg"
                >
        </upload-multimedia-img>
            <pop :show="show" @replace="changeLink" @add="parHref"></pop>
            <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>

            <!--end-->
        </div>
    </div>
    @include('public.admin.uploadMultimediaImg')
    @include('public.admin.pop')  
    @include('public.admin.program')
    <script>
        let data = {!! $data?:'{}' !!}
        console.log(data)
        let form = {
            sort:"",
            title:"",
            slide_pic:"",
            slide_link:"",
            mini_link:"",
            is_show:0,
        }
        if(data.id) {
            form = {
                sort:data.sort,
                title:data.title,
                slide_pic:data.slide_pic,
                slide_link:data.slide_link,
                mini_link:data.mini_link,
                is_show:data.is_show,
                slide_pic_url:data.pic_url,
            }
        }
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    show:false,//是否开启公众号弹窗
                    pro:false ,//是否开启小程序弹窗 
                    chooseLink:'',
                    chooseMiniLink:'',
                    id:0,
                    form:form,
                    submit_url:'',
                    link:"",

                    uploadShow:false,
                    chooseImgName:'',
                    
                    loading: false,
                    uploadImg1:'',
                    rules:{
                        title:{ required: true, message: '请输入标题'}
                    },
                    type:'',
                    selNum:'',
                    
                }
            },
            created() {
                this.id = this.getParam("id");
                if(this.id) {
                    this.submit_url = '{!! yzWebFullUrl('coupon.slide-show.edit') !!}'
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('coupon.slide-show.add') !!}'
                }
                // this.getData();
            },
            mounted() {
            },
            methods: {
                //弹窗显示与隐藏的控制
                changeLink(item){
                    this.show=item;
                },
                //当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    // this.form.link=child;
                    this.form[this.chooseLink] = child;
                },
                changeprogram(item){
                    this.pro=item;
                },
                parpro(child,confirm){
                    this.pro=confirm;
                    // this.form.prolink=child;
                    this.form[this.chooseMiniLink] = child;

                },
                showLink(type,name) {
                    if(type=="link") {
                        this.chooseLink = name;
                        this.show = true;
                    }
                    else {
                        this.chooseMiniLink = name;
                        this.pro = true;
                    }
                },
                getData() {
                    let json = {};
                    if(this.id) {
                        json.id = this.id
                    }
                    else {
                        json = {
                            level:this.level,
                            parent_id:this.parent_id
                        }
                    }
                    console.log(json)
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(this.submit_url,json).then(function (response) {
                            if (response.data.result){
                                if(this.id) {
                                    let datas = response.data.data.item;
                                    this.form.display_order = datas.display_order;
                                    this.form.name = datas.name;
                                    this.form.thumb = datas.thumb;
                                    this.form.thumb_url = datas.thumb_url;
                                    this.form.description = datas.description;
                                    this.form.adv_img = datas.adv_img;
                                    this.form.adv_img_url = datas.adv_img_url;
                                    this.form.adv_url = datas.adv_url;
                                    this.form.small_adv_url = datas.small_adv_url;
                                    this.form.is_home = datas.is_home || 0;
                                    this.form.enabled = datas.enabled || 0;
                                    this.edit_level = datas.level || 0;
                                    this.edit_parent_id = datas.parent_id || 0;
                                    if(response.data.data.label_group && response.data.data.label_group.length) {
                                        response.data.data.label_group.forEach((item,index) => {
                                            this.form.filter_ids.push(item.id);
                                            this.filter_names.push(item.name);
                                        })
                                    }
                                    this.link = response.data.data.link || "";
                                }
                                this.parent = response.data.data.parent;
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    );
                },
                getParam(name) { 
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
                    var r = window.location.search.substr(1).match(reg); 
                    if (r != null) return unescape(r[2]); 
                    return null; 
                },
                
                clearGoods() {
                    
                },
                submitForm(formName) {
                    let that = this;
                    let json = {
                        data:{
                            sort:this.form.sort,
                            title:this.form.title,
                            slide_pic:this.form.slide_pic,
                            slide_link:this.form.slide_link,
                            mini_link:this.form.mini_link,
                            is_show:this.form.is_show,
                        }
                    };
                    if(this.id) {
                        json.id = this.id;
                    }
                    
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.submit_url,json).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    this.goBack();
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                            },response => {
                                loading.close();
                            });
                        }
                        else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                
                goBack() {
                    history.go(-1)
                },
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('coupon.slide-show') !!}`;
                },
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type;
                    this.selNum = sel;
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
                clearImg(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
                },
                copyLink(type) {
                    this.$refs[type].select();
                    document.execCommand("Copy")
                    this.$message.success("复制成功!");
                },
                // 字符转义
                escapeHTML(a) {
                    a = "" + a;
                    return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
                },
            },
        })

    </script>
@endsection