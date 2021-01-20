@extends('layouts.base')

@section('content')
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
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
        .con .confirm-btn{
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
    </style>
    <div id='re_content' >
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="18%">
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>自动关闭未付款订单</b></div>
                        <el-form-item label="自动关闭未付款订单天数">
                            <el-input v-model="form.close_order_days"  style="width:70%;"></el-input>
                            <div style="padding:5px;background-color: #f6f7f9;width:60%;margin-top:5px;">
                                <div style="line-height:20px!important;font-size: 12px;">1.订单下单未付款，n天后自动关闭，0/空为不自动关闭</div>
                                <div style="line-height:20px!important;font-size: 12px;">2.退换货处理中的订单不能自动关闭</div>
                            </div>
                        </el-form-item>
                        <el-form-item label="自动关闭未付款订单执行间隔时间">
                            <el-input v-model="form.close_order_time"  style="width:70%;"></el-input>
                            <span style="margin-left:10px;">分钟</span>
                            <div style="font-size: 12px;">执行自动关闭未付款订单操作的间隔时间，如果为空默认为 5分钟 执行一次关闭到期未付款订单</div>
                        </el-form-item>
                </el-form>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>自动收货</b></div>
                <el-form-item label="自动收货天数">
                    <el-input v-model="form.receive"  style="width:70%;"></el-input>
                    <div style="padding:5px;background-color: #f6f7f9;width:60%;margin-top:5px;">
                        <div style="line-height:20px!important;font-size: 12px;">1.订单下单未付款，n天后自动关闭，0/空为不自动收货</div>
                        <div style="line-height:20px!important;font-size: 12px;">2.退换货处理中的订单不能自动收货</div>
                        <div style="line-height:20px!important;font-size: 12px;">3.配送方式为自提,酒店入住,配送站送货,司机配送,自提点的配送类型不支持自动收货</div>
                    </div>
                </el-form-item>
                <el-form-item label="自动收货执行间隔时间">
                    <el-input v-model="form.receive_time"  style="width:70%;"></el-input>
                    <span style="margin-left:10px;">分钟</span>
                    <div style="font-size: 12px;">执行自动收货操作的间隔时间，如果为空默认为 5分钟 执行一次自动收货</div>
                </el-form-item>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>交易设置</b></div>

                <el-form-item label="退款">
                    <template>
                        <el-switch
                                v-model="form.refund_status"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </template>
                    <div style="font-size: 12px;">开关判断前端退款按钮是否显示</div>
                </el-form-item>
                <!-- <el-form-item label="快递配送">
                <template>
                        <el-switch
                        v-model="form.is_dispatch"
                        active-value="0"
                        inactive-value="1"
                        >
                        </el-switch>
                    </template>
                </el-form-item> -->
                <el-form-item label="完成订单多少天内可申请退款">
                    <el-input v-model="form.refund_days"  style="width:70%;"></el-input>
                    <div style="font-size: 12px;">订单完成后 ，用户在x天内可以发起退款申请，设置0天不允许完成订单退款</div>
                </el-form-item>
                <el-form-item label="公众号支付后跳转链接">
                    <el-input v-model="form.redirect_url"  style="width:70%;"></el-input><el-button @click="show=true" style="margin-left:10px;">选择链接</el-button>
                    <div style="font-size: 12px;">当用户下单支付后，跳转到指定的页面，默认跳转到商城首页</div>
                </el-form-item>
                <el-form-item label="小程序支付后跳转链接">
                    <el-input v-model="form.min_redirect_url"  style="width:70%;"></el-input><el-button @click="pro=true" style="margin-left:10px;">选择小程序链接</el-button>
                    <div style="font-size: 12px;">当用户下单支付后，跳转到指定的页面</div>
                </el-form-item>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>发票设置</b></div>

                <el-form-item label="支持发票类型">
                    <template>
                        <el-checkbox label="纸质发票" v-model="form.invoice.papery" :false-label="0"  :true-label="1"></el-checkbox>
                        <el-checkbox label="电子发票" v-model="form.invoice.electron"  :false-label="0"  :true-label="1"></el-checkbox>
                    </template>
                    <div>当商品支持开发票时，买家可以选择以上勾选的类型</div>
                </el-form-item>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>收货地址</b></div>

                <el-form-item label="是否开启乡镇及街道地址选择">
                    <template>
                        <el-switch
                                v-model="form.is_street"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </template>
                </el-form-item>
                <el-form-item label="地址是否需要区域">
                    <template>
                        <el-switch
                                v-model="form.is_region"
                                active-value="0"
                                inactive-value="1"
                        >
                        </el-switch>
                    </template>
                </el-form-item>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>支付日志</b></div>
                <el-form-item label="支付回调日志">
                    <template>
                        <el-switch
                                v-model="form.pay_log"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </template>
                    <div>支付回调日志，如果出现手机付款而后台显示待付款状态，请开启日志，查错误日志路径为 addon/yun_shop/data/paylog/[公众号ID]</div>
                </el-form-item>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>提现绑定手机号</b></div>

                <el-form-item label="是否开启">
                    <template>
                        <el-switch
                                v-model="form.is_bind"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </template>
                </el-form-item>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>支付协议开启</b></div>

                <el-form-item label="是否开启">
                    <template>
                        <el-switch
                                v-model="form.share_chain_pay_open"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </template>
                </el-form-item>
                <el-form-item label="支付协议">
                    <tinymceee v-model="form.pay_content" style="width:70%;"></tinymceee>
                </el-form-item>
            </div>
        </div>
        <pop :show="show" @replace="changeProp" @add="parHref"></pop>
        <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
        </el-form>
        <div class="confirm-btn">
            <el-button type="primary"  @click="submit">提交</el-button>
        </div>
    </div>
    </div>
    @include('public.admin.pop')
    @include('public.admin.program')
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    @include('public.admin.tinymceee')
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    activeName: 'one',
                    show:false,//是否开启公众号弹窗
                    pro:false ,//是否开启小程序弹窗
                    form:{
                        refund_status:'0',
                        // is_dispatch:'1',
                        redirect_url:'',
                        min_redirect_url:'',
                        refund_days:'',
                        is_street:'0',
                        is_region:'0',
                        pay_log:'0',
                        is_bind:'0',
                        close_order_days:'',
                        close_order_time:'',
                        receive:'',
                        receive_time:'',
                        share_chain_pay_open:'0',
                        pay_content:'',
                        invoice:{
                            electron: 0,
                            papery : 0
                        }
                    },
                }
            },
            mounted () {

                this.getData();
            },
            methods: {

                //弹窗显示与隐藏的控制
                changeProp(item){
                    this.show=item;
                },
                //当前链接的增加
                parHref(child,confirm){
                    this.show=confirm;
                    this.form.redirect_url=child;

                },
                changeprogram(item){
                    this.pro=item;
                },
                parpro(child,confirm){
                    this.pro=confirm;
                    this.form.min_redirect_url=child;
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.trade') !!}').then(function (response){
                        if (response.data.result) {
                            if(response.data.data.set){
                                for(let i in response.data.data.set){
                                    this.form[i]=response.data.data.set[i]
                                }
                                if(!this.form.invoice){
                                    this.form.invoice={
                                        electron:0,
                                        papery:1
                                    }
                                }
                            }
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                submit() {

                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.trade') !!}',{'trade':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message:  response.data.msg,type: 'success'});

                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection
