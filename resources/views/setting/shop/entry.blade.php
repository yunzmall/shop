@extends('layouts.base')

@section('content')
    <style>
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:20px;
            position:relative;
            min-height:100vh;
            border-radius: 8px;
            background-color:#fff;
        }
        .con .setting .block{
            background-color:#fff;
            border-radius: 8px;
            padding:10px;
        }
        .con .setting .block .title{
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
        .el-form-item__label{
            margin-right:30px;
        }
        .qrcode_list{
            width:100%;
            display:flex;
            flex-wrap:wrap;
        }
        .qrcode_list .qr_code{
            width: 163px;
            margin-right:2%;
            border:solid 1px #ccc;
            border-radius: 17px;
            height: 174px;
            display:flex;
            flex-direction:column;
            align-items: center;
            justify-content: center;
            margin-bottom:20px;
            position:relative;
        }
        .qrcode_list .qr_code .mask{
            position:absolute;
            top:0;
            left:0;
            right:0;
            bottom:0;
            background-color:rgba(0,0,0,0.5);
            display:none;
            border-radius: 17px;
        }
        .qrcode_list .qr_code:hover .mask{
            display:block;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .qrcode_list .qr_code:hover .mask h5{
            width: 105px;
            height: 31px;
            background-color: #ffffff;
            border-radius: 16px;
            margin:0 auto;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .qrcode_list .qr_code:hover .mask h5 a{
            color: #29ba9c;
            font-size:12px;
        }
        .qrcode_list .qr_code img{
            width: 108px;
            height: 108px;
        }
        b{
            font-size:14px;
        }
        .el-tabs{
            padding-right:10px;
        }
    </style>
    <div id="qrcode" ref="qrcode" style="display:none;"></div>
    <div id='re_content' >
        <div class="con" >
            <div class="setting">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>系统>商城入口</b></div>
                </div>
                <template>
                    <el-tabs v-model="activeName" style="padding-left:10px;" >
                        <el-tab-pane label="商城页面链接" name="first">
                            <ul class="qrcode_list" >
                                <li class="qr_code" >
                                    <img id='home' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">商城首页</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('home') !!}" data-url="{!! yzAppFullUrl('home') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code" >
                                    <img id='category' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">分类导航</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('category') !!}" data-url="{!! yzAppFullUrl('category') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code" >
                                    <img id='searchAll' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">全部商品</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('searchAll') !!}" data-url="{!! yzAppFullUrl('searchAll') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                            </ul>
                        </el-tab-pane>
                        <el-tab-pane label="会员中心链接" name="second">
                            <ul class="qrcode_list" >
                                <li class="qr_code" >
                                    <img id='member' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">会员中心</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member') !!}" data-url="{!! yzAppFullUrl('member') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code" >
                                    <img id='orderList' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">我的订单</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/orderList/0') !!}" data-url="{!! yzAppFullUrl('member/orderList/0') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code" >
                                    <img id='cart' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">购物车</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('cart') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='collection'>
                                    <h5 style="margin:0;font-sieze:14px;">我的收藏</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/collection') !!}" data-url="{!! yzAppFullUrl('member/collection') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='footprint' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">我的足迹</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/footprint') !!}" data-url="{!! yzAppFullUrl('member/footprint') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='myEvaluation' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">评价</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/myEvaluation') !!}" data-url="{!! yzAppFullUrl('member/myEvaluation') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='myrelationship' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">关系</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/myrelationship') !!}" data-url="{!! yzAppFullUrl('member/myrelationship') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='address' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">收货地址</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/address') !!}" data-url="{!! yzAppFullUrl('member/address') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='coupon_index' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">我的优惠券</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('coupon/coupon_index') !!}" data-url="{!! yzAppFullUrl('coupon/coupon_index') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='coupon_store' style="margin-bottom:10px;" >
                                    <h5 style="margin:0;font-sieze:14px;">领券中心</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('coupon/coupon_store') !!}" data-url="{!! yzAppFullUrl('coupon/coupon_store') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='integral_v2' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">积分页面</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/integral_v2') !!}" data-url="{!! yzAppFullUrl('member/integral_v2') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='integrallist' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">积分明细</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/integrallist') !!}" data-url="{!! yzAppFullUrl('member/integrallist') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='balance' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">余额页面</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/balance') !!}" data-url="{!! yzAppFullUrl('member/balance') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='detailed' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">余额明细</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/detailed') !!}" data-url="{!! yzAppFullUrl('member/detailed') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                            </ul>
                        </el-tab-pane>
                        <el-tab-pane label="推广中心链接" name="third">
                            <ul class="qrcode_list" >
                                <li class="qr_code">
                                    <img id='extension' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">推广中心</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/extension') !!}" data-url="{!! yzAppFullUrl('member/extension') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='incomedetails' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">收入明细</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/incomedetails') !!}" data-url="{!! yzAppFullUrl('member/incomedetails') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='income' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">收入提现</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/withdrawal') !!}" data-url="{!! yzAppFullUrl('member/income') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                                <li class="qr_code">
                                    <img id='presentationRecord' style="margin-bottom:10px;">
                                    <h5 style="margin:0;font-sieze:14px;">提现明细</h5>
                                    <div class="mask" >
                                        <h5><a href="javascript:;" data-clipboard-text="{!! yzAppFullUrl('member/presentationRecord',['extension'=>'extension']) !!}" data-url="{!! yzAppFullUrl('member/presentationRecord') !!}" class="js-clip" title="复制链接">复制链接</a></h5>
                                    </div>
                                </li>
                            </ul>
                        </el-tab-pane>
                    </el-tabs>
                </template>
            </div>
        </div>
    </div>
    <script src="{{resource_get('static/js/qrcode.min.js')}}"></script>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    activeName: 'first',
                    qr_list:[
                        {
                            url:"{!! yzAppFullUrl('home') !!}",
                            name:'home',
                        },
                        {
                            url:"{!! yzAppFullUrl('category') !!}",
                            name:'category',
                        },
                        {
                            url:"{!! yzAppFullUrl('searchAll') !!}",
                            name:'searchAll',
                        },
                        {
                            url:"{!! yzAppFullUrl('member') !!}",
                            name:'member',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/orderList/0') !!}",
                            name:'orderList',
                        },
                        {
                            url:"{!! yzAppFullUrl('cart') !!}",
                            name:'cart',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/collection') !!}",
                            name:'collection',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/footprint') !!}",
                            name:'footprint',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/myEvaluation') !!}",
                            name:'myEvaluation',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/myrelationship') !!}",
                            name:'myrelationship',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/address') !!}",
                            name:'address',
                        },
                        {
                            url:"{!! yzAppFullUrl('coupon/coupon_index') !!}",
                            name:'coupon_index',
                        },
                        {
                            url:"{!! yzAppFullUrl('coupon/coupon_store') !!}",
                            name:'coupon_store',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/integral_v2') !!}",
                            name:'integral_v2',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/integrallist') !!}",
                            name:'integrallist',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/balance') !!}",
                            name:'balance',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/detailed') !!}",
                            name:'detailed',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/extension') !!}",
                            name:'extension',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/incomedetails') !!}",
                            name:'incomedetails',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/income') !!}",
                            name:'income',
                        },
                        {
                            url:"{!! yzAppFullUrl('member/presentationRecord') !!}",
                            name:'presentationRecord',
                        },
                    ],
                }
            },
            mounted () {
                this.qr_list.forEach((item,index,key)=>{
                    this.qrcodeScan(item.url,item.name)
                })
            },
            methods: {
                qrcodeScan(url,name) {//生成二维码
                    let qrcode = new QRCode('qrcode', {
                        width: 200,  // 二维码宽度
                        height: 200, // 二维码高度
                        render: 'image',
                        text: url
                    });

                    var data = $("canvas")[$("canvas").length - 1].toDataURL().replace("image/png", "image/octet-stream;");
                    $('#'+name+'').attr('src',data);
                    console.log($('#'+name+''))

                }
            },
        });
    </script>
@endsection





