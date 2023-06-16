@extends('layouts.base')
@section('title', '商品标签详情')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">商品标签</a> > 编辑标签
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">编辑标签</div>
                    <div class="vue-main-title-button">
                        <el-button type="primary" plain icon="el-icon-back" size="small" @click="goNext" v-if="parent_id&&parent_id!=0">返回上级列表</el-button>
                    </div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="所属标签组" prop="explain_title" v-if="parent_id&&parent_id!=0">
                            <el-input v-model="parent.name" style="width:70%;" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="" prop="explain_title" v-else>
                            <div style="color:#f00;font-weight:600">标签组</div>
                        </el-form-item>
                        <el-form-item label="名称" prop="name">
                            <el-input  v-model="form.name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="是否启用" prop="is_show" v-if="parent_id==0">
                            <el-radio v-model.number="form.is_show" :label="1">不启用</el-radio>
                            <el-radio v-model.number="form.is_show" :label="0">启用</el-radio>
                        </el-form-item>
                        <el-form-item label="前端是否显示" prop="is_front_show" v-else>
                            <el-radio v-model.number="form.is_front_show" :label="0">不显示</el-radio>
                            <el-radio v-model.number="form.is_front_show" :label="1">显示</el-radio>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button  @click="goBack()">返回</el-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let parent_id = {!! $parent_id?:'0' !!};
        let id = {!! $id?:'0' !!};
        console.log(parent_id)
        console.log(id)
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {

                return{
                    id:id,
                    parent_id:parent_id,
                    parent:{},
                    item:{},
                    submit_url:'',
                    form:{
                        
                    },
                    rules:{
                        name:{ required: true, message: '请输入名称'}
                    },
                }
            },
            created() {


            },
            mounted() {
                let json = {};
                if(this.id==0) {
                    this.submit_url = '{!! yzWebFullUrl('filtering.filtering.create') !!}';
                    json = {parent_id:this.parent_id}
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('filtering.filtering.edit') !!}';
                    json = {parent_id:this.parent_id,id:this.id}
                }
                this.getData(json);
            },
            methods: {
                getData(json) {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(this.submit_url,json).then(function (response) {
                            if (response.data.result){
                                this.parent =  response.data.data.parent;
                                if(this.id!=0) {
                                    this.item = response.data.data.item;
                                    this.form = response.data.data.item;
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
                submitForm(formName) {
                    let that = this;
                    let json = {
                        parent_id:this.parent_id,
                        filter:{
                            name:this.form.name,
                            is_show:this.form.is_show || 0,
                            is_front_show:this.form.is_front_show || 0
                        }
                    }
                    if(this.id!=0) {
                        json.id = this.id
                    }
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
                    window.location.href = `{!! yzWebFullUrl('filtering.filtering.index') !!}`;
                }, 
                goNext() {
                    window.location.href = `{!! yzWebFullUrl('filtering.filtering.filter-value') !!}`+`&parent_id=`+this.parent_id;
                },          
            },
        })

    </script>
@endsection


