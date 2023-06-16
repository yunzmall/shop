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
            padding-bottom: 20px;
            position: relative;
            border-radius: 8px;
            min-height: 100vh;
            background-color: #fff;
        }

        .con .setting .block {
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
        }

        .con .setting .block .title {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
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
                <el-form ref="form" label-width="15%">
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>数据清理</b>
                        </div>
                        <el-form-item :label="item.name" v-for="item in plugin_data">
                            <el-button style="margin-left: 60%" size="mini" type="primary" @click="setTimeBox(item.name,item.plugin)">清理</el-button>
                        </el-form-item>
                    </div>
                </el-form>

                <el-dialog :visible.sync="setTime" width="50%" center :title="name">
                    <div>
                        <el-form ref="form1" label-width="15%">
                            <el-form-item label="需要清理的时间">
                                <el-date-picker
                                        v-model="times"
                                        type="datetimerange"
                                        value-format="timestamp"
                                        range-separator="至"
                                        start-placeholder="开始日期"
                                        end-placeholder="结束日期"
                                        style="margin-left:5px;"
                                        align="right">
                                </el-date-picker>
                            </el-form-item>
                            <div style="text-align: center">
                                <el-button type="primary" @click="submit()">提交</el-button>
                                <el-button type="danger" @click="cancel()">取消</el-button>
                            </div>
                        </el-form>
                    </div>
                </el-dialog>
            </div>
        </div>
    </div>
    <script>
        new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let config = JSON.parse(`{!! json_encode($data) !!}`);
                return {
                    times:[],
                    setTime:false,
                    name:'',
                    plugin:'',
                    plugin_data:config,
                }
            },
            methods: {
                submit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    let search = {
                        plugin:this.plugin,
                        start:'',
                        end:''
                    };
                    if(this.times && this.times.length>0) {
                        search.start = this.times[0]/1000;
                        search.end   = this.times[1]/1000;
                    }
                    this.$http.post("{!! yzWebFullUrl('setting.shop.clear-handle') !!}", search).then(function(response) {
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
                        loading.close();
                    }, function(response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    })
                },
                setTimeBox(name,plugin){
                    this.name = name;
                    this.plugin = plugin;
                    this.setTime = true;
                },
                cancel(){
                    this.name = '';
                    this.plugin = '';
                    this.setTime = false;
                }
            },
        });
    </script>
@endsection