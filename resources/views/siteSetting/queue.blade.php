@extends('layouts.base')
@section('title', '队列设置')
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
  <div id='re_content' >
  @include('layouts.newTabs')
    <div class="con">
        <div class="setting">
            <div class="block">
            <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>队列设置</b></div>
            <el-form ref="form" :model="form" label-width="15%">
                <el-form-item label="队列执行错误消息" prop="service_type">
                    <el-radio v-model="form.receive_message" :label="0">开启</el-radio>
                    <el-radio v-model="form.receive_message" :label="1">关闭</el-radio>
                </el-form-item>
            <el-form-item label="普通队列进程数">
                    <el-input placeholder=""
                              v-model="form.default"
                              style="width: 70%">
                    </el-input>
                </el-form-item>
                <el-form-item label="微信消息队列进程数">
                    <el-input placeholder=""
                              v-model="form.message"
                              style="width: 70%">
                    </el-input>
                </el-form-item>

                <el-form-item label="订单队列进程数">
                    <el-input placeholder=""
                              v-model="form.order"
                              style="width: 70%">
                    </el-input>
                </el-form-item>
                </el-form>
            </div>
        </div>
        <div class="confirm-btn">
            <el-button type="primary" @click="onSubmit">提交</el-button>
        </div>
    </div>
</div>

    <script>
        var app = new Vue({
            el: '#re_content',
            delimiters: ['[[', ']]'],
            data() {
                // 默认数据
                let temp = JSON.parse('{!! $setting !!}');
                if (!temp || temp.length === 0) {
                    temp = {
                    }
                }

                return {
                    form: temp,
                    loading: false,
                    formLoading: false,
                    centerDialogVisible: false,
                }
            },
            mounted: function () {
                console.log(this.form)
            },
            methods: {

                onSubmit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    if (this.formLoading) {
                        return;
                    }
                    this.formLoading = true;

                    this.$refs.form.validate((valid) => {
                        console.log(valid)
                    });
                    this.$http.post("{!! yzWebUrl('siteSetting.store.queue') !!}", {'setting': this.form}).then(response => {
                        //console.log(response.data);
                        // return;
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
                        loading.close();
                        location.reload();
                    }, response => {
                        console.log(response);
                    });
                },


            }
        });
    </script>
@endsection

