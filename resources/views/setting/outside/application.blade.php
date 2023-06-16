@extends('layouts.base')
@section('content')
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
            padding-bottom:20px;
            position:relative;
            min-height:100vh;
            background-color:#fff;
        }
    </style>

    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div v-if="set_show" class="setting">
                <el-form ref="form_data" :model="form_data" label-width="15%">
                    <!-- 自定义名称 -->
                    <div class="vue-head">
                        <div class="vue-main-title">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">基础设置</div>
                        </div>

                        <el-form-item label="接口地址：">
                            <div style="min-height: 40px">
                                <span>{{ request()->getSchemeAndHttpHost().'/outside/'.\YunShop::app()->uniacid.'/' }}</span>
                            </div>
                        </el-form-item>

                        <div class="vue-main-form">
                            <el-form-item label="是否开启：">
                                <el-switch
                                        v-model="form_data.is_open"
                                        :active-value="1"
                                        :inactive-value="0"
                                        active-color="#29BA9C"
                                        inactive-color="#ccc">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="签名校验：">
                                <el-switch
                                        v-model="form_data.sign_required"
                                        :active-value="0"
                                        :inactive-value="1"
                                        active-color="#29BA9C"
                                        inactive-color="#ccc">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="应用APPID：">
                                <div>[[form_data.app_id]]</div>
                            </el-form-item>
                            <el-form-item label="APP_Secret：">
                                <div style="min-height: 40px">
                                    <span v-if="secret_show">[[form_data.app_secret]]</span>
                                    <el-button size="mini" type="info" round @click="isShow()">[[button_name]]</el-button>
                                </div>
                                <div style="">
                                    <el-button size="mini" type="danger" round @click="updateSecret()">更新</el-button>
                                </div>
                            </el-form-item>
                        </div>
                    </div>
                    {{--<div style="background: #eff3f6;width:100%;height:15px;"></div>--}}
                    {{--<div class="vue-head">--}}
                        {{--<div class="vue-main-title">--}}
                            {{--<div class="vue-main-title-left"></div>--}}
                            {{--<div class="vue-main-title-content"></div>--}}
                        {{--</div>--}}
                        {{--<div class="vue-main-form">--}}

                        {{--</div>--}}
                    {{--</div>--}}

                </el-form>
                <div class="vue-page">
                    <div class="vue-center">
                        <el-button type="primary" @click="submitForm">提交</el-button>
                    </div>
                </div>
            </div>
            <div v-if="!set_show">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div style="text-align:center">
                            <h3>设置对应APP应用</h3>
                        </div>
                        <div style="text-align:center">
                            <el-button type="primary" @click="openApp()">开启对外应用</el-button>
                        </div>
                        <div style="margin-top: 20px; text-align:center">
                            注意： <span style="color: red">保存好密钥和应用ID，APPID是唯一无法更改的</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    form_data:{
                        is_open:0,
                        sign_required:0,
                    },
                    set_show:false,
                    secret_show:false,
                    button_name: '显示',
                }
            },
            mounted () {
                let result = this.viewReturn();
                this.__initial(result);
            },
            methods: {
                //视图返回数据
                viewReturn() {
                    return {!! $data?:'{}' !!};
                },
                //初始化页面数据，请求链接
                __initial(data) {

                    if(data.set && JSON.stringify(data.set) !== '[]') {
                        this.form_data = data.set;
                        this.set_show = true;
                    }
                },
                isShow() {
                    this.secret_show = this.secret_show == true ? false: true;
                    this.button_name = this.button_name == '显示' ? '隐藏': '显示';
                },

                openApp() {
                    this.$confirm('确认开启对外应用吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {

                        this.$http.post('{!! yzWebFullUrl('setting.outside-app.create-app') !!}').then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '创建成功'});
                                    location.reload();
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消开启'});
                    });

                },

                switchOperation(operation) {

                    let json = {
                        operation:operation,
                        value:this.form_data[operation],
                    };

                    console.log(json);
                    this.$http.post('{!! yzWebFullUrl('setting.outside-app.store') !!}',{data:json}).then(function (response) {
                            if (response.data.result) {
                                // this.$message({type: 'success',message: '操作成功'});
                            } else{
                                this.$message({type: 'error',message: response.data.msg});
                            }
                        },function (response) {
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    );
                },

                updateSecret() {
                    this.$confirm('确认更新密钥吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('setting.outside-app.update-secret') !!}',{operation:'app_secret'}).then(function (response) {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功'});
                                    location.reload();
                                } else{
                                    this.$message({type: 'error',message: response.data.msg});
                                }
                                loading.close();
                            },function (response) {
                                this.$message({type: 'error',message: response.data.msg});
                                loading.close();
                            }
                        );
                    }).catch(() => {
                        this.$message({type: 'info',message: '已取消操作'});
                    });
                },

                submitForm() {

                    let json = {
                        is_open:this.form_data.is_open,
                        sign_required:this.form_data.sign_required,
                    };

                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.outside-app.store') !!}',{data:json}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        loading.close();
                    })
                },
            },
        });
    </script>
@endsection
