@extends('layouts.base')
@section('title', '前端版本')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-main">

                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">前端版本</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="当前版本" prop="version">
                            <el-input v-model="form.version" style="width:70%;" :disabled="true"></el-input>
                        </el-form-item>
                        <el-form-item label="修改版本" prop="change">
                            <el-input v-model="form.change" style="width:70%;"></el-input>
                        </el-form-item>
                    </el-form>
                </div>

                <!-- 分页 -->
                <div class="vue-page">
                    <div class="vue-center">
                        <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
    .vue-page {
        width: calc(100% - 115px);
    }
    </style>
    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            data() {

                return{
                    form:{
                        version:'',
                        change:''
                    },
                    rules:{},
                }
            },
            created() {


            },
            mounted() {
                this.getData();
            },
            methods: {
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('frontend-version.getVersion') !!}').then(function (response) {
                            if (response.data.result){
                                this.form.version = response.data.data.version || '';
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
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post("{!! yzWebUrl('frontend-version.change') !!}",{'version':this.form.change}).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                                location.reload();
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
        })

    </script>
@endsection


