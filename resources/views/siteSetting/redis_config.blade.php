@extends('layouts.base')
@section('content')
@section('title', trans('REDIS设置'))
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
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>REDIS设置</b></div>
                    <el-form-item label="域名(redis_host)" v-if="framework">
                        <el-input v-model="form.host" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为127.0.0.1</div>
                    </el-form-item>
                    <el-form-item label="密码(redis_password)" v-if="framework">
                        <el-input v-model="form.password" placeholder="" style="width:70%;"  type="password"></el-input>
                        <div style="font-size:12px;">redis没设置密码时此行不用填写，为空时默认为NULL</div>
                    </el-form-item>
                    <el-form-item label="端口(port)" v-if="framework">
                        <el-input v-model="form.port" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为6379</div>
                    </el-form-item>
                    <el-form-item label="库(database)">
                        <el-input v-model="form.database" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为0</div>
                    </el-form-item>
                    <el-form-item label="cache库(cache_database)">
                        <el-input v-model="form.cache_database" placeholder="" style="width:70%;"></el-input>
                        <div style="font-size:12px;">为空时默认为1</div>
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
                framework : {{(config('app.APP_Framework', false) == 'platform')?1:0}},
                form:{
                    host : data.host ? data.host : '',
                    password :data.password ? data.password : '',
                    port : data.port ? data.port : '',
                    database : data.database ? data.database : '',
                    cache_database : data.cache_database ? data.cache_database : '',
                },
            }
        },
        mounted () {
         //   this.getData();
        },
        methods: {
            submit() {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('siteSetting.index.redis-config') !!}',{'redis':this.form}).then(function (response){
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
                this.$http.post('{!! yzWebFullUrl('siteSetting.index.redis-config') !!}').then(function (response){
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
