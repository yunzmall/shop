
@extends('layouts.base')
@section('title', '服务器设置')
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

        b{
            font-size:14px;
        }
    </style>

    <div id="re_content">

        @include('layouts.newTabs')

        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>IP设置</b></div>
                    <el-form ref="form" :rules="rules" :model="form" label-width="15%">
                        <el-form-item label="服务器类型" prop="service_type">
                            <el-radio v-model="form.service_type" :label="0">单服</el-radio>
                            <el-radio v-model="form.service_type" :label="1">集群</el-radio>
                        </el-form-item>
                        <el-form-item v-if="!form.service_type" label="ip 地址">
                            <el-form-item prop="address.ip">
                                <el-input :placeholder="form.address.ip" v-model.String="form.address.ip" style="width: 27%">
                                </el-input>
                                <p class="help-block">为空默认：http://127.0.0.1</p>
                            </el-form-item>
                        </el-form-item>
                        <div v-else>
                            <div  v-for="(item,index) in form.service" >
                                <el-form-item :label="`服务器${index+1}:ip地址`" prop="address.ip">
                                    <el-input :placeholder="form.address.ip" v-model ="form.service[index]" style="width: 27%">
                                    </el-input>
                                    <el-button v-if="index" size="small" @click="delService(index)" type="primary">删除</el-button>
                                </el-form-item>
                            </div>
                            <el-form-item  label="" prop="name">
                                <el-button @click="addService" type="primary">添加服务器</el-button>
                            </el-form-item>
                        </div>
                    </el-form>
                </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click.native.prevent="onSubmit" v-loading="formLoading">提交</el-button>
            </div>
        </div>
    </div>
    <script>
        var app = new Vue({
            el: '#re_content',
            delimiters: ['[[', ']]'],
            data() {
                // 默认数据

                let temp = JSON.parse('{!! $setting?:'{}' !!}');
                console.log(temp);
                let temp1 = {
                    address: {
                        'ip': 'http://127.0.0.1',
                    },
                    service_type:0,
                    service:[
                        'http://127.0.0.1',
                    ],
                    ...temp,
                }
                let rules = {
                    // 'service.name': [],
                };
                return {
                    form: temp1,
                    props: {
                        label: 'areaname',
                        children: 'children',
                        isLeaf: 'isLeaf'
                    },
                    name:'11111',
                    loading: false,
                    formLoading: false,
                    centerDialogVisible: false,
                    treeData: [],
                    rules: rules
                }
            },
            mounted: function () {
                console.log(this.form.address.ip,'2222')
            },
            methods: {
                onSubmit() {
                    if (this.formLoading) {
                        return;
                    }
                    this.formLoading = true;

                    this.$refs.form.validate((valid) => {
                        console.log(valid)
                    });
                    this.$http.post("{!! yzWebUrl('supervisord.supervisord.store') !!}", {'setting': this.form}).then(response => {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        this.formLoading = false;
                    }, response => {
                        console.log(response);
                    });
                },
                goBack() {
                    window.history.back();
                },
                checkAreas(node,checked,children) {
                    if(node.isLeaf){
                        return;
                    }
                    if(checked){

                    }
                },
                addService() {
                    this.form.service.push('');
                },
                delService(index) {
                    this.form.service.splice(index,1)
                },

            }
        });
    </script>
@endsection
