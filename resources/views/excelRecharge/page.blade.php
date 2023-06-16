@extends('layouts.base')
@section('title','EXCEL 批量充值')

@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .el-dropdown-menu__item {
            padding: 0;
        }

        .el-dropdown-menu {
            padding: 0;
        }
        .boom-button {
            margin-top: -20px;
            background: #ffffff;
            height: 60px;
            display: flex;
            flex-direction: row;
            justify-content:center;
            align-items: center;
        }
        .boom-button button {
            height: 40px;
        }
        .vue-page {
            right: unset;
        }
    </style>
    <div id="app-remittance-audit" xmlns:v-bind="http://www.w3.org/1999/xhtml">
        <div class="block" style="padding:0 15px 15px 15px">
            <div class="vue-main-form" style="background: #ffffff;border-radius: 10px 10px 0 0;height: 100vh;margin-top: unset">
                <div class="vue-main-title" style="margin-bottom:20px;margin-top: unset;padding-top: 15px;">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">
                        批量充值
                    </div>
                </div>
                <div class="alert alert-info alert-important" style="width: 90%;margin: 100px auto 50px;">
                    <span>功能介绍:</span>
                    <span style="padding-left: 60px;">1. 使用excel快速导入进行会员充值（积分、余额）, 文件格式<b
                                style="color:red;">[xls]</b></span>
                    <span style="padding-left: 60px;">2. 一次导入的数据不要太多,大量数据请分批导入,建议在服务器负载低的时候进行</span>
                    <br>
                    <span>使用方法:</span>
                    <span style="padding-left: 60px;">1. 下载Excel模板文件并录入信息</span>
                    <span style="padding-left: 60px;">2. 选择充值类型</span>
                    <span style="padding-left: 60px;">3.选择第一列值的类型 </span>
                    <span style="padding-left: 60px;">4. 上传Excel导入</span>
                    <br>
                    <span>格式要求： Excel第一列可以为会员ID或者手机号(根据选择的上传第一列值得类型决定)，第二列必须为充值数量</span>
                </div>
                <el-form ref="" :model="form" label-width="17%">
                    <el-form-item label="充值类型">
                        <el-select v-model="form.batch_type" placeholder="请选择">
                            <el-option
                                    v-for="(item,index) in batch_type"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                        <el-select v-if="form.batch_type=='love'" v-model="form.love_type" placeholder="请选择">
                            <el-option
                                    v-for="(item,index) in love_type"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="上传第一列的值">
                        <el-select v-model="form.genre" placeholder="请选择">
                            <el-option
                                    v-for="(item,index) in genre"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="EXCEL文件">
                        <input type="file" id="file" hidden @change="fileChange($event)">
                        <el-button type="success" @click="btnChange" plain>选择文件</el-button>
                        <div v-if="file_name">[[file_name]]
                            <el-link :underline="false" @click="delFile"><i class="el-icon-close"></i></el-link>
                        </div>
                        <div style="margin-bottom: 25%"></div>
                    </el-form-item>
                </el-form>
                <!-- 分页 -->
                <div class="vue-page boom-button" style="border-top:4px solid #f2f2f2;width: calc(100% - 276px);">
                    <el-button type="primary" @click="affirmExport()">确认导入</el-button>
                    <el-button type="primary" @click="navRechargeRecord()">充值记录</el-button>
                    <el-button @click="downloadFormwork(1)">下载EXCEL模板文件（会员id）</el-button>
                    <el-button @click="downloadFormwork(2)">下载EXCEL模板文件（手机号）</el-button>
                </div>
            </div>

        </div>


    </div>
    <script>
        let app = new Vue({
            el: '#app-remittance-audit',
            delimiters: ['[[', ']]'],
            data() {
                return {
                    form: {
                        genre: '',
                        batch_type: '',
                        batch_recharge: '',
                        love_type: '',
                    },
                    batch_type: [
                        {
                            value: 'point',
                            name: '积分'
                        },
                        {
                            value: 'balance',
                            name: '充值余额'
                        }
                    ],
                    genre: [
                        {
                            value: 1,
                            name: '会员ID'
                        },
                        {
                            value: 2,
                            name: '手机号'
                        }
                    ],
                    love_type: [
                        {
                            value: 'froze',
                            name: '冻结'
                        },
                        {
                            value: 'usable',
                            name: '可用'
                        }
                    ],
                    loveName: '',
                    loveOpen: '',
                    set: {},
                    file_name: '',
                }
            },
            mounted: function () {
                this.getData()
            },
            methods: {
                affirmExport() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    let formdata = new FormData();
                    formdata.append("batch_recharge", this.form.batch_recharge);
                    formdata.append("batch_type", this.form.batch_type);
                    formdata.append("genre", this.form.genre);
                    formdata.append("love_type", this.form.love_type);
                    formdata.append("cancelsend", 'yes');
                    this.$http.post('{!! yzWebFullUrl('excelRecharge.confirm.index') !!}', formdata).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                delFile() {
                    this.file_name = ''
                    this.form.batch_type = ''
                },
                navRechargeRecord() {
                    window.open('{!!yzWebUrl('excelRecharge.records.index')!!}')
                },
                downloadFormwork(id) {
                    window.open('{!!yzWebUrl('excelRecharge.example.index')!!}' + '&id=' + id)
                },
                getFile() {
                    this.$refs.file.click()
                },
                fileChange(e) {
                    this.form.batch_recharge = e.target.files[0]
                    this.file_name = e.target.files[0].name
                    console.log(this.file_name)
                },
                btnChange() {
                    var file = document.getElementById('file')
                    file.click()
                },
                getData() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('excelRecharge.page.index') !!}').then(function (response) {
                        if (response.data.result) {
                            this.love_name = response.data.data.loveName
                            this.love_open = response.data.data.loveOpen
                            if (this.love_open === 1) {
                                this.batch_type.push({
                                    value: 'love',
                                    name: this.love_name
                                })
                            }
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
            }
        });
    </script>

@endsection('content')
