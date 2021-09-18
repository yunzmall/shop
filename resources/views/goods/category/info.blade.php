@extends('layouts.base')
@section('title', '商品分类详情')
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
                <a @click="goParent">商品分类</a> > 商品分类详情
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">商品分类详情</div>
                </div>
                <div class="vue-main-form">
                    
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="分类链接（点击复制）" v-if="id">
                            <el-input v-model="link" ref="link" class="link1" style="width:70%;opacity: 0;width: 5px;" readonly></el-input>
                            <a @click="copyLink('link')" style="color:#29BA9C;font-weight:400">[[link]]</a>
                        </el-form-item>
                        <el-form-item label="上级分类" v-if="parent&&parent.name">
                            <el-input v-model="parent.name" style="width:70%;" placeholder="请输入排序" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="排序" prop="display_order">
                            <el-input v-model="form.display_order" style="width:70%;" placeholder="请输入排序"></el-input>
                        </el-form-item>
                        <el-form-item label="分类名称" prop="name">
                            <el-input v-model="form.name" style="width:70%;" placeholder="请输入分类名称"></el-input>
                        </el-form-item>
                        <el-form-item label="分类图片" prop="thumb">
                            <!-- 传1:表示图片类型 -->
                            <div class="upload-box" @click="openUpload('thumb',1,'one')" v-if="!form.thumb_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('thumb',1,'one')" class="upload-boxed" v-if="form.thumb_url">
                                <img :src="form.thumb_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('thumb')" title="点击清除图片"></i>

                            </div>
                            <div class="tip">建议尺寸: 100*100，或正方型图片</div>
                        </el-form-item>
                        <!-- <el-form-item label="分类描述" prop="description">
                            <el-input type="textarea" rows="5" v-model="form.description" style="width:70%;"></el-input>
                            <div class="tip">如果不填写，默认为空</div>
                        </el-form-item> -->
                        <div v-if="level!=3 && edit_level!=3">
                            <el-form-item label="移动端分类广告" prop="adv_img">
                                <div class="upload-box" @click="openUpload('adv_img',1,'one')" v-if="!form.adv_img_url">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('adv_img',1,'one')" class="upload-boxed" v-if="form.adv_img_url" style="height:75px">
                                    <img :src="form.adv_img_url" alt="" style="width:150px;height:75px;border-radius: 5px;cursor: pointer;">
                                    <div class="upload-boxed-text">点击重新上传</div>
                                    <i class="el-icon-close" @click.stop="clearImg('adv_img')" title="点击清除图片"></i>
                                </div>
                                <div class="tip">建议尺寸: 640*320</div>
                            </el-form-item>
                            <el-form-item label="分类广告链接" prop="adv_url">
                                <el-input v-model="form.adv_url" style="width:70%;" placeholder=" 请填写指向的链接 (请以https://开头, 不填则不显示)"></el-input>
                                <el-button  @click="showLink('link','adv_url')">选择链接</el-button>
                            </el-form-item>
                            <el-form-item label="分类广告小程序链接" prop="small_adv_url">
                                <el-input v-model="form.small_adv_url" style="width:70%;"></el-input>
                                <el-button   @click="showLink('mini','small_adv_url')">选择小程序链接</el-button>
                            </el-form-item>
                        </div>
                        
                        <el-form-item label="关联标签组" prop="banner">
                            <el-input v-model="form.filter" disabled style="width:70%;"></el-input>
                            <el-button @click="filterShow = true">选择标签组</el-button>
                            <!-- filter_ids -->
                            <div >
                                <el-tag v-for="(tag,index) in filter_names" :key="index" closable @close="closeFilter(index)">
                                    [[tag]]
                                </el-tag>
                            </div>
                        </el-form-item>
                        
                        <el-form-item label="是否推荐" prop="is_home">
                            <el-switch v-model="form.is_home" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="是否显示" prop="enabled">
                            <el-switch v-model="form.enabled" :active-value="1" :inactive-value="0"></el-switch>
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
            <el-dialog :visible.sync="filterShow" width="60%" center title="选择标签组">
                <div v-loading="loading">
                    <div>
                        <el-input v-model="keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchFilter()">搜索</el-button>
                    </div>
                    <el-table :data="filter_list" style="width: 100%;height:500px;overflow:auto">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="标签组名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.name]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="sureFilter(scope.row)">
                                    选择
                                </el-button>
                                
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="filterShow = false">取 消</el-button>
                </span>
            </el-dialog>
   
            <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
            <pop :show="show" @replace="changeLink" @add="parHref"></pop>
            <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>

            <!--end-->
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script> 
    @include('public.admin.uploadMultimediaImg')
    @include('public.admin.pop')  
    @include('public.admin.program')
    <script>
        let id = {!! $id?:0 !!}
        let level = {!! $level?:0 !!}
        let parent_id = {!! $parent_id?:0 !!}
        console.log(level)
        console.log(parent_id)
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
                    id:id,
                    level:level,
                    parent_id:parent_id,
                    edit_parent_id:0,
                    edit_level:0,
                    parent:{},
                    form:{
                        filter_ids:[],
                        display_order:'0',
                        name:'',
                        thumb:'',
                        description:'',
                        adv_img:'',
                        adv_url:'',
                        small_adv_url:'',
                        is_home:0,
                        enabled:0,

                    },
                    filter_names:[],//选中的标签组名
                    filter_list:[],
                    filterShow:false,
                    keyword:'',
                    submit_url:'',
                    link:"",

                    uploadShow:false,
                    chooseImgName:'',
                    
                    loading: false,
                    uploadImg1:'',
                    rules:{
                        name:{ required: true, message: '请输入分类名称'}
                    },
                    type:'',
                    selNum:'',
                    
                }
            },
            created() {
                if(this.id) {
                    this.submit_url = '{!! yzWebFullUrl('goods.category.edit-category') !!}'
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('goods.category.add-category') !!}'
                }
                this.getData();


            },
            mounted() {
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
                
                clearGoods() {
                    
                },
                searchFilter() {
                    let that = this;
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('filtering.filtering.get-search-label-v2') !!}',{keyword:this.keyword}).then(response => {
                        if (response.data.result) {
                            this.filter_list = response.data.data;
                            
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                    },response => {
                        loading.close();
                    });
                },
                sureFilter(item) {
                    let is_exist = 0;
                    this.form.filter_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.form.filter_ids.push(item.id)
                    this.filter_names.push(item.name)
                    
                    // this.filterShow = false;
                },
                closeFilter(index) {
                    this.form.filter_ids.splice(index,1);
                    this.filter_names.splice(index,1);
                },
                submitForm(formName) {
                    let that = this;
                    let json = {
                        level:this.level,
                        parent_id:this.parent_id,
                        category:{
                            display_order:this.form.display_order,
                            name:this.form.name,
                            thumb:this.form.thumb,
                            description:this.form.description,
                            adv_img:this.form.adv_img,
                            adv_url:this.form.adv_url,
                            small_adv_url:this.form.small_adv_url,
                            is_home:this.form.is_home,
                            enabled:this.form.enabled,
                            filter_ids:this.form.filter_ids,
                            level:this.level,
                            parent_id:this.parent_id,
                        }
                    };
                    if(this.id) {
                        json.id = this.id;
                        json.category.level = this.edit_level;
                        json.category.parent_id = this.edit_parent_id;
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
                    window.location.href = `{!! yzWebFullUrl('goods.category.index') !!}`;
                },
                openUpload(str,type,sel) {
                    console.log(str, type,'uuuuuuuuuuu');
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

               
                // 参数：fileList  上传文件的列表信息
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