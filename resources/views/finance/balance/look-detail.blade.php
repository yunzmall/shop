
@extends('layouts.base')
@section('title', '查看详情')
@section('content')
<style>
.panel{
    margin-bottom:10px!important;
    border-radius: 10px;
    padding-left: 20px;
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
    
    height:100vh;
    background-color:#fff;
}
.left{
    height:200px;
    display:flex;
    align-items:center;
    background:#fff;
    width:49%;
    border-radius: 8px;
    padding-left:50px;
    box-sizing:border-box;
}
.left .image{
    width: 90px;
    height: 90px;
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
.right{
    height:200px;
    display:flex;
    align-items:center;
    background:#fff;
    width:49%;
    border-radius: 8px;
    
}
b{
    font-size:14px;
}
[v-cloak]{
    display: none;
}
</style>
<div id='re_content'  v-cloak>
    <div class="con">
        <div class="setting">
            <div class="block" style="display:flex;">
                <div class="left">
                    <div style="display:flex;">
                        <div class="image">
                            <img :src='detailModel.member.avatar_image' style="width: 100%;height:100%;">
                        </div>
                        <div style="flex-direction:column;margin-left:20px;display: flex;justify-content: space-between;">
                            <div>会员ID:[[detailModel.member.uid]]</div>
                            <div>会员昵称:[[detailModel.member.realname]]</div>
                            <div>会员手机:[[detailModel.member.mobile ]]</div>
                        </div>
                    </div>
                </div>
                <div style="width:2%;height:200px;background: #eff3f6;"></div>
                <div class="right">
                    <div style="display:flex;justify-content:space-between;width:100%;">
                       <div style="flex:1;display:flex;justify-content:center;align-items:center;">
                            <div><i class="iconfont icon-fontclass-qian" style="font-size:37px;color:#DB1E06;height: 100%;display: flex;align-items: center;"></i></div>
                            <div style="flex-direction:column;justify-content: space-between;margin-left:10px;display: flex;">
                                <div>变动余额值</div>
                                <div>[[detailModel.change_money]]</div>
                            </div>
                       </div> 
                       <div style="flex:1;display:flex;justify-content:center;align-items:center;">
                       <div><i class="iconfont icon-fontclass-qian" style="font-size:37px;color:#DB1E06;height: 100%;display: flex;align-items: center;"></i></div>
                            <div style="flex-direction:column;justify-content: space-between;margin-left:10px;display: flex;">
                                <div>变动前余额</div>
                                <div>[[detailModel.old_money]]</div>
                            </div>
                       </div> 
                       <div style="flex:1;display:flex;justify-content:center;align-items:center;">
                       <div><i class="iconfont icon-fontclass-qian" style="font-size:37px;color:#DB1E06;height: 100%;display: flex;align-items: center;"></i></div>
                            <div style="flex-direction:column;justify-content: space-between;margin-left:10px;display: flex;">
                                <div>变动后余额</div>
                                <div>[[detailModel.new_money ]]</div>
                            </div>
                       </div> 
                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block" style="height:300px;width:100%;background-color:#fff;padding:10px;">
                <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>信息</b></div>
                <div class="info" style="padding-left:24px;">
                    <div style="margin-top:20px;margin-bottom:20px;">
                        <span style="display:inline-block;width:500px;">变动时间:[[detailModel.created_at ]]</span>
                        <span>订单编号:[[detailModel.serial_number]]</span>
                    </div>
                    <div style="margin-top:20px;margin-bottom:20px;">
                        <span style="display:inline-block;width:500px;">业务类型:[[detailModel.service_type_name ]]</span>
                        <span>收入/支出:[[detailModel.type_name]]</span>
                    </div>
                    <div style="margin-top:20px;margin-bottom:20px;">
                        <span style="display:inline-block;width:500px;">备注:[[detailModel.remark]]</span>
                       
                    </div>
                </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">返回</el-button>
            </div>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
            let detailModel={!!$detailModel?:'{}' !!}
      
           return {
                detailModel:detailModel,
           }
        },
        mounted () {
           
        },
        methods: {
            submit(){
                window.location.href='{!! yzWebFullUrl('finance.balance-records.index') !!}';
            },
        }
    });
</script>
@endsection
