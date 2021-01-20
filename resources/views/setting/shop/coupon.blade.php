@extends('layouts.base')
@section('content')
@section('title', trans('优惠券设置'))
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
        border-radius: 8px;
        min-height:100vh;
        background-color:#fff;
        position:relative;
    }
    .con .setting .block{
        padding:10px;
        margin-bottom:10px;
        border-radius: 8px;
    }
    .con .setting .block .title{
        font-size:18px;
        margin-bottom:15px;
        display:flex;
        align-items:center;
    }
    .el-form-item__label{
        margin-right:30px;
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
            <el-form ref="form" :model="form" label-width="15%">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                    <el-form-item label="优惠券到期消息通知">
                        <el-input  v-model="form.delayed" placeholder="请输入优惠券在到期前多少天可收到通知" style="width:30%;"></el-input :min="0">
                    </el-form-item>
                    <el-form-item label="提醒时间">
                        <template>
                            <el-select v-model="form.every_day"  @change="getSelect" style="width:30%;">
                                <el-option
                                        v-for="item in hourData"
                                        :label="item.name"
                                        :value="item.key"
                                >
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label="优惠券过期提醒">
                        <template>
                            <el-select v-model="form.expire" filterable placeholder="请选择"  @change="getSelect" style="width:30%;">
                                <el-option
                                        v-for="item in temp_list"
                                        :label="item.title"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                            <el-switch
                                    @change="changeVal('expire')"
                                    style="margin-left:20px;"
                                    v-model="value"
                                    :active-value="1"
                                    :inactive-value="0"
                            >
                            </el-switch>
                        </template>
                    </el-form-item>
                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>商品详情显示设置</b></div>
                    <el-form-item label="券后价和商品优惠券显示">
                        <template>
                            <el-switch
                                    v-model="form.is_show_coupon"
                                    active-value="1"
                                    inactive-value="0"
                            >
                            </el-switch>
                        </template>
                    </el-form-item>
                </div>

                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>下单提示使用优惠券</b></div>
                    <el-form-item label="下单提示使用优惠券">
                        <template>
                            <el-switch
                                    v-model="form.coupon_remind"
                                    active-value="1"
                                    inactive-value="0"
                            >
                            </el-switch>
                        </template>
                        <div><span class='help-block'>下单时会员有优惠券未使用时是否提醒会员使用优惠券</span></div>
                    </el-form-item>

                </div>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </el-form>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
            return {
                value:0,
                activeName: 'first',
                temp_list: [],
                hourData:[],
                form:{
                    expire:'',
                    send_times:'0',
                    delayed:'',
                    every_day: "",
                    coupon_remind : '0',

                },
            }
        },
        mounted () {
            this.getData();
        },
        methods: {
            getSelect(val){
                this.$forceUpdate()
            },
            changeVal(val){
                let that=this;
                if(this.value==0){
                    var url = "{!! yzWebUrl('setting.default-notice.cancel') !!}"
                    var postdata = {
                        notice_name: val,
                        setting_name: "shop.coupon"
                    };
                }else if(this.value==1){
                    var url = "{!! yzWebUrl('setting.default-notice.index') !!}"
                    var postdata = {
                        notice_name: val,
                        setting_name: "shop.coupon"
                    };
                }
                this.$http.post(url,postdata).then(function (response){
                    if (response.data.result==1) {
                        if(this.value==1){
                            this.temp_list[0].id=Number(response.data.id)
                            this.form.expire=Number(response.data.id)
                            this.$forceUpdate()
                        }
                    }else {
                        this.form.expire=0;
                        this.value=0;
                        this.$message({message: response.data.msg,type: 'error'});
                    }
                },function (response) {
                    this.$message({message: response.data.msg,type: 'error'});
                })
            },
            getData(){
                this.$http.post('{!! yzWebFullUrl('setting.coupon.index') !!}').then(function (response){
                    if (response.data.result) {
                        if(response.data.data.set){
                            for(let i in response.data.data.set){
                                this.form[i]=response.data.data.set[i]
                            }
                            this.form.expire=Number(this.form.expire)
                        }
                        this.temp_list=response.data.data.temp_list
                        this.value=response.data.data.is_open;
                        this.temp_list.unshift({id:this.form.expire,title:'默认消息模板'})
                        this.hourData=response.data.data.hourData
                        this.hourData.forEach((item,index,key)=>{
                            if(this.form.every_day==item.key){
                                this.form.every_day=item.key
                            }
                        })

                    }else {
                        this.$message({message: response.data.msg,type: 'error'});
                    }
                },function (response) {
                    this.$message({message: response.data.msg,type: 'error'});
                })
            },
            submit() {
              /*  if(this.form.delayed<=0){
                    this.$message({message:'优惠券到期消息通知不能小于0',type: 'error'});
                    return
                }*/
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('setting.coupon.index') !!}',{'coupon':this.form}).then(function (response){
                    if (response.data.result) {
                        this.$message({message: response.data.msg,type: 'success'});
                    }
                    else {
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
