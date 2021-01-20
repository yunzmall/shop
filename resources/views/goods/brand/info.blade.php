@extends('layouts.base')
@section('title', '品牌详情')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">商品品牌</a> > 品牌详情
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">品牌详情</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="品牌名称" prop="name">
                            <el-input v-model="form.name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="品牌别名" prop="alias">
                            <el-input v-model="form.alias" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="LOGO" prop="logo">
                            <div class="upload-box" @click="openUpload('logo')" v-if="!form.logo_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('logo')" class="upload-boxed" v-if="form.logo_url">
                                <img :src="form.logo_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传1</div>
                                <i class="el-icon-close" @click.stop="clearImg('logo')" title="点击清除图片"></i>

                            </div>
                            <div class="tip">建议尺寸: 100*100，或正方型图片</div>
                        </el-form-item>
                        
                        
                        <el-form-item label="是否推荐" prop="is_recommend">
                            <el-switch v-model="form.is_recommend" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="品牌描述" prop="desc">
                            <tinymceee v-model="form.desc" style="width:70%"></tinymceee>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>
            <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script> 
    <!-- <script src="{{resource_get('static/yunshop/tinymceTemplate.js')}}"></script> -->
    
    @include('public.admin.tinymceee')  
    @include('public.admin.uploadImg')  

    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                let id = {!! $id?:0 !!};
                console.log(id);
                return{
                    id:id,
                    form:{

                    },
                    uploadShow:false,
                    chooseImgName:'',
                    submit_url:'',
                    showVisible:false,
                    
                    loading: false,
                    uploadImg1:'',
                    rules:{
                        name:{ required: true, message: '请输入品牌名称'}
                    },

                }
            },
            created() {


            },
            mounted() {
                // this.getData();
                if(this.id) {
                    this.getData();
                    this.submit_url = '{!! yzWebFullUrl('goods.brand.edit') !!}';
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('goods.brand.add') !!}';
                }
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
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('goods.brand.index') !!}`;
                },
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.brand.edit') !!}',{id:this.id}).then(function (response) {
                            if (response.data.result){
                                this.form = {
                                    ...response.data.data,
                                };
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
                submitForm(formName) {
                    console.log(this.form)
                    let that = this;
                    let json = {
                        name:this.form.name,
                        alias:this.form.alias,
                        logo:this.form.logo,
                        is_recommend:this.form.is_recommend || 0,
                        desc:this.form.desc,
                    };
                    let json1 = {
                        brand:json
                    }
                    if(this.id) {
                        json1.id = this.id
                    }
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.submit_url,json1).then(response => {
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
                clearImg(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
                },
            },
        })

    </script>
@endsection


