@extends('layouts.base')
@section('title', '商品评论回复')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .reply-content{width:70%;border:1px solid #dadada;background:#f5f7fa;padding:10px}
    .reply-content-li{display:flex;line-height:24px;margin-bottom:5px}
    .reply-content-left{max-width:300px;overflow:hidden;display:flex}
    .reply-content-left-times{width:130px;font-size:12px;color:#ccc;}
    .reply-content-left-user{flex:1;font-weight:500;display:flex}
    .reply-content-left-user1{max-width:60px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;word-break: break-all;}
    .reply-content-left-user2{width:40px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;word-break: break-all;}
    .reply-content-left-user3{max-width:70px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;word-break: break-all;}
    .reply-content-middle{flex:1;margin: 0 15px;color: #666;font-size: 12px;font-weight: 500;}
    .reply-content-middle-img{margin:5px 2px;display:inline-block}
</style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">评论管理</a> > 回复评论
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">回复评论</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="评价商品" prop="goods">
                            <el-input v-model="goods.title" style="width:70%;" disabled></el-input>
                            <div style="width:150px;height:150px;position:relative;margin-top:10px">
                                <img :src="goods.thumb" onerror="this.src='/addons/yun_shop/static/resource/images/nopic.jpg'; this.title='图片未找到.'" style="width:150px;height:150px">
                            </div>
                        </el-form-item>
                        <el-form-item label="评价者" prop="goods">
                            <el-input v-model="comment.nick_name" style="width:70%;" disabled></el-input>
                            <div style="width:150px;height:150px;position:relative;margin-top:10px">
                                <img :src="comment.head_img_url" onerror="this.src='/addons/yun_shop/static/resource/images/nopic.jpg'; this.title='图片未找到.'" style="width:150px;height:150px">
                            </div>
                        </el-form-item>
                        <el-form-item label="评分等级" prop="level">
                            <div style="padding-top:10px">
                                <el-rate v-model="comment.level" disabled show-score></el-rate>
                            </div>
                        </el-form-item>

                        <el-form-item label="评论内容" prop="alias">
                            <el-input v-model="comment.content" disabled type="textarea" style="width:70%"></el-input>
                            <div v-if="comment&&comment.images&&comment.images.length>0">
                                <div v-for="(item1,index1) in comment.images" :key="index1" style="margin:5px 2px;display:inline-block">
                                    <img :src="item1" alt="" style="width:50px;height:50px" @click="openBig(comment.images,index1)">
                                </div>
                            </div>
                        </el-form-item>

                        <el-form-item label="">
                            <div class="reply-content">
                                <div v-for="(item,index) in comment.has_many_reply" :key="index">
                                    <div class="reply-content-li">
                                        <div class="reply-content-left">
                                            <div class="reply-content-left-times">[[item.created_at]]</div>
                                            <div class="reply-content-left-user">
                                                <div class="reply-content-left-user1">[[item.nick_name]]&nbsp;</div>
                                                <div class="reply-content-left-user2">[[item.type_name]]</div>
                                                <div class="reply-content-left-user3" v-if="item.type!=3">&nbsp;&nbsp;[[item.reply_name]]</div>
                                            </div>
                                        </div>
                                        <div class="reply-content-middle">
                                            <div class="reply-content-middle-con" style="line-height:24px">[[item.content]]</div>
                                            <div class="reply-content-middle-img" v-for="(item1,index1) in item.images" :key="index1">
                                                <img :src="item1" alt="" style="width:50px;height:50px" @click="openBig(item.images,index1)">
                                            </div>
                                        </div>
                                        <div style="width:150px;text-align:right">
                                            <el-button size="mini" v-if="item.uid&&item.type!=3" @click="replyCon(item)">回复</el-button>
                                            <el-button size="mini" type="info" @click="del(item.id,index)" plain>删除</el-button>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top:10px">
                                    <el-form-item :label="'管理员 回复 '+reply_name" prop="content">
                                        <el-input v-model="form.content" type="textarea" rows="5" ref="reply_content"></el-input>
                                        <el-button size="mini" @click="openListUpload('images')">选择图片</el-button>
                                        <div v-if="form.images_url">
                                            <div v-for="(item,index) in form.images_url" :key="index" style="display:inline-block;margin:5px;width:50px;height:50px;position:relative;margin-top:10px">
                                                <img :src="item" alt="" style="width:50px;height:50px">
                                                <i
                                                    class="el-icon-circle-close"
                                                    @click="clearImg('images','list',index)"
                                                    title="点击清除图片"
                                                ></i>
                                            </div>
                                        </div>
                                    </el-form-item>
                                </div>
                            </div>
                        </el-form-item>
                    </el-form>
                    <el-dialog :visible.sync="big_img_show" width="60%" height="500px" center>
                        <div>
                            <el-carousel trigger="click" height="500px" :initial-index="big_img_index" :autoplay="false">
                                <el-carousel-item v-for="item in big_img_list" :key="item">
                                    <div style="text-align:center">
                                        <img :src="item" style="max-height:700px;" />
                                    </div>
                                </el-carousel-item>
                            </el-carousel>
                        </div>
                    </el-dialog>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>

            <upload-img-list :upload-list-show="uploadListShow" :name="chooseImgListName" @replace="changeListProp" @sure="sureImgList"></upload-img-list>
        </div>
    </div>
    @include('public.admin.uploadImgList')

    <script>
        let id = {!! $id !!};
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                
                return{
                    
                    id:id,
                    form:{
                        images:[],
                        images_url:[],
                        content:'',
                    },
                    comment_id:'',
                    reply_id:'',
                    reply_name:'',

                    big_img_show:false,
                    big_img_list:[],
                    big_img_index:0,

                    goods:{},
                    comment:{},
                    submit_url:'',

                    uploadListShow:false,
                    chooseImgListName:'',
                    
                    loading: false,
                    rules:{
                        content:{ required: true, message: '请输入回复内容'}
                    },

                }
            },
            created() {


            },
            mounted() {
                this.getData();
                this.submit_url = '{!! yzWebFullUrl('goods.comment.reply') !!}';
            },
            methods: {
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.comment.reply') !!}',{id:this.id}).then(function (response) {
                            if (response.data.result){
                                this.goods = response.data.data.goods || {};
                                this.comment = response.data.data.comment || {};
                                this.comment_id = response.data.data.comment.id;
                                this.reply_id = response.data.data.comment.uid;
                                this.reply_name = response.data.data.comment.nick_name;
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
                // 回复
                replyCon(item) {
                    this.$refs.reply_content.focus();
                    this.reply_id = item.uid;
                    this.reply_name = item.nick_name;
                },
                
                del(id,index) {
                    console.log(id,index)
                    this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('goods.comment.deleted') !!}',{id:id}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '删除成功!'});
                                    this.getData();
                                }
                                else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                                this.search(this.current_page)
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消删除'});
                    });
                },
                submitForm(formName) {
                    console.log(this.form)
                    let that = this;
                    let json = {
                        id:this.id,
                        reply:{
                            reply_content:this.form.content,
                            reply_images:this.form.images,
                            order_id:this.comment.order_id,
                            goods_id:this.comment.goods_id,
                            nick_name:'管理员',
                            comment_id:this.comment_id,
                            reply_id:this.reply_id,
                            type:2,
                        }
                    };
                    console.log(json);
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
                    window.location.href = `{!! yzWebFullUrl('goods.comment.index') !!}`;
                },
                openBig(item,index) {
                    this.big_img_index = index;
                    this.big_img_list = item;
                    this.big_img_show = true;
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

                openListUpload(str) {
                    this.chooseImgListName = str;
                    this.uploadListShow = true;
                },
                changeListProp(val) {
                    if(val == true) {
                        this.uploadListShow = false;
                    }
                    else {
                        this.uploadListShow = true;
                    }
                },
                sureImgList(name,image,image_url) {
                    console.log(name)
                    console.log(image)
                    console.log(image_url)
                    if(!this.form[name] || !this.form[name+'_url']) {
                        this.form[name] = [];
                        this.form[name+'_url'] = [];
                    }
                    image.forEach((item,index) => {
                        this.form[name].push(item);
                        this.form[name+'_url'].push(image_url[index]);
                    })
                    console.log(this.form)
                },
                
            },
        })
    </script>
@endsection


