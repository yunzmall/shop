@extends('layouts.base')
@section('title', '自定义表单')

@section('content')
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
            padding:10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
            background-color:#fff;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
            margin-bottom:10px;
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
            justify-content:space-between;
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
        .el-form-item__label{
            margin-right:30px;
        }
        .add{
            width: 100px;
            height: 36px;
            border-radius: 4px;
            border: solid 1px #29ba9c;
            color:#29ba9c;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .list-wrap{
            padding-left:10%;
        }
        .list{
            width: 911px;
            height: 159px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-bottom:20px;
            box-sizing:border-box;
            padding:40px 0;
            position:relative;
        }
        .list .delete{
            width: 26px;
            height: 26px;
            background-color: #dee2ee;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            position:absolute;
            right:0;
            top:0;
            font-size:16px;
        }

        b{
            font-size:14px;
        }
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con" >
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div></div>
                        <el-form-item label="姓名">
                            <div style="display: flex;align-items: center;">
                                <el-switch
                                        v-model="base.name"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                                <div>
                                    <span style="margin: 0 30px 0 80px; font-size: 14px; color: #666;font-weight: 400;">姓名是否必填</span>
                                    <el-switch
                                            v-model="base.name_must"
                                            active-value="1"
                                            inactive-value="0"
                                    >
                                    </el-switch>
                                </div>
                            </div>
                        </el-form-item>

                        <el-form-item label="性别">
                            <template>
                                <el-switch
                                        v-model="base.sex"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="详细地址">
                            <template>
                                <el-switch
                                        v-model="base.address"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="生日">
                            <template>
                                <el-switch
                                        v-model="base.birthday"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="修改昵称头像">
                            <template>
                                <el-switch
                                        v-model="base.change_info"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div>开启后，前端可修改会员头像昵称，若开启微信授权登录，修改后会被微信的头像昵称替换</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>自定义</b><span></div><el-button type="primary"  @click="addBlock">添加</el-button></div>
                        <el-form-item label="自定义必填开启">
                            <template>
                                <el-switch
                                        v-model="base.form_open"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div>开启后，自定义字段必填</div>
                        </el-form-item>
                        <el-form-item label="前端禁止编辑">
                            <template>
                                <el-switch
                                        v-model="base.form_edit"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div>开启后，前端会员设置不可编辑</div>
                        </el-form-item>
                        <div class="list-wrap">
                            <div class="list" v-for="(item,index) in form.sort">
                                <div>
                                    <span style="display:inline-block;margin-right:30px;width:150px;text-align:right;">排序</span>
                                    <el-input v-model="form.sort[index]"  name="form[sort][]" style="width:60%;"></el-input>
                                </div>
                                <div style="margin-top:20px;">
                                    <span style="display:inline-block;margin-right:30px;width:150px;text-align:right;">自定义字段名称</span>
                                    <el-input v-model="form.name[index]"  name ="form[name][]" style="width:60%;"></el-input>
                                </div>
                                <a style="color:#666;"><div class="delete" @click="Delete(index)">x</div></a>
                            </div>
                        </div>
                    </div>
                </el-form>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    activeName: 'one',
                    form:{
                        sort:[],
                        name:[]
                    },
                    base :{
                        name:'0',
                        sex:'0',
                        address:'0',
                        birthday:'0',
                        name_must:'0',
                        change_info:'0',
                    }
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                Delete(i){

                    this.form.sort.splice(i,1)
                    this.form.name.splice(i,1)
                },
                addBlock(){
                    this.form.sort.push("")
                    this.form.name.push("")
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.form.index') !!}').then(function (response){
                        if (response.data.result) {
                            if(response.data.data.set.form && response.data.data.set.form instanceof Array == true) {
                                response.data.data.set.form.forEach((item,index) => {
                                    this.form.sort.push(item.sort)
                                    this.form.name.push(item.name)
                                })
                            }
                            if(response.data.data.set.base){
                                for(let i in response.data.data.set.base){
                                    this.base[i]=response.data.data.set.base[i]
                                }
                            }
                            console.log(this.base)
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.form.index') !!}',{'form':this.form,'base':this.base}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                            loading.close();
                            location.reload();
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection