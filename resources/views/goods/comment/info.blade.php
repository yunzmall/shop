@extends('layouts.base')
@section('title', '评论详情')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">评论管理</a> > 编辑评论
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">编辑评论</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="选择商品" prop="goods">
                            <el-input v-model="chooseGoodsItem.content" style="width:70%;" disabled></el-input>
                            <el-button @click="openGoods()" type="primary">选择商品</el-button>
                            <el-button  type="primary" @click="clearGoods()" plain>清空</el-button>
                            <div style="width:150px;height:150px;position:relative;margin-top:10px" v-if="chooseGoodsItem.thumb">
                                <img :src="chooseGoodsItem.thumb" onerror="this.src='/addons/yun_shop/static/resource/images/nopic.jpg'; this.title='图片未找到.'" style="width:150px;height:150px">
                            </div>
                        </el-form-item>
                        <el-form-item label="用户头像" prop="head_img_url">
                            <!-- 1: 表示需要上传的类型是图片 -->
                            <div class="upload-box" @click="openUpload('head_img_url',1,'one')" v-if="!form.head_img_url_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('head_img_url',1,'one')" class="upload-boxed" v-if="form.head_img_url_url">
                                <img :src="form.head_img_url_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click="clearImg('head_img_url')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">用户头像，如果不选择，默认从粉丝表中随机读取</div>
                        </el-form-item>
                        <el-form-item label="用户昵称" prop="nick_name">
                            <el-input v-model="form.nick_name" style="width:70%;"></el-input>
                            <div class="tip">用户昵称，如果不填写，默认从粉丝表中随机读取</div>
                        </el-form-item>
                        <el-form-item label="会员等级">
                            <template>
                                <el-select v-model="form.level_set" placeholder="请选择会员等级">
                                    <el-option
                                            v-for="(item,i) in levels"
                                            :label="item.level_name"
                                            :value="item.id"
                                            :key="i">

                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="评分等级" prop="level">
                            <div style="padding-top:10px">
                                <el-rate v-model="form.level" show-score></el-rate>
                            </div>
                        </el-form-item>

                        <el-form-item v-show="form.is_score_latitude" label="描述/包装" prop="score_latitude_describe">
                            <div style="padding-top:10px">
                                <el-rate v-model="form.score_latitude.score_latitude_describe" show-score></el-rate>
                            </div>
                        </el-form-item>

                        <el-form-item v-show="form.is_score_latitude" label="物流服务/配送" prop="score_latitude_delivery">
                            <div style="padding-top:10px">
                                <el-rate v-model="form.score_latitude.score_latitude_delivery" show-score></el-rate>
                            </div>
                        </el-form-item>

                        <el-form-item v-show="form.is_score_latitude" label="服务态度/质量" prop="score_latitude_service">
                            <div style="padding-top:10px">
                                <el-rate v-model="form.score_latitude.score_latitude_service" show-score></el-rate>
                            </div>
                        </el-form-item>

                        <el-form-item label="设置评论时间">
                            <template><el-switch v-model="form.time_state" :active-value="1" :inactive-value="0"></el-switch></template>
                        </el-form-item>
                        <el-form-item v-if="form.time_state == 1" label="评论时间" >
                            <el-date-picker
                                        v-model="form.comment_time"
                                        type="datetime"
                                        placeholder="请输入自定义评论时间"
                                        value-format="timestamp"
                            >
                            </el-date-picker>
                        </el-form-item>


                        <el-form-item label="首次评论" prop="content">
                            <el-input type="textarea" rows="5" v-model="form.content" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="评论图片" prop="alias">
                            
                            <div class="upload-boxed-list">
                                <div class="upload-boxed-list-a" style="" v-for="(item,index) in form.images_url" :key="index">
                                    <img :src="item" alt="" style="width:150px;height:150px;border-radius: 5px;">
                                    <i class="el-icon-close" @click="clearImg('images','list',index)" title="点击清除图片"></i>
                                </div>
                                <div class="upload-box" @click="openUpload('images',1,'more')">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <!-- <div class="upload-boxed-text">点击重新上传</div> -->
                            </div>
                            <!-- <el-input style="width:70%;" disabled></el-input>
                            <el-button @click="openListUpload('images')">选择图片</el-button>
                            <div v-if="form.images_url">
                                <div v-for="(item,index) in form.images_url" :key="index" style="display:inline-block;margin:5px;width:150px;height:150px;position:relative;margin-top:10px">
                                    <img :src="item" alt="" style="width:150px;height:150px">
                                    <i
                                        class="el-icon-circle-close"
                                        @click="clearImg('images','list',index)"
                                        title="点击清除图片"
                                    ></i>
                                </div>
                            </div> -->
                        </el-form-item>

                        <el-form-item label="显示" prop="is_show">
                            <el-switch v-model="form.is_show" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>

                        <el-form-item label="置顶" prop="is_top">
                            <el-switch v-model="form.is_top" :active-value="1" :inactive-value="0"></el-switch>
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
            
            <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img> -->
            <!-- <upload-img-list :upload-list-show="uploadListShow" :name="chooseImgListName" @replace="changeListProp" @sure="sureImgList"></upload-img-list> -->
            <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>

            <el-dialog :visible.sync="goodsShow" width="60%" center title="选择商品">
                <div v-loading="goods_loading">
                    <div>
                        <el-input v-model="keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchGoods()">搜索</el-button>
                    </div>
                    <el-table :data="goods_list" style="width: 100%;height:500px;overflow:auto">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="商品信息">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <img v-if="scope.row.thumb" :src="scope.row.thumb" onerror="this.src='/addons/yun_shop/static/resource/images/nopic.jpg'; this.title='图片未找到.'" style="width:50px;height:50px"></img>
                                    <div style="margin-left:10px">[[scope.row.title]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="sureGoods(scope.row)">
                                    选择
                                </el-button>
                                
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="goodsShow = false">取 消</el-button>
                </span>
            </el-dialog>
            <!--end-->
        </div>
    </div>
    <!-- @include('public.admin.uploadImg')   -->
    @include('public.admin.uploadMultimediaImg')
    <!-- @include('public.admin.uploadImgList') -->

    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                let id = {!! $id?:0 !!};
                let goods_id = {!! $goods_id?:0 !!};
                let default_level = {!! json_encode($default_level) !!};
                let levels = {!! json_encode($levels)?:'{}' !!}
                console.log(id);
                console.log(goods_id);
                return{
                    id:id,
                    goods_id:goods_id,
                    form:{
                        level:5,
                        level_set:'',
                        content:'',
                        head_img_url:'',
                        head_img_url_url:'',
                        goods:'',
                        nick_name:'',
                        image:[],
                        image_url:[],
                        comment_time:new Date().getTime(),
                        time_state:0,
                        score_latitude:{
                            score_latitude_describe:5,
                            score_latitude_delivery:5,
                            score_latitude_service:5,
                        },
                        is_score_latitude:false,
                        is_show:0,
                        is_top:0
                    },
                    goods_list:[],
                    goodsShow:false,
                    chooseGoodsItem:{},//选中的商品
                    keyword:'',
                    submit_url:'',
                    showVisible:false,
                    default_level:default_level,
                    levels:levels,
                    uploadShow:false,
                    chooseImgName:'',

                    uploadListShow:false,
                    chooseImgListName:'',

                    
                    goods_loading: false,
                    uploadImg1:'',
                    rules:{
                        content:{ required: true, message: '请输入评论内容',trigger: 'blur' }
                    },
                    type:'',
                    selNum:'',
                }
            },
            created() {
                console.log(this.level);
                this.levels.unshift({
                    id:0,
                    level:0,
                    level_name:this.default_level
                })
            },
            mounted() {
                // this.getData();
                if(this.id) {
                    this.submit_url = '{!! yzWebFullUrl('goods.comment.updated') !!}';
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('goods.comment.add-comment') !!}';
                }
                this.getData();
            },
            methods: {
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(this.submit_url,{id:this.id,goods_id:this.goods_id}).then(function (response) {
                            if (response.data.result){
                                this.form.nick_name = response.data.data.comment?response.data.data.comment.nick_name:'';
                                this.form.head_img_url = response.data.data.comment?response.data.data.comment.head_img_url:'';
                                this.form.head_img_url_url = response.data.data.comment?response.data.data.comment.head_img_url_url:'';
                                this.form.level = response.data.data.comment&&response.data.data.comment.level?response.data.data.comment.level:5;
                                this.form.content = response.data.data.comment?response.data.data.comment.content:'';
                                this.form.images = response.data.data.comment?response.data.data.comment.images:[];
                                this.form.images_url = response.data.data.comment?response.data.data.comment.images_url:[];
                                this.form.comment_time = response.data.data.comment ? response.data.data.comment.comment_time*1000 : new Date().getTime();
                                this.form.is_show = response.data.data.comment.is_show ?  response.data.data.comment.is_show : 0;
                                this.form.is_top = response.data.data.comment.is_top ?  response.data.data.comment.is_top : 0;
                                this.form.level_set = response.data.data.comment.level_set ?  response.data.data.comment.level_set : 0;
                                console.log(response.data.data.score_latitude);
                                if (this.id) {
                                    if (response.data.data.score_latitude) {
                                        console.log(11);
                                        this.form.score_latitude = response.data.data.score_latitude;
                                    }
                                }

                                // this.form.score_latitude = response.data.data.score_latitude.length > 0 || response.data.data.score_latitude ? response.data.data.score_latitude : this.form.score_latitude;
                                // if (this.id && response.data.data.score_latitude) {
                                //     this.form.score_latitude = response.data.data.score_latitude ? response.data.data.score_latitude : this.form.score_latitude;
                                // } else {
                                //     this.form.score_latitude = response.data.data.score_latitude ? response.data.data.score_latitude : this.form.score_latitude;
                                // }

                                this.form.is_score_latitude = response.data.data.is_score_latitude ? response.data.data.is_score_latitude : false;
                                if(response.data.data.goods && response.data.data.goods.id && response.data.data.goods != [] && response.data.data.goods.length!=0) {
                                    this.chooseGoodsItem = JSON.parse(JSON.stringify(response.data.data.goods));
                                    this.chooseGoodsItem.title = this.escapeHTML(this.chooseGoodsItem.title);
                                    this.chooseGoodsItem.content = `[`+this.chooseGoodsItem.id+`]`+this.chooseGoodsItem.title;
                                    this.form.goods = this.chooseGoodsItem.id;
                                }
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
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('goods.comment.index') !!}`;
                },
                openGoods() {
                    this.goodsShow = true;
                },
                clearGoods() {
                    this.chooseGoodsItem = {};
                    this.form.goods = '';
                },
                searchGoods() {
                    let that = this;
                    this.goods_loading = true;
                    this.$http.post('{!! yzWebFullUrl('goods.comment.search-goods-v2') !!}',{keyword:this.keyword}).then(response => {
                        if (response.data.result) {
                            this.goods_list = response.data.data.goods;
                            this.goods_list.forEach((item,index) => {
                                if(item.title) {
                                    item.title = this.escapeHTML(item.title);
                                }
                            });
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.goods_loading = false;
                    },response => {
                        this.goods_loading = false;
                    });
                },
                sureGoods(item) {
                    this.chooseGoodsItem = {};
                    this.chooseGoodsItem = JSON.parse(JSON.stringify(item));
                    this.chooseGoodsItem.content = `[`+item.id+`]`+item.title;
                    this.form.goods = item.id;
                    this.goodsShow = false;
                },
                submitForm(formName) {
                    let that = this;
                    if(!this.form.goods) {
                        this.$message.error("请选择商品")
                        return false;
                    }
                    let json = {
                        goods_id:this.form.goods,
                        comment:{
                            head_img_url:this.form.head_img_url,
                            nick_name:this.form.nick_name,
                            level:this.form.level,
                            level_set:this.form.level_set,
                            content:this.form.content,
                            images:this.form.images,
                            time_state:this.form.time_state,
                            comment_time:this.form.comment_time,
                            score_latitude:{
                                score_latitude_describe:this.form.score_latitude.score_latitude_describe,
                                score_latitude_delivery:this.form.score_latitude.score_latitude_delivery,
                                score_latitude_service:this.form.score_latitude.score_latitude_service
                            },
                            is_show:this.form.is_show,
                            is_top:this.form.is_top,
                        },
                        
                    };
                    if(this.id) {
                        json.id = this.id
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
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type//1图片类型
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
                //     console.log(image)
                //     console.log(image_url)
                //     this.form[name] = image;
                //     this.form[name+'_url'] = image_url;
                // },
                sureImg(name,uploadShow,fileList) {

                    if(fileList.length <= 0) {
                        return 
                    }
                    if(name == 'images') {//评论图片
                        if(!this.form[name] || !this.form[name+'_url']) {
                            this.form[name] = [];
                            this.form[name+'_url'] = [];
                        }
                        fileList.forEach((item,index) => {
                            this.form[name].push(item.attachment);
                            this.form[name+'_url'].push(item.url);
                        })
                        console.log(this.form[name],this.form[name+'_url'])
                    }
                    if(name == 'head_img_url') {//用户头像
                        this.form[name] =fileList[0].attachment;
                        this.form[name+'_url'] = fileList[0].url;
                    }
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

                openListUpload(str,type) {
                    // this.chooseImgListName = str;
                    // this.uploadShow = true;
                    // this.type = type;
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type//1图片类型
                },
                // changeListProp(val) {
                //     if(val == true) {
                //         this.uploadShow = false;
                //     }
                //     else {
                //         this.uploadShow = true;
                //     }
                // },
                // sureImgList(name,image,image_url) {

                //     console.log(name,'1111')
                //     console.log(image,'2222')
                //     console.log(image_url,'33333')

                //     return
                //     if(!this.form[name] || !this.form[name+'_url']) {
                //         this.form[name] = [];
                //         this.form[name+'_url'] = [];
                //     }
                //     image.forEach((item,index) => {
                //         this.form[name].push(item);
                //         this.form[name+'_url'].push(image_url[index]);
                //     })
                //     console.log(this.form)
                // },
                clearImgList(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
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


