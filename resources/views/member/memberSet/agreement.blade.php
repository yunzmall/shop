    @extends('layouts.base')
    @section('title', '注册协议')
    @section('content')
    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style scoped>
        .main-panel{
            margin-top:50px;
        }
        .main-panel #re_content {
            padding: 10px;
        }
        .panel{
            margin-bottom:10px!important;
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
        .content{
            background: #eff3f6;
            padding:10px!important;
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
        .block{
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
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 注册协议 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">注册协议</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="是否启用">
                                <template>
                                    <el-switch
                                            v-model="form.register_status"
                                            :active-value="1"
                                            :inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                            </el-form-item>
                            <el-form-item label="协议链接" prop="">
                                <el-input v-model="form.register_agreement_url" style="width:70%;" readonly ref="register_agreement_url"></el-input>
                                <el-button @click="copyLink('register_agreement_url')">复制</el-button>
                            </el-form-item>
                            <el-form-item label="小程序路径" prop="">
                                <el-input v-model="form.register_agreement_mini_url" style="width:70%;" readonly ref="register_agreement_mini_url"></el-input>
                                <el-button @click="copyLink('register_agreement_mini_url')">复制</el-button>
                            </el-form-item>
                            <el-form-item label="注册协议默认勾选">
                                <template>
                                    <el-switch
                                            v-model="form.register_default_tick"
                                            :active-value="1"
                                            :inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                            </el-form-item>
                            <el-form-item label="协议标题">
                                <el-input v-model="form.register_title" style="width:70%;" placeholder="会员注册协议"></el-input>
                            </el-form-item>
                            <el-form-item label="协议内容">
                                <tinymceee v-model="form.register_content" style="width:70%;"></tinymceee>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">平台协议</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="是否开启" prop="is_agreement">
                                <el-switch v-model="form.is_agreement" :active-value="1" :inactive-value="0"></el-switch>
                            </el-form-item>
                            <el-form-item label="平台协议自定义名称">
                                <el-input v-model="form.agreement_name" placeholder="请输入平台协议自定义名称" style="width:70%;"></el-input>
                            </el-form-item>
                            <el-form-item label="平台协议" prop="agreement">
                                <tinymceee v-model="form.agreement" style="width:70%" ></tinymceee>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </div>

            </div>
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    @include('public.admin.tinymceee')
    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                return {
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                }
            },
            created() {
            },
            methods: {
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.agreement-store') !!}';
                    let json = {
                        set : this.form
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});

                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        // location.reload();
                    },response => {
                        loading.close();
                    });
                },
                copyLink(type) {
                    this.$refs[type].select();
                    document.execCommand("Copy")
                    this.$message.success("复制成功!");
                },
            }
        })
    </script>
    @endsection