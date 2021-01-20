@extends('layouts.base')
@section('content')
@section('title', trans('物理路径修改'))
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
                    <div class="title"><b>物理路径修改</b></div>
                    <span style="font-size: 8px;line-height: 20px;margin-left: 200px"   >！！！此功能可用于服务器迁移之后，证书文件路径的修改&nbsp;&nbsp;(胡乱使用会导致商城无法使用，非技术人员不建议使用)</span>
                    <el-form-item label="旧路径">
                        <el-input v-model="form.old_url" placeholder="请输入旧路径" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="新路径">
                        <el-input v-model="form.new_url" placeholder="请输入新路径" style="width:70%;"></el-input>
                    </el-form-item>
                </div>
        </div>
        <div class="confirm-btn">
            <el-button type="primary" @click="submit">提交</el-button>
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
                activeName: 'first',
                form:{
                    old_url:'',
                    new_url:'',
                },
            }
        },
        mounted () {
            //this.getData();
        },
        methods: {
            getData(){
                this.$http.post('{!! yzWebFullUrl('siteSetting.index.physics-path1') !!}').then(function (response){
                    if (response.data.result) {
                        this.form=response.data.data.set
                    }else {
                        this.$message({message: response.data.msg,type: 'error'});
                    }
                },function (response) {
                    this.$message({message: response.data.msg,type: 'error'});
                })
            },
            submit() {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('siteSetting.index.physics-path') !!}',{'physics':this.form}).then(function (response){
                    if (response.data.result) {
                        this.$message({message: "提交成功",type: 'success'});
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
