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
                        <div class="title">  <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>高德地图</b></div>
                        <el-form-item label="Web服务：key">
                            <el-input v-model="form.web" style="width:70%;" ></el-input>
                            <el-button  type="primary" @click="synchronize" v-if="synchronize_store != 1">同步到门店</el-button>
                            <div style="color:#737373;font-size:12px;">
                                <span>此处指定高德绑定服务"Web服务"，如何注册高德地图KEY</span>
                                <a href="https://jingyan.baidu.com/article/bea41d43c78831b4c51be68b.html">查看帮助</a>，
                            </div>
                        </el-form-item>
                        <el-form-item label="Web端(JS API)">
                            <el-input v-model="form.web_js_key" style="width:70%;" ></el-input>
                            <div style="color:#737373;font-size:12px;">
                                <span>此处指定高德绑定服务"Web端(JS API)"，如何注册高德地图KEY</span>
                                <a href="https://jingyan.baidu.com/article/bea41d43c78831b4c51be68b.html">查看帮助</a>，
                                <span>注：请注意Web端(JS API) 和 Web服务是有区别的</span>
                            </div>
                        </el-form-item>
                        <el-form-item label="Web端(JS API) 安全密钥">
                            <el-input v-model="form.web_js_secret_key" style="width:70%;" ></el-input>
                        </el-form-item>

                        <div class="confirm-btn">
                            <el-button  type="primary" @click="submit">提交</el-button>
                        </div>
                    </div>
                </el-form>
                </div>
            </div>
        </div>
        <script>
            var vm = new Vue({
                el: "#re_content",
                delimiters: ['[[', ']]'],
                data() {
                    let map = {!! json_encode($map) !!};

                    return {
                        activeName: 'one',
                        disabled:map ? !!map.web : false,
                        synchronize_store: map.synchronize_store ? map.synchronize_store : 0,
                        form:{
                            web: map ? map.web : '',
                            web_js_key: map ? map.web_js_key : '',
                            web_js_secret_key: map ? map.web_js_secret_key : '',
                        },
                    }
                },
                mounted () {
                },
                methods: {
                    submit() {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('map.setting.index') !!}',{'a_map':this.form}).then(function (response){
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
                    synchronize() {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('map.setting.synchronize') !!}',{'a_map':this.form}).then(function (response){
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
