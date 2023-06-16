@extends('layouts.base')
@section('content')
    <style>
        .panel {
            margin-bottom: 10px !important;
            padding-left: 20px;
            border-radius: 10px;
        }

        .panel .active a {
            background-color: #29ba9c !important;
            border-radius: 18px !important;
            color: #fff;
        }

        .panel a {
            border: none !important;
            background-color: #fff !important;
        }

        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .con {
            padding-bottom: 40px;
            position: relative;
            min-height: 100vh;
            background-color: #fff;
        }

        .con .setting .block {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .con .setting .block .title {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .confirm-btn {
            width: calc(100% - 266px);
            position: fixed;
            bottom: 0;
            right: 0;
            margin-right: 10px;
            line-height: 63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px rgba(51, 51, 51, 0.3);
            background-color: #fff;
            text-align: center;
        }

        b {
            font-size: 14px;
        }
    </style>
    <div id='re_content'>
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>配置信息</b></div>
                        <br>
                        <el-form-item label="邮箱类型">
                            <template>
                                <el-radio-group v-model="form.email_type">
                                    <el-radio label="0">qq邮箱</el-radio>
                                    <el-radio label="1">163邮箱</el-radio>
                                </el-radio-group>
                            </template>
                        </el-form-item>
                        <el-form-item label="发件人邮箱">
                            <el-input v-model="form.send_email" style="width:70%;" placeholder="请输入发件人邮箱"></el-input>
                        </el-form-item>
                        <el-form-item label="邮箱授权码">
                            <el-input v-model="form.password" style="width:70%;" placeholder="请输入邮箱授权码"></el-input>
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
                    form: {
                        send_email: '',
                        password: '',
                        email_type: '0'
                    },
                }
            },
            mounted() {
                this.getData();
            },
            methods: {
                getData() {
                    this.$http.post('{!! yzWebFullUrl('setting.shop.email') !!}').then(function (response) {
                        if (response.data.result) {
                            for (let i in response.data.data.set) {
                                this.form[i] = response.data.data.set[i]
                            }
                        } else {
                            this.$message({message: response.data.msg, type: 'error'});
                        }
                    }, function (response) {
                        this.$message({message: response.data.msg, type: 'error'});
                    })
                },
                submit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('setting.shop.email') !!}', {'email': this.form}).then(function (response) {
                        if (response.data.result) {
                            this.$message({message: response.data.msg, type: 'success'});
                        } else {
                            this.$message({message: response.data.msg, type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    }, function (response) {
                        this.$message({message: response.data.msg, type: 'error'});
                    })
                },

            },
        });
    </script>
@endsection
