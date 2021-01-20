@extends('layouts.base')
@section('content')
@section('title', trans('缓存设置'))
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

            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                <el-form ref="form" :model="form" label-width="15%">
                    <el-form-item label="是否开启数据库查询缓存">
                        <template>
                            <el-switch
                                    v-model="form.select.open"
                                    active-value= "1"
                                    inactive-value="0"
                            >
                            </el-switch>
                        </template>
                    </el-form-item>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
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
            let is_open = {!! $set['select']['open'] ?: '0'!!}
                return {
                    activeName: 'first',
                    form:{
                        select:{
                            open: String(is_open)
                        },
                    },
                }
        },
        mounted () {
        },
        methods: {
            submit() {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('setting.cache.index') !!}',{'cache':this.form}).then(function (response){
                    if (response.data.result) {
                        this.$message({message: response.data.msg,type: 'success'});
                    }
                    else {
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
