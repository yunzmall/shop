@extends('layouts.base')
@section('title', '折扣全局设置')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>

    <style>
        
        .el-tabs__item,.is-top{font-size:16px}
        .el-tabs__active-bar { height: 3px;}
    </style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="折扣全局设置" name="1"></el-tab-pane>
                    <el-tab-pane label="折扣设置" name="2"></el-tab-pane>
                    <el-tab-pane label="运费批量设置" name="3"></el-tab-pane>
                </el-tabs>
            </div>
            <div class="vue-main">
                
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">折扣全局设置</div>
                </div>
                <div class="vue-main-form">
                    
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="折扣类型" prop="type">
                            <el-radio v-model.number="form.type" :label="0">商品现价</el-radio>
                            <el-radio v-model.number="form.type" :label="1">商品原价</el-radio>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                    <!-- <el-button @click="goBack">返回</el-button> -->
                </div>
            </div>
            <!--end-->
        </div>
    </div>

    
    <script>
        var vm = new Vue({
        el:"#app",
        delimiters: ['[[', ']]'],
            data() {
                let type = JSON.parse('{!! $set !!}');
                return{
                    form:{
                        type:0,
                        ...type
                    },
                    type:type,
                    submit_loading:false,
                    activeName:"1",
                    rules: {
                        
                    },
                }
            },
            methods: {
                handleClick(val) {
                    console.log(val.name)
                    if(val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('discount.batch-discount.allSet') !!}`;
                    }
                    else if(val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('discount.batch-discount.index') !!}`;
                    }
                    else if(val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('discount.batch-dispatch.freight') !!}`;
                    }
                },
                submitForm(formName) {
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post("{!! yzWebUrl('discount.batch-discount.all-set') !!}",{'form_data':this.form}).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    window.location.href='{!! yzWebFullUrl('discount.batch-discount.allSet') !!}';
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




