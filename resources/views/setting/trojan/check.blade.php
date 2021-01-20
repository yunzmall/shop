@extends('layouts.base')
@section('title', '检查木马文件')
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
            width: 100%;
            position:absolute;
            bottom:0;
            left:0;
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
        <template>

        </template>
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>检查木马文件</b>
                        <div style="margin-left: 50px">
                            <el-button type="warning" @click="submit" size="mini">检查</el-button>
                        </div>
                    </div>
                    <div>
                        <el-alert v-for="(item ,i) in file" center @close="Del"
                                  type="error">[[item]]
                        </el-alert>
                    </div>

                </div>
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
                    file: [],
                }
            },
            mounted () {

            },
            methods: {
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0.2)'});
                    this.$http.post('{!! yzWebFullUrl('setting.trojan.check') !!}',{'trojan':'check'}).then(function (response) {
                            if (response.data.result) {
                                if(response.data.data.files.length>0){
                                    this.$message({type: 'waring',message: '发现可疑文件!'});
                                    this.file =  response.data.data.files
                                }else{
                                    this.$message({type: 'success',message: '暂无可疑文件!'});
                                }

                            }
                            else{
                                this.$message({type: 'error',message: response.data.msg});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({type: 'error',message: response.data.msg});
                            loading.close();
                        }
                    );

                },
                apiDel(){
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0.2)'});
                    this.$http.post('{!! yzWebFullUrl('setting.trojan.del') !!}',{'files':this.file}).then(function (response) {
                            if (response.data.result) {
                                this.$message({type: 'success',message: '删除成功!'});
                            }
                            else{
                                this.$message({type: 'error',message: '删除失败!'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({type: 'error',message: response.data.msg});
                            loading.close();
                        }
                    );
                },
                del(){
                    this.$confirm('将删除该文件, 是否确定?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        this.apiDel();
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消删除'
                        });
                    });

                }
            },


        });
    </script>
@endsection('content')
