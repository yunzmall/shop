@extends('layouts.base')
@section('content')
<style>
.panel{
    margin-bottom:10px!important;
    margin-top:10px!important;
    border-radius: 10px;
    padding-left: 20px;
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
    border-radius: 8px;
}
.con .setting .block{
    padding:10px;
    background-color:#fff;
    border-radius: 8px;
}
.con .setting .block .title{
    display:flex;
    align-items:center;
    margin-bottom:15px;
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
textarea{
    height:150px;
}
b{
    font-size:14px;
}
</style>
<div id='re_content' >
    <div class="con">
        <div class="setting">
            <div class="block">
            <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
            <el-form  label-width="15%">
                <el-form-item label="模板ID">
                    <div>[[template.priTmplId]]</div>
                </el-form-item>
                <el-form-item label="模板名称">
                    <div>[[template.title]]</div>
                </el-form-item>
                <el-form-item label="模板格式">
                    <el-input   type="textarea" v-model="template.content" disabled style="width:70%;"></el-input>
                </el-form-item>
                <el-form-item label="模板示例">
                    <el-input   type="textarea" v-model="template.example" disabled  style="width:70%;"></el-input>
                </el-form-item>
                </el-form>
            </div>
        </div>
        <div class="confirm-btn">
            <el-button type="primary" @click="back">返回列表</el-button>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
          let template = {!! json_encode($template) ?: '{}' !!}
          console.log(template)
           return {
                activeName: 'first',
                template:template
           }
        },
        mounted () {
         
        },
        methods: {
            back(){
                window.location.href = `{!! yzWebFullUrl('setting.small-program.index') !!}`;
            },
        },
    });
</script>
@endsection
