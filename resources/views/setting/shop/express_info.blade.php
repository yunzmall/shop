@extends('layouts.base')

@section('content')
    <style>
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
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                       
                    <div class="block">
                        <div class="title">  <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                        <el-form-item label="类型选择">
                            <template>
                                    <el-radio-group v-model="express_type" >
                                        <el-radio label="1">快递鸟</el-radio>
                                        <el-radio label="2">芸签</el-radio>
                                    </el-radio-group>
                            </template>
                            <p>芸签快递查询套餐费用远低于快递鸟，购买请联系客服！</p>
                        </el-form-item>
                        <template v-if="express_type=='1'">
                            <el-form-item label="是否收费接口">
                                <template>
                                    <el-switch
                                            v-model="form.KDN.express_api"

                                            active-value="8001"
                                            inactive-value="1002"
                                    >
                                    </el-switch>
                                </template>
                            </el-form-item>
                            <el-form-item label="用户ID">
                                <el-input v-model="form.KDN.eBusinessID" placeholder="请输入用户ID" style="width:70%;"></el-input>
                            </el-form-item>
                            <el-form-item label="API key">
                                <el-input v-model="form.KDN.appKey" placeholder="请输入APIKey" style="width:70%;"></el-input>
                            </el-form-item>

                            <el-form-item label="青龙配送编码">
                                <el-input v-model="form.KDN.CustomerName" placeholder="请输入青龙配送编码" style="width:70%;"></el-input>
                                <div style="color:#737373;font-size:12px;">在青龙系统申请一个商家编码，也叫青龙配送编码，格式：数字＋字母＋数字，举例：001K123456,京东配送必填</div>
                                <div style="color:#737373;font-size:12px;">使用快递鸟物流接口，请注意开通相关账号。官网： <a href="https://www.kdniao.com/reg?from=cbbyzxx" target="_blank">https://www.kdniao.com/reg?from=cbbyzxx</a> ，开通详情步骤说明请联系客服。</div>
                            </el-form-item>
                        </template>
                        <template v-if="express_type=='2'">
                            <el-form-item label="AppId" >
                                <el-input v-model="yun_form.YQ.appId"  style="width:70%;"></el-input>
                            </el-form-item>
                            <el-form-item label="AppSecret" >
                                <el-input v-model="yun_form.YQ.appSecret"  style="width:70%;"></el-input>
                                <p>接口总数:[[yun_form.YQ.apiTotalCount]] 单;接口使用总数: [[yun_form.YQ.statistics]] 单;接口剩余总数:[[yun_form.YQ.surplus]] 单;</p>
                            </el-form-item>
                        </template>
                    </div>
            </div>
                <div class="confirm-btn">
                    <el-button  type="primary" @click="submit">提交</el-button>
                </div>
            </el-form>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    express_type:'1',
                    activeName: 'one',
                    yun_form:{
                        YQ:{
                            appId:'',
                            appSecret:'',
                            apiTotalCount:0,
                            statistics:0,
                            surplus:0
                        }
                    },
                    form:{
                        KDN: {
                            express_api:'0',
                            eBusinessID:'',
                            appKey:'',
                            Mobile:'',
                            CustomerName:'',
                        },
                    },
                }
            },
            mounted () {
                this.getData()
            },
            methods: {
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.express-info') !!}').then(function (response){
                        if (response.data.result) {
                            if(response.data.data.set){
                                for(let i in response.data.data.set){
                                    response.data.data.type==2?this.yun_form[i]=response.data.data.set[i]:this.form[i]=response.data.data.set[i]
                                }
                            }
                            if(response.data.data.type == 2){
                                console.log(response.data.data.statistics,45454545)
                                this.yun_form.YQ.statistics = response.data.data.statistics.statistics;
                                this.yun_form.YQ.apiTotalCount = response.data.data.statistics.apiTotalCount;
                                this.yun_form.YQ.surplus = response.data.data.statistics.apiTotalCount - response.data.data.statistics.statistics;
                            }
                            if(response.data.data.type){
                                this.express_type=String(response.data.data.type)
                            }
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                submit() {
                    let json={}
                    if(this.express_type=='1'){
                        json=this.form
                    }else{
                        json=this.yun_form
                    }
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('setting.shop.express-info') !!}',{'express_info':json,type:this.express_type}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        }else {
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
