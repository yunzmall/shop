    @extends('layouts.base')
    @section('title', '会员等级设置')
    @section('content')
    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style scoped>
        .main-panel{
            margin-top:50px;
        }
        .main-panel #re_content {
            padding: 10px;
        }
        .panel{
            margin-bottom:10px!important;
            border-radius: 10px;
            padding-left: 20px;
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
            padding:10px!important;
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
        .block{
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
        .el-checkbox__inner{
            border:solid 1px #56be69!important;
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
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 会员等级设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">会员等级设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="默认会员级别名称"  >
                                <el-input v-model="form.level_name" placeholder="请输入会员级别名称" style="width:70%;"></el-input>
                                <div style="font-size:12px;">会员默认等级名称，不填写默认“普通会员”</div>
                            </el-form-item>
                            <el-form-item label="会员等级权益页面是否显示" >
                                <template>
                                    <el-switch
                                            v-model="form.display_page"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div style="font-size:12px;">提示： 开启，用户在商品详情页和会员中心点击会员等级都可以进入等级权益页面</div>
                            </el-form-item>
                            <el-form-item label="会员等级升级依据">
                                <template>
                                    <el-radio-group v-model="form.level_type">
                                        <el-radio label="0">订单金额</el-radio>
                                        <el-radio label="1">订单数量</el-radio>
                                        <el-radio label="2">指定购买商品</el-radio>
                                        <el-radio label="3">团队业绩(自购+一级+二级)</el-radio>
                                        <el-radio label="4">一次性充值余额</el-radio>
                                    </el-radio-group>
                                </template>
                            </el-form-item>
                            <el-form-item label=" ">
                                <template>
                                    <el-radio-group v-model="form.level_after">
                                        <el-radio label="1">付款后</el-radio>
                                        <el-radio label="0">完成后</el-radio>
                                    </el-radio-group>
                                </template>
                                <div style="font-size:12px;">如果选择付款后，只要用户下单付款满足升级依据，即可升级；如果选择完成后，则表示需要订单完成状态才能升级</div>
                            </el-form-item>
                            <el-form-item label="会员等级时间限制" >
                                <template>
                                    <el-switch
                                            v-model="form.term"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                                <div style="font-size:12px;">只对指定购买商品升级依据生效，设置其它条件时会员等级不会受到该选项影响</div>
                            </el-form-item>
                            <el-form-item label="会员等级优惠计算方式 ">
                                <template>
                                    <el-radio-group v-model="form.level_discount_calculation">
                                        <el-radio label="0">折扣方式</el-radio>
                                        <el-radio label="1">成本比例方式</el-radio>
                                    </el-radio-group>
                                </template>
                                <div style="font-size:12px;"> 折扣方式：按打折计算最高10折，折扣价格= 商品现价 * (1 - 折扣率/10 ) <br/>
                                    成本比例方式：按百分比计算最高999%, 折扣价格 = 商品成本价 * (比例/100) <br/></div>
                            </el-form-item>
                            <el-form-item label="商品详情会员折扣" >
                                <template>
                                    <el-switch
                                            v-model="form.discount"
                                            active-value="1"
                                            inactive-value="2"
                                    >
                                    </el-switch>
                                </template>
                            </el-form-item>
                            <el-form-item label="会员价" >
                                <template>
                                    <el-switch
                                            v-model="form.vip_price"
                                            active-value="1"
                                            inactive-value="2"
                                    >
                                    </el-switch>
                                </template>
                                <div style="font-size:12px;">开启后，在前端商品选择规格页面，购物车页面，填写订单页面，我的订单页面，订单详情页面，存货订单页面（存货订单插件）显示会员价 <br/></div>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                return {
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                }
            },
            created() {
            },
            mounted () {
                console.log(this.form,111);
            },
            methods: {
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.level-store') !!}';
                    let json = {
                        set : this.form
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});

                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        // location.reload();
                    },response => {
                        loading.close();
                    });
                },
            }
        })
    </script>
    @endsection