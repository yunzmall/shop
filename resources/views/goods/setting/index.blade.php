@extends('layouts.base')
@section('title', trans('基础设置'))
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .all { background: #eff3f6;}
</style>
<div class="all">
    <div id="app" v-cloak>
        <el-form ref="form" :model="form" label-width="15%">
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">基础设置</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="显示月销售量" prop="is_month_sales">
                        <el-switch v-model="form.set.is_month_sales" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">开启后在自营，供应商，门店，聚合供应链，租赁商品详情页显示月销量</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="隐藏总销售量" prop="hide_goods_sales">
                        <el-switch v-model="form.set.hide_goods_sales" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">开启后在分类页、自营商品详情页、拍卖商品详情页隐藏总销量</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="显示利润" prop="profit_show_status">
                        <el-switch v-model="form.set.profit_show_status" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">开启后在商品(自营)列表和详情页显示商品利润(售价-成本价)</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="显示会员中心入口" prop="is_member_enter">
                        <el-switch v-model="form.set.is_member_enter" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">开启后在商品详情页面显示会员中心入口</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="商品详情页-商品详情" prop="method">
                        <el-radio v-model="form.set.detail_show" :label="0">默认不显示</el-radio>
                        <el-radio v-model="form.set.detail_show" :label="1">默认显示</el-radio>
                        <div class="tip">PC端商品详情默认显示，不受本设置控制；H5/公众号/小程序端按本设置控制；<br>
                            默认不显示：用户访问商品详情页时，需要下拉再加载商品详情，可以提升页面打开速度；<br>
                            默认显示：用户访问商品详情页时，自动显示商品详情，可能会影响商品详情打开速度。</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="价格说明" prop="is_price_desc">
                        <el-switch v-model="form.set.is_price_desc" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">开启后在自营，供应商，门店，聚合供应链，租赁，应用市场商品详情页显示价格说明</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="自定义标题" prop="title">
                        <el-input v-model="form.set.title" style="width:70%;"></el-input>
                        <div class="tip">默认显示价格说明</div>
                    </el-form-item>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="原价划线显示" prop="method">
                        <el-radio v-model="form.set.scribing_show" :label="0">显示划线</el-radio>
                        <el-radio v-model="form.set.scribing_show" :label="1">不显示划线</el-radio>
                        <div class="tip">
                            设置装修，商品列表，商品详情页原价是否显示划线<br>
                            装修含商品组件，选项卡商品组件，商品列表含搜索页，全部商品页，品牌商品页，分类页商品含自营商品，供应商商品
                        </div>
                    </el-form-item>
                </div>

                <div class="vue-main-form">
                    <el-form-item label="说明内容" prop="explain">
                        <tinymceee v-model="form.set.explain" style="width: 68%"></tinymceee>
                    </el-form-item>
                </div>
            </div>
        </el-form>

        <!-- 分页 -->
        <div class="vue-page">
            <div class="vue-center">
                <el-button type="primary" @click="submitForm('form')">提交</el-button>
{{--                <el-button @click="goBack">返回</el-button>--}}
            </div>
        </div>
    </div>
</div>
@include('public.admin.tinymceee')
<script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
<script>
    var vm = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        data() {
            let set = {!! $set !!};
            return {
                loading: false,
                form:{
                    set:set?set:{
                        'is_member_enter':1,
                        'detail_show' : 0,
                        'scribing_show':0,
                    },
                },
            }

        },
        mounted () {
        },
        methods: {
            goBack() {
                history.go(-1);
            },
            submitForm(formName) {
                let json = {
                    data:{
                        is_month_sales:this.form.set.is_month_sales,
                        is_member_enter:this.form.set.is_member_enter,
                        is_price_desc:this.form.set.is_price_desc,
                        title:this.form.set.title,
                        explain:this.form.set.explain,
                        detail_show:this.form.set.detail_show,
                        profit_show_status:this.form.set.profit_show_status,
                        hide_goods_sales:this.form.set.hide_goods_sales,
                        scribing_show:this.form.set.scribing_show,
                    }
                };
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post('{!! yzWebFullUrl('goods.goods_setting.index') !!}',json).then(response => {
                            if (response.data.result) {
                                this.$message({type: 'success',message: '操作成功!'});
                                window.location.reload();
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
        },
    });


</script>
@endsection

