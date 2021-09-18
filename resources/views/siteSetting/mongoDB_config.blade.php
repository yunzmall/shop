@extends('layouts.base')
@section('content')
@section('title', trans('MONGODB设置'))
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
        border-radius: 8px;
        min-height:100vh;
        background-color:#fff;
        position:relative;
    }
    .con .setting .block{
        padding:10px;
        margin-bottom:10px;
        border-radius: 8px;
    }
    .con .setting .block .title{
        font-size:18px;
        margin-bottom:15px;
        display:flex;
        align-items:center;
    }
    .el-form-item__label{
        margin-right:30px;
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
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>MONGO设置</b></div>
                    <el-form-item label="域名(mongodb_host)">
                        <el-input v-model="form.host" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为127.0.0.1</div>
                    </el-form-item>
                    <el-form-item label="用户名(username)">
                        <el-input v-model="form.username" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为空</div>
                    </el-form-item>
                    <el-form-item label="密码(password)">
                        <el-input v-model="form.password" placeholder="" style="width:70%;" type="password"></el-input>
                        <div style="font-size:12px;">为空时默认为NULL</div>
                    </el-form-item>
                    <el-form-item label="端口(port)">
                        <el-input v-model="form.port" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为27017</div>
                    </el-form-item>
                    <el-form-item label="库(database)">
                        <el-input v-model="form.database" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">默认跟mysql数据库名称一致,切勿随意更改</div>
                    </el-form-item>

                </div>
        </div>
        <div class="confirm-btn">
            <el-button  @click="submit" type="primary">提交</el-button>
        </div>
        </el-form>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
            let data = {!! $set ?: '{}' !!}
            return {
                activeName: 'first',
                form:{
                    host : data.host ? data.host : '',
                    username : data.username ? data.username : '',
                    password : data.password ? data.password : '',
                    port : data.port ? data.port : '',
                    database : data.database ? data.database : '',
                },
            }
        },
        mounted () {
            //this.getData();
        },
        methods: {
            submit() {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('siteSetting.index.mongoDB-config') !!}',{'mongoDB':this.form}).then(function (response){
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
            getData(){
                this.$http.post('{!! yzWebFullUrl('siteSetting.index.mongoDB-config') !!}').then(function (response){
                    if (response.data.result) {
                        if(response.data.data.set){
                            for(let i in response.data.data.set){
                                this.form[i]=response.data.data.set[i]
                            }
                        }
                    }else {
                        this.$message({message: response.data.msg,type: 'error'});
                    }

                },function (response) {
                    this.$message({message: response.data.msg,type: 'error'});
                })
            }
        },
    });
</script>
@endsection
