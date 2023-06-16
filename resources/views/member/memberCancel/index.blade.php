@extends('layouts.base')
@section('title', '账号注册管理设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .panel-info {
            margin: 6px 0;
        }
        .add-shopnav {
            padding: 5px;
        }
        .add-shopnav li.active a{
            padding: 4px 12px;
            letter-spacing: 2px;
            font-size: 14px;
            border-radius: 5px!important;
        }
        .main-panel{
            margin-top:50px;
        }
        .panel{
            margin-bottom: 20px!important;
            border-radius: 10px;
            padding-left: 5px;
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
            padding:15px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
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
        .el-checkbox__inner{
            border:solid 1px #56be69!important;
        }
        .upload-boxed .el-icon-close {
            position: absolute;
            top: -5px;
            right: -5px;
            color: #fff;
            background: #333;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
    <div id='re_content' >
        <div class="panel panel-info">
            <ul class="add-shopnav">
                <li class="active">
                    <a href="{{ yzWebFullUrl('member.member-cancel.index') }}">
                        账号注销设置
                    </a>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </li>
                <li>
                    <a href="{{ yzWebFullUrl('member.member-cancel.verify') }}">
                        账号注销申请审核
                    </a>
                </li>
            </ul>
        </div>

        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>账号注销设置</b></div>
                        <el-form-item label="账号注销功能开启" prop="status">
                            <el-switch v-model="form.status" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="手机验证码开启" prop="tel_status">
                            <el-switch v-model="form.tel_status" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="账号注销申请标题">
                            <el-input v-model="form.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="申请协议内容" prop="content">
                            <tinymceee v-model="form.content" style="width:70%"></tinymceee>
                        </el-form-item>
                    </div>
                    <div class="confirm-btn">
                        <el-button type="primary" @click="submit">提交</el-button>
                    </div>
                </el-form>
            </div>
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    <!-- @include('public.admin.uploadImg') -->
    @include('public.admin.tinymceee')
    @include('public.admin.uploadMultimediaImg')

    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! $set ?: '{}' !!};
                return {
                    activeName: 'one',
                    level:[],
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    form:{
                        status:1,
                        tel_status:1,
                        title:'',
                        content:'',
                        ...set,
                    },
                }
            },
            mounted () {
            },
            methods: {
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type;
                    this.selNum = sel
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,uploadShow,fileList) {
                    if(fileList.length <= 0) {
                        return
                    }
                    this.form[name] =fileList[0].attachment;
                    this.form[name+'_url'] = fileList[0].url;
                },

                getInfo(){
                    this.$forceUpdate()
                },
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('member.member-cancel.index') !!}',{'form':this.form}).then(function (response){
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
