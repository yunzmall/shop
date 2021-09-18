@extends('layouts.base')
@section('content')
@section('title', trans('语言设置'))

<style>
    .content {
        background: #eff3f6;
        padding: 10px !important;
    }

    .con {
        padding-bottom: 40px;
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

    .el-form-item__label {
        margin-right: 30px;
    }

    b {
        font-size: 14px;
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

    .el-tabs {
        padding-right: 10px;
    }
</style>
<div id='re_content'>
    <template>
        <el-tabs v-model="activeName"
                 style="padding-left:10px;background-color:#fff;margin-bottom:10px;border-radius:8px;">

            <el-tab-pane label="提现收入设置" name="eight"></el-tab-pane>
            <el-tab-pane label="商品" name="ten"></el-tab-pane>
            <el-tab-pane label="客户" name="eleven"></el-tab-pane>

            @if(app('plugins')->isEnabled('commission'))
                <el-tab-pane label="分销设置" name="one"></el-tab-pane>
            @endif

            @if(app('plugins')->isEnabled('single-return'))
                <el-tab-pane label="消费赠送设置" name="two"></el-tab-pane>
            @endif

            @if(app('plugins')->isEnabled('team-dividend'))
                <el-tab-pane label="经销商奖励设置" name="three"></el-tab-pane>
            @endif

            @if(app('plugins')->isEnabled('full-return'))
                <el-tab-pane label="满额赠送设置" name="four"></el-tab-pane>

            @endif

            @if(app('plugins')->isEnabled('team-dividend'))
                <el-tab-pane label="经销商管理设置" name="five"></el-tab-pane>
            @endif

            @if(app('plugins')->isEnabled('area-dividend'))
                <el-tab-pane label="区域代理设置" name="six"></el-tab-pane>

            @endif

            @if(app('plugins')->isEnabled('store-cashier'))
                <el-tab-pane label="门店收银台设置" name="seven"></el-tab-pane>
            @endif


            @if(app('plugins')->isEnabled('appointment'))
                <el-tab-pane label="门店预约" name="nine"></el-tab-pane>
            @endif
            {{--@if(app('plugins')->isEnabled('store-projects'))//前端没弄好，先注释掉
                <el-tab-pane label="多门店核销" name="store_projects"></el-tab-pane>
            @endif--}}

            @if(app('plugins')->isEnabled('merchant'))
                <el-tab-pane label="招商" name="twelve"></el-tab-pane>
            @endif

            @if(app('plugins')->isEnabled('love'))
                <el-tab-pane label="爱心值" name="love"></el-tab-pane>
            @endif


        </el-tabs>
    </template>

    <div class="con">
        <div class="setting">
            <el-form ref="form" :model="form" label-width="15%">
                <div class="block">
                    <div class="title"><span
                                style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b>
                    </div>
                    <template v-if="activeName=='one'">
                        <el-form-item label="插件名称">
                            <el-input v-model="form.commission.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="分销">
                            <el-input v-model="form.commission.commission" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="分销商">
                            <el-input v-model="form.commission.agent" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="分销等级">
                            <el-input v-model="form.commission.level_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="分销订单">
                            <el-input v-model="form.commission.commission_order" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="分销佣金">
                            <el-input v-model="form.commission.commission_amount" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="默认等级名称">
                            <el-input v-model="form.commission.commission_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="活动详情-佣金">
                            <el-input v-model="form.commission.commission_alias" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='two'">
                        <el-form-item label="插件名称">
                            <el-input v-model="form.single_return.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="消费赠送">
                            <el-input v-model="form.single_return.single_return" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送">
                            <el-input v-model="form.single_return.return_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送队列">
                            <el-input v-model="form.single_return.return_queue" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送记录">
                            <el-input v-model="form.single_return.return_log" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送详情">
                            <el-input v-model="form.single_return.return_detail" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="已赠送金额">
                            <el-input v-model="form.single_return.return_amount" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='three'">
                        <el-form-item label="插件名称">
                            <el-input v-model="form.team_return.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="经销商奖励">
                            <el-input v-model="form.team_return.team_return" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励">
                            <el-input v-model="form.team_return.return_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="经销商等级">
                            <el-input v-model="form.team_return.team_level" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励记录">
                            <el-input v-model="form.team_return.return_log" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励详情">
                            <el-input v-model="form.team_return.return_detail" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励金额">
                            <el-input v-model="form.team_return.return_amount" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励比例">
                            <el-input v-model="form.team_return.return_rate" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="经销商">
                            <el-input v-model="form.team_return.team_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励时间">
                            <el-input v-model="form.team_return.return_time" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='four'">
                        <el-form-item label="插件名称1">
                            <el-input v-model="form.full_return.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="满额赠送">
                            <el-input v-model="form.full_return.full_return" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送">
                            <el-input v-model="form.full_return.return_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="满额赠送记录">
                            <el-input v-model="form.full_return.full_return_log" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送详情">
                            <el-input v-model="form.full_return.return_detail" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="赠送金额">
                            <el-input v-model="form.full_return.return_amount" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='five'">
                        <el-form-item label="插件名称">
                            <el-input v-model="form.team_dividend.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="经销商中心">
                            <el-input v-model="form.team_dividend.team_agent_centre" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="提成">
                            <el-input v-model="form.team_dividend.dividend" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="活动详情-经销商提成">
                            <el-input v-model="form.team_dividend.dividend_alias" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="团队提成">
                            <el-input v-model="form.team_dividend.team_dividend" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="平级奖">
                            <el-input v-model="form.team_dividend.flat_prize" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="感恩奖">
                            <el-input v-model="form.team_dividend.award_gratitude" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="额外奖励">
                            <el-input v-model="form.team_dividend.extra_award" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">"额外奖励" 自定义名称</div>
                        </el-form-item>
                        <el-form-item label="未结算">
                            <el-input v-model="form.team_dividend.wait_dividend" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">未结算佣金 中 "未结算"自定义名称</div>
                        </el-form-item>
                        <el-form-item label="已结算">
                            <el-input v-model="form.team_dividend.settle_dividend" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">已结算佣金 中 "已结算"自定义名称</div>
                        </el-form-item>
                        <el-form-item label="佣金">
                            <el-input v-model="form.team_dividend.dividend_amount" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">已结算佣金 中 "佣金" 自定义名称</div>
                        </el-form-item>
                        <el-form-item label="我的客户">
                            <el-input v-model="form.team_dividend.my_agent" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="顾客">
                            <el-input v-model="form.team_dividend.client" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="直推字样自定义">
                            <el-input v-model="form.team_dividend.recommend_text" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">订单概况页面"直推字样"自定义</div>
                        </el-form-item>
                        <el-form-item label="非直推字样自定义">
                            <el-input v-model="form.team_dividend.no_recommend_text" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">订单概况页面"非直推字样"自定义</div>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='six'">
                        <el-form-item label="插件名称">
                            <el-input v-model="form.area_dividend.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="区域分红中心">
                            <el-input v-model="form.area_dividend.area_dividend_center" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="佣金">
                            <el-input v-model="form.area_dividend.dividend_amount" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='seven'">
                        <el-form-item label="提货人姓名">
                            <el-input v-model="form.store_carry.name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="提货人手机号码">
                            <el-input v-model="form.store_carry.number" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='eight'">
                        <el-form-item label="提现">
                            <el-input v-model="form.income.name_of_withdrawal" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="收入">
                            <el-input v-model="form.income.income_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="手续费">
                            <el-input v-model="form.income.poundage_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="劳务税">
                            <el-input v-model="form.income.special_service_tax" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="手动提现">
                            <el-input v-model="form.income.manual_withdrawal" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='nine'">
                        <el-form-item label="技师">
                            <el-input v-model="form.appointment.worker" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="项目">
                            <el-input v-model="form.appointment.project" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="服务">
                            <el-input v-model="form.appointment.service" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    {{--<template v-if="activeName=='store_projects'">
                        <el-form-item label="项目">
                            <el-input v-model="form.store_projects.project" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>--}}
                    <template v-if="activeName=='ten'">
                        <el-form-item label="原价">
                            <el-input v-model="form.goods.market_price" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="现价">
                            <el-input v-model="form.goods.price" style="width:70%;"></el-input>
                        </el-form-item>

                    </template>
                    <template v-if="activeName=='eleven'">
                        <el-form-item label="客户">
                            <el-input v-model="form.agent.agent" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="客户数量">
                            <el-input v-model="form.agent.agent_num" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="总客户数">
                            <el-input v-model="form.agent.agent_count" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="客户订单">
                            <el-input v-model="form.agent.agent_order" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="客户总订单">
                            <el-input v-model="form.agent.agent_order_count" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="客户总支付商品数">
                            <el-input v-model="form.agent.agent_goods_num" style="width:70%;"></el-input>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='twelve'">
                        <el-form-item label="插件名称">
                            <el-input v-model="form.merchant.title" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="招商员">
                            <el-input v-model="form.merchant.merchant_people" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="招商中心">
                            <el-input v-model="form.merchant.merchant_center" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="分红">
                            <el-input v-model="form.merchant.merchant_reward" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="奖励名称">
                            <el-input v-model="form.merchant.merchant_income" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">收入明细、提现、提现记录页面显示</div>
                        </el-form-item>
                    </template>
                    <template v-if="activeName=='love'">
                        <el-form-item label="收入">
                            <el-input v-model="form.love.change_income" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">爱心值明细 "收入" 自定义名称</div>
                        </el-form-item>
                        <el-form-item label="支出">
                            <el-input v-model="form.love.change_expend" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">爱心值明细 "支出" 自定义名称</div>
                        </el-form-item>
                    </template>
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
                    activeName: 'eight',
                    form: {
                        commission: {
                            title: '',
                            commission: '',
                            agent: '',
                            level_name: '',
                            commission_order: '',
                            commission_amount: '',
                            commission_name: '',
                            commission_alias: '',
                        },
                        single_return: {
                            title: '',
                            single_return: '',
                            return_name: '',
                            return_queue: '',
                            return_log: '',
                            return_detail: '',
                            return_amount: '',
                        },
                        team_return: {
                            title: '',
                            team_return: '',
                            return_name: '',
                            return_queue: '',
                            return_log: '',
                            return_detail: '',
                            return_amount: '',
                            return_rate: '',
                            team_name: '',
                            return_time: '',
                        },
                        full_return: {
                            title: '',
                            full_return: '',
                            return_name: '',
                            return_log: '',
                            return_detail: '',
                            return_amount: '',
                        },
                        team_dividend: {
                            title: '',
                            team_agent_centre: '',
                            dividend: '',
                            dividend_alias: '',
                            team_dividend: '',
                            flat_prize: '',
                            award_gratitude: '',
                            extra_award: '',
                            wait_dividend: '',
                            settle_dividend: '',
                            dividend_amount: '',
                            my_agent: '',
                            client: '',
                        },
                        area_dividend: {
                            title: '',
                            area_dividend_center: '',
                            dividend_amount: '',
                        },
                        store_carry: {
                            name: '',
                            number: '',
                        },
                        income: {
                            name_of_withdrawal: '',
                            income_name: '',
                            poundage_name: '',
                            special_service_tax: '',
                        },
                        appointment: {
                            worker: '',
                            project: '',
                            service: '',
                        },
                        /*store_projects: {
                            project: ''
                        },*/
                        goods: {
                            market_price: '',
                            price: '',
                        },
                        agent: {
                            agent: '',
                            agent_num: '',
                            agent_count: '',
                            agent_order: '',
                            agent_order_count: '',
                            agent_goods_num: '',
                        },
                        merchant: {
                            title: '',
                            merchant_people: '',
                            merchant_center: '',
                            merchant_reward: '',
                            merchant_income: ''
                        },
                        love: {
                            change_income: '',
                            change_expend: '',
                        },
                    }
                }
            },
            mounted() {
                this.getData()
            },
            methods: {
                getData() {
                    this.$http.post('{!! yzWebFullUrl('setting.lang.index') !!}').then(function (response) {
                        if (response.data.result) {
                            if (response.data.data.set) {
                                for (let i in response.data.data.set) {
                                    this.form[i] = response.data.data.set[i]
                                }
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
                    this.$http.post('{!! yzWebFullUrl('setting.lang.index') !!}', {'setdata': this.form}).then(function (response) {
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
