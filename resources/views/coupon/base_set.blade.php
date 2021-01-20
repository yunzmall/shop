@extends('layouts.base')
@section('title', '优惠券设置')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
    .el-tabs__item,.is-top{font-size:16px}
    .el-tabs__active-bar { height: 3px;}
    .description .el-form-item__label{line-height:24px}
</style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav" style="margin-bottom:15px">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="优惠券设置" name="1"></el-tab-pane>
                    <el-tab-pane label="优惠券列表" name="2"></el-tab-pane>
                    <el-tab-pane label="领取发放记录" name="3"></el-tab-pane>
                    <el-tab-pane label="分享领取记录" name="4"></el-tab-pane>
                    <el-tab-pane label="使用记录" name="5"></el-tab-pane>
                    <el-tab-pane label="领券中心幻灯片" name="6"></el-tab-pane>
                </el-tabs>
            </div>
            <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基础设置</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="优惠券使用限制" prop="alias">
                            <el-radio v-model="form.is_singleton" label="1">单张</el-radio>
                            <el-radio v-model="form.is_singleton" label="0">多张</el-radio>
                            <div class="tip">选中单张时每个订单最多只能使用一张优惠券</div>
                        </el-form-item>
                        <el-form-item label="优惠券转让" prop="transfer">
                            <el-radio v-model="form.transfer" label="0">关闭</el-radio>
                            <el-radio v-model="form.transfer" label="1">开启</el-radio>
                            <div class="tip">优惠券转让：会员之间可以转让自己拥有的优惠券。</div>
                        </el-form-item>
                        <el-form-item label="抵扣奖励积分" prop="award_point">
                            <el-radio v-model="form.award_point" label="0">关闭</el-radio>
                            <el-radio v-model="form.award_point" label="1">开启</el-radio>
                            <div class="tip">优惠券抵扣金额奖励等值积分，如优惠券抵扣 10元则奖励 10积分。</div>
                        </el-form-item>
                        <el-form-item label="优惠券返还" prop="order_close_return">
                            <el-radio v-model="form.order_close_return" label="0">关闭</el-radio>
                            <el-radio v-model="form.order_close_return" label="1">开启</el-radio>
                            <div class="tip">开启优惠券返还：未付款订单、退款订单关闭订单后，使用的优惠券返还到会员账户。</div>
                        </el-form-item>
                        <el-form-item label="兑换中心" prop="exchange_center">
                            <el-radio v-model="form.exchange_center" label="0">关闭</el-radio>
                            <el-radio v-model="form.exchange_center" label="1">开启</el-radio>
                            <div class="tip">兑换中心开关</div>
                        </el-form-item>
                        <el-form-item label="优惠券显示" prop="coupon_show">
                            <el-radio v-model="form.coupon_show" label="0">多张展开</el-radio>
                            <el-radio v-model="form.coupon_show" label="1">多张折叠</el-radio>
                            <div class="tip">多张展开:会员同一张优惠券拥有多张时优惠券单张显示</div>
                            <div class="tip">多张折叠:会员同一张优惠券拥有多张时优惠券合并显示（仅限使用时间期限为时间范围的优惠券）</div>
                        </el-form-item>
                        <el-form-item label="下单页优惠券" prop="order_coupon">
                            <el-radio v-model="form.order_coupon" label="0">开启</el-radio>
                            <el-radio v-model="form.order_coupon" label="1">关闭</el-radio>
                            <div class="tip">下单页是否显示优惠券选项</div>
                        </el-form-item>
                        <el-form-item label="通知设置" prop="coupon_notice">
                            <el-select v-model="form.coupon_notice" filterable style="width:70%" placeholder="请选择通知模板" >
                                <el-option label="默认消息模板" :value="default_id"></el-option>
                                <el-option v-for="(item,index) in temp_list" :key="item.id" :label="item.title" :value="item.id"></el-option>
                            </el-select>
                            <el-switch v-model="coupon_notice_open" :active-value="1" :inactive-value="0" @change="changeModal('coupon_notice')"></el-switch>
                        </el-form-item>



                        <el-form-item label="指定商品优惠券领取说明" prop="description" class="description">
                            <span slot="label">指定商品优惠券<br>领取说明</span>
                            <tinymceee v-model="form.description" style="width:70%"></tinymceee>
                        </el-form-item>
                    </div>
                </div>
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">购物分享设置</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="分享限制" prop="share_limit">
                            <el-radio v-model="form.share_limit" label="1">是</el-radio>
                            <el-radio v-model="form.share_limit" label="0">否</el-radio>
                            <div class="tip">是否限制为拥有推广资格的会员才可以分享</div>
                        </el-form-item>
                        <el-form-item label="领取限制" prop="receive_limit">
                            <el-radio v-model="form.receive_limit" label="1">是</el-radio>
                            <el-radio v-model="form.receive_limit" label="0">否</el-radio>
                            <div class="tip">分享者是否可以领取</div>
                        </el-form-item>
                        <el-form-item label="分享Banner图" prop="banner">
                            <div class="upload-box" @click="openUpload('banner')" v-if="!form.banner_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('banner')" class="upload-boxed" v-if="form.banner_url">
                                <img :src="form.banner_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                            </div>
                        </el-form-item>
                        <el-form-item label="分享标题" prop="share_title">
                            <el-input v-model="form.share_title" style="width:70%;"></el-input>
                            <div class="tip">如果不填写，默认为商城名称</div>
                        </el-form-item>
                        <el-form-item label="分享描述" prop="share_desc">
                            <el-input type="textarea" rows="5" v-model="form.share_desc" style="width:70%;"></el-input>
                            <div class="tip">如果不填写，默认为空</div>
                        </el-form-item>
                    </div>
                </div>
            </el-form>

            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>
            <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>

            <!--end-->
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script> 
    @include('public.admin.tinymceee')  
    @include('public.admin.uploadImg')  
    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    activeName:'1',
                    coupon_notice_open:0,
                    coupon_notice:{},
                    default_id:'',
                    form:{
                        is_singleton:'0',
                        transfer:'0',
                        award_point: "0",
                        order_close_return:"0",
                        exchange_center:"0",
                        coupon_show:'0',
                        description:"",
                        coupon_notice:'',
                        order_coupon: "0",
                        share_limit:"0",
                        receive_limit:"0",
                        banner:'',
                        share_title:'',
                        share_desc:'',
                        
                    },
                    temp_list:[],
                    goods_list:[],
                    goodsShow:false,
                    chooseGoodsItem:{},//选中的商品
                    keyword:'',
                    submit_url:'',
                    showVisible:false,

                    uploadShow:false,
                    chooseImgName:'',
                    
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
                this.getData();
            },
            methods: {
                handleClick(val) {
                    console.log(val.name)
                    if(val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('coupon.base-set.see') !!}`;
                    }
                    else if(val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('coupon.coupon.index') !!}`;
                    }
                    else if(val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('coupon.coupon.log-view') !!}`;
                    }
                    else if(val.name == 4) {
                        window.location.href = `{!! yzWebFullUrl('coupon.share-coupon.log') !!}`;
                    }
                    else if(val.name == 5) {
                        window.location.href = `{!! yzWebFullUrl('coupon.coupon-use.index') !!}`;
                    }
                    else if(val.name == 6) {
                        window.location.href = `{!! yzWebFullUrl('coupon.slide-show') !!}`;
                    }
                    
                },
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.base-set.see-data') !!}').then(function (response) {
                            if (response.data.result){
                                
                                this.temp_list = response.data.data.temp_list || [];

                                this.form.is_singleton = response.data.data.coupon && response.data.data.coupon.is_singleton?response.data.data.coupon.is_singleton:'0';
                                this.form.transfer = response.data.data.coupon && response.data.data.coupon.transfer?response.data.data.coupon.transfer:'0';
                                this.form.award_point = response.data.data.coupon && response.data.data.coupon.award_point?response.data.data.coupon.award_point:'0';
                                this.form.order_close_return = response.data.data.coupon && response.data.data.coupon.order_close_return?response.data.data.coupon.order_close_return:'0';
                                this.form.exchange_center = response.data.data.coupon && response.data.data.coupon.exchange_center?response.data.data.coupon.exchange_center:'0';
                                this.form.coupon_show = response.data.data.coupon && response.data.data.coupon.coupon_show?response.data.data.coupon.coupon_show:'0';
                                this.form.coupon_notice = response.data.data.coupon && response.data.data.coupon.coupon_notice?response.data.data.coupon.coupon_notice:'';
                                this.form.description = response.data.data.coupon && response.data.data.coupon.description?response.data.data.coupon.description:'';
                                this.form.share_limit = response.data.data.coupon && response.data.data.coupon.shopping_share?response.data.data.coupon.shopping_share.share_limit:'0';
                                this.form.receive_limit = response.data.data.coupon && response.data.data.coupon.shopping_share?response.data.data.coupon.shopping_share.receive_limit:'0';
                                this.form.banner = response.data.data.coupon && response.data.data.coupon.shopping_share?response.data.data.coupon.shopping_share.banner:'',
                                this.form.banner_url = response.data.data.coupon && response.data.data.coupon.shopping_share?response.data.data.coupon.shopping_share.banner_url:'';
                                this.form.share_title = response.data.data.coupon && response.data.data.coupon.shopping_share?response.data.data.coupon.shopping_share.share_title:'';
                                this.form.share_desc = response.data.data.coupon && response.data.data.coupon.shopping_share?response.data.data.coupon.shopping_share.share_desc:'';
                                this.form.order_coupon = response.data.data.coupon && response.data.data.coupon.order_coupon?response.data.data.coupon.order_coupon:'0';

                                this.coupon_notice = response.data.data.coupon_notice;
                                if(this.coupon_notice) {
                                    // console.
                                    this.default_id = response.data.data.coupon_notice.id+"";
                                    console.log(this.default_id)
                                    this.coupon_notice_open = 1;
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
                openGoods() {
                    this.goodsShow = true;
                },
                clearGoods() {
                    this.chooseGoodsItem = {};
                    this.form.goods = '';
                },
                searchGoods() {
                    let that = this;
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
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
                        loading.close();
                    },response => {
                        loading.close();
                    });
                },
                sureGoods(item) {
                    this.chooseGoodsItem = {};
                    this.chooseGoodsItem = JSON.parse(JSON.stringify(item));
                    this.chooseGoodsItem.title = `[`+item.id+`]`+item.title;
                    this.form.goods = item.id;
                    this.goodsShow = false;
                },
                submitForm(formName) {
                    let that = this;
                    let json = {
                        is_singleton:this.form.is_singleton,
                        transfer:this.form.transfer,
                        award_point:this.form.award_point,
                        order_close_return:this.form.order_close_return,
                        exchange_center:this.form.exchange_center,
                        coupon_show:this.form.coupon_show,
                        coupon_notice:this.form.coupon_notice,
                        description:this.form.description,
                        order_coupon:this.form.order_coupon,
                        shopping_share:{
                            share_limit:this.form.share_limit,
                            receive_limit:this.form.receive_limit,
                            banner:this.form.banner,
                            share_title:this.form.share_title,
                            share_desc:this.form.share_desc,
                        },
                        
                    };
                    
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post('{!! yzWebFullUrl('coupon.base-set.store') !!}',{coupon:json}).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    // this.goBack();
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
                    console.log(name)
                    console.log(image)
                    console.log(image_url)
                    this.form[name] = image;
                    this.form[name+'_url'] = image_url;
                },
                clearImg(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
                },
                changeModal(name) {
                    let url_open = "{!! yzWebUrl('setting.default-notice.store') !!}"
                    let url_close = "{!! yzWebUrl('setting.default-notice.storeCancel') !!}"
                    let url = "";
                    if(this.coupon_notice_open==1) {
                        url = url_open;
                    }
                    else {
                        url = url_close;
                    }
                    let json = {
                        notice_name: name,
                        setting_name: "coupon." + name,
                    }
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,{postdata:json}).then(response => {
                        if (response.data.result == 1) {
                            this.$message({type: 'success',message: '操作成功!'});
                            // this.goBack();
                            if(this.coupon_notice_open==1) {
                                this.default_id = response.data.id;
                                this.form.coupon_notice = response.data.id;
                            }
                            else {
                                this.default_id = '';
                            }
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                            if(this.coupon_notice_open==1) {
                                this.coupon_notice_open=0
                            }
                            else {
                                this.coupon_notice_open=1
                            }
                        }
                        loading.close();
                    },response => {
                        loading.close();
                    });
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


