@extends('layouts.base')
@section('title', trans('指定支付设置'))
@section('content')
    <style>
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
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
        .cert{
            width: 115px;
            height: 42px;
            background-color: #29ba9c;
            border-radius: 4px;
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size: 16px;
            display: inline-block;
            text-align: center;
        }

        b{
            font-size:14px;
        }
        input[type=file]{
            display:none;
        }
    </style>
    <div id='re_content' >
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%" >
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额充值指定支付设置</b></div>
                        <el-form-item label="余额充值指定支付" prop="">
                            <el-switch v-model="form.appoint_pay" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">开启后前端余额充值支付走该设置，关闭走后台系统-支付方式的设置</div>
                        </el-form-item>
                        <div v-if="form.appoint_pay==1">
                            <el-form-item label="微信" prop="">
                                <el-radio v-model.number="form.wechat" :label="1">开启</el-radio>
                                <el-radio v-model.number="form.wechat" :label="0">关闭</el-radio>
                            </el-form-item>
                            <el-form-item label="">
                                <el-input v-model="form.wechat_limit" style="width:70%;" placeholder="为0为空则不限制">
                                    <template slot="prepend">单笔最大充值金额</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="支付宝" prop="">
                                <el-radio v-model.number="form.alipay" :label="1">开启</el-radio>
                                <el-radio v-model.number="form.alipay" :label="0">关闭</el-radio>
                            </el-form-item>
                            <el-form-item label="">
                                <el-input v-model="form.alipay_limit" style="width:70%;" placeholder="为0为空则不限制">
                                    <template slot="prepend">单笔最大充值金额</template>
                                </el-input>
                            </el-form-item>
                            @if(app('plugins')->isEnabled('converge_pay'))
                                <el-form-item label="汇聚微信" prop="">
                                    <el-radio v-model.number="form.pay_wechat_hj" :label="1">开启</el-radio>
                                    <el-radio v-model.number="form.pay_wechat_hj" :label="0">关闭</el-radio>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-input v-model="form.pay_wechat_hj_limit" style="width:70%;" placeholder="为0为空则不限制">
                                        <template slot="prepend">单笔最大充值金额</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="汇聚支付宝" prop="">
                                    <el-radio v-model.number="form.pay_alipay_hj" :label="1">开启</el-radio>
                                    <el-radio v-model.number="form.pay_alipay_hj" :label="0">关闭</el-radio>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-input v-model="form.pay_alipay_hj_limit" style="width:70%;" placeholder="为0为空则不限制">
                                        <template slot="prepend">单笔最大充值金额</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="汇聚快捷支付" prop="">
                                    <el-radio v-model.number="form.converge_quick_pay" :label="1">开启</el-radio>
                                    <el-radio v-model.number="form.converge_quick_pay" :label="0">关闭</el-radio>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-input v-model="form.converge_quick_pay_limit" style="width:70%;" placeholder="为0为空则不限制">
                                        <template slot="prepend">单笔最大充值金额</template>
                                    </el-input>
                                </el-form-item>
                            @endif
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
                let set = {!! json_encode($set) !!};
                return {
                    activeName: 'first',
                    show:true,

                    form:{
                        ...set
                    },
                }
            },
            mounted () {

            },
            methods: {
                submit() {
                    console.log(this.form);
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('finance.balance-recharge-set.store') !!}',{set:this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                            location.reload();
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection