@extends('layouts.base')
@section('title', '商品表单')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-main">
                
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">表单使用规则</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="标题" prop="explain_title">
                            <el-input v-model="form.explain_title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="内容" prop="explain_content">
                            <el-input type="textarea" :rows="8" v-model="form.explain_content" style="width:70%;"></el-input>
                            <div class="tip">ps：商品表单是针对跨境商品报税所开放使用的固定信息，非自定义表单信息</div>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {

                return{
                    form:{
                        explain_title:'',
                        explain_content:'',
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
                    this.$http.post('{!! yzWebFullUrl('from.div-from.get-data') !!}').then(function (response) {
                            if (response.data.result){
                                this.form.explain_title = response.data.data.explain_title || '';
                                this.form.explain_content = response.data.data.explain_content || '';
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
                            this.$http.post("{!! yzWebUrl('from.div-from.store') !!}",{'div_from':this.form}).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
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
        })

    </script>
@endsection


