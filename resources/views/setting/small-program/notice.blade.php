@extends('layouts.base')

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
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
            background: #fff;
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

        .add-people{
            width: 91px;
            height: 91px;
            border: dashed 1px #dde2ee;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
        }

    </style>
    <div id='re_content' >
        @include('layouts.newTabs')

        <div class="con">
            <div class="setting">
                <el-form ref="form"  label-width="15%" v-if="temp_list.length>0">
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>基础设置
                            </b>
                            <i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
                                <el-popover
                                        placement="bottom-start"
                                        title="提示"
                                        width="400"
                                        trigger="hover"
                                        content="小程序公众平台服务类目请添加商家自营 > 美妆/洗护。
                点击模版消息后方开关按钮即可开启默认模版消息，无需进行额外设置。
                ">
                                    <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
                                </el-popover>
                            </i>
                        </div>
                        <template v-for="(item,index,key) in temp_list">
                            <el-form-item   :label="item.title">
                                <template>
                                    <el-switch
                                            @change="changeVal(item.id)"
                                            v-model="item.is_open"
                                            :active-value="1"
                                            :inactive-value="0"
                                    >
                                    </el-switch>
                                </template>
                            </el-form-item>
                            <el-form-item :label="item.title">
                                <el-input :value="item.title" disabled style="width:70%;"></el-input>
                            </el-form-item>
                        </template>
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
                    temp_list:[],
                    activeName: 'one',
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                changeVal(val){
                    let that=this;
                    var open=false
                    this.temp_list.forEach((item,index,key)=>{
                        if(item.id==val){
                            open=item.is_open
                        }
                    })
                    var postdata={
                        id:val,
                        open:open
                    }
                    this.$http.post("{!! yzWebUrl('setting.small-program.set-notice') !!}",postdata).then(function (response){
                        if (response.data.result==1) {
                            this.$message({message:'操作成功',type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.small-program.notice') !!}').then(function (response){
                        if (response.data.result) {
                            this.temp_list=response.data.data.temp_list;

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
