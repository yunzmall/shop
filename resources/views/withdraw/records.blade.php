@extends('layouts.base')

@section('content')
@section('title', trans('提现记录'))
<link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .content {
        background: #eff3f6;
        padding: 10px !important;
    }
    .vue-main-form {
        margin-top: 0;
    }
</style>
<div id="app" v-cloak class="main">
    <div class="block">
        <div class="vue-head">
            <el-tabs v-model="activeName" @tab-click="handleClick(1)">
                <el-tab-pane label="全部记录" name="all"></el-tab-pane>
                <el-tab-pane label="待审核" name="initial"></el-tab-pane>
                <el-tab-pane label="待打款" name="audit"></el-tab-pane>
                <el-tab-pane label="打款中" name="paying"></el-tab-pane>
                <el-tab-pane label="已打款" name="payed"></el-tab-pane>
                <el-tab-pane label="已驳回" name="rebut"></el-tab-pane>
                <el-tab-pane label="已无效" name="invalid"></el-tab-pane>
            </el-tabs>
        </div>
    </div>
    <div class="block">
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">提现记录</div>
                <div class="vue-main-title-button">
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input
                                placeholder="会员ID"
                                v-model="search_form.member_id"
                                clearable>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input
                                placeholder="昵称/姓名/手机"
                                v-model="search_form.member"
                                clearable>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input
                                placeholder="提现编号"
                                v-model="search_form.withdraw_sn"
                                clearable>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select clearable v-model="search_form.type" placeholder="收入类型">
                            <el-option
                                    v-for="(item,index) in types"
                                    :key="item.index"
                                    :label="item.title"
                                    :value="item.class">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select clearable v-model="search_form.pay_way" placeholder="提现方式">
                            <el-option
                                    v-for="(item,index) in pay_way_list"
                                    :key="item.index"
                                    :label="item.title"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-date-picker
                                value-format="timestamp"
                                v-model="search_time"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="">
                        <el-button type="primary" @click="search(1)">搜索</el-button>
                    </el-form-item>
                    <el-form-item label="">
                        <el-button type="primary" @click="exportList()">导出 EXCEL</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
    <div class="block">
        <div class="vue-main">
            <div class="vue-main-form">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">
                        提现列表
                        <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]] &nbsp;
                               提现金额合计：[[amount]]
                        </span>
                        @if(app('plugins')->isEnabled('tax-withdraw'))
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               第三方 @php echo TAX_WITHDRAW_DIY_NAME; @endphp 账户可用余额：@php echo \Yunshop\TaxWithdraw\services\TaxService::getBalance(); @endphp
                        </span>
                        @endif
                    </div>
                </div>

                <el-table :data="record_list.data" style="width: 100%">
                    <el-table-column label="申请时间" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.created_at]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现编号" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.withdraw_sn]]
                        </template>
                    </el-table-column>
                    <el-table-column label="粉丝" align="center" prop="created_at" width="auto">
                        <template slot-scope="scope">
                            <div>
                                <el-image v-if="scope.row.has_one_member.uid"
                                          style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                          :src="scope.row.has_one_member.avatar"
                                          alt="">
                                </el-image>
                            </div>
                            <div>
                                <el-button type="text" @click="memberNav(scope.row.member_id)">
                                    [[scope.row.has_one_member.nickname]]
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="姓名/手机号" align="center" prop="">
                        <template slot-scope="scope">
                            [[scope.row.has_one_member.realname]] <br>
                            [[scope.row.has_one_member.mobile]]
                        </template>
                    </el-table-column>
                    <el-table-column label="收入类型" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.type_name]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现方式" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.pay_way_name]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现金额" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.amounts]]
                        </template>
                    </el-table-column>
                    <el-table-column label="提现状态" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                                <span v-if="scope.row.status==1" class='label label-danger'>[[scope.row.status_name]]</span>
                                <span v-else-if="scope.row.status==2" class='label label-success'>[[scope.row.status_name]]</span>
                                <span v-else-if="scope.row.status==3" class='label label-warning'>[[scope.row.status_name]]</span>
                                <span v-else-if="scope.row.status==4" class='label label-info'>[[scope.row.status_name]]</span>
                                <span v-else="scope.row.status==1" class='label label-primary'>[[scope.row.status_name]]</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            <el-button @click="detailNav(scope.row.type,scope.row.id)">查看详情
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>
    </div>

    <!-- 分页 -->
    <div class="vue-page">
        <el-row>
            <el-col align="right">
                <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                               :page-size="per_page" :current-page="current_page" background
                ></el-pagination>
            </el-col>
        </el-row>
    </div>
</div>
<script>
    var vm = new Vue({
        el: '#app',
        // 防止后端冲突,修改ma语法符号
        delimiters: ['[[', ']]'],
        data() {
            return {
                search_form: {
                    member_id: '',
                    member: '',
                    withdraw_sn: '',
                    type: '',
                    pay_way: '',
                    time: {
                        start: 0,
                        end: 0,
                    }
                },
                activeName: 'all',
                record_list: {},
                total: 0,
                per_page: 0,
                current_page: 0,
                pageSize: 0,
                shopSet: {},
                amount: 0,
                search_time: [],
                pay_way_list: [],
                types: []
            }
        },
        created() {
            this.handleClick(1)
        },
        //定义全局的方法
        beforeCreate() {
        },
        filters: {},
        methods: {
            getData(url, page) {
                let search = this.search_form
                if (this.search_time) {
                    search.time.start = this.search_time[0] ? this.search_time[0] : ''
                    search.time.end = this.search_time[1] ? this.search_time[1] : ''
                } else {
                    search.time.start = ''
                    search.time.end = ''
                }

                let loading = this.$loading({
                    target: document.querySelector(".content"),
                    background: 'rgba(0, 0, 0, 0)'
                });
                this.$http.post(url, {
                    search: search,
                    page: page
                }).then(function (response) {
                    if (response.data.result) {
                        this.record_list = response.data.data.records
                        this.total = response.data.data.records.total
                        this.per_page = response.data.data.records.per_page
                        this.current_page = response.data.data.records.current_page
                        this.shopSet = response.data.data.shopSet
                        this.amount = response.data.data.amount
                        this.types = response.data.data.types
                        this.pay_way_list = response.data.data.pay_way_list
                        loading.close();
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }

                    loading.close();
                }, function (response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                });
            },
            search(page) {
                this.handleClick(page)
            },
            exportList() {
                let that = this;
                let json = {
                    member_id: that.search_form.member_id,
                    member: that.search_form.member,
                    withdraw_sn: that.search_form.withdraw_sn,
                    type: that.search_form.type,
                    pay_type: that.search_form.pay_type,
                    pay_way: that.search_form.pay_way,
                    time: []
                };
                if (this.search_time) {
                    json.time = [this.search_time[0] ? this.search_time[0] : '', this.search_time[1] ? this.search_time[1] : '']
                } else {
                    json.time = ['','']
                }
                let url = this.getUrl()


                for (let i in json) {
                    if (json[i]) {
                        if (i === 'time' && json[i][0] && json[i][1]) {
                            url += "&search[" + i + "][start]=" + json[i][0]
                            url += "&search[" + i + "][end]=" + json[i][1]
                        }else {
                            url += "&search[" + i + "]=" + json[i]
                        }
                    }
                }

                console.log(url);
                window.location.href = url+'&export=1';
            },
            memberNav(uid) {
                let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                window.open(url + "&id=" + uid)
            },
            handleClick(page) {
                let url = this.getUrl()

                this.getData(url, page)
            },
            getUrl() {
                let url = ''
                switch (this.activeName) {
                    case 'invalid' :
                        url = '{!! yzWebFullUrl('withdraw.records.invalid') !!}';
                        break;
                    case 'rebut' :
                        url = '{!! yzWebFullUrl('withdraw.records.rebut') !!}';
                        break;
                    case 'payed' :
                        url = '{!! yzWebFullUrl('withdraw.records.payed') !!}';
                        break;
                    case 'paying' :
                        url = '{!! yzWebFullUrl('withdraw.records.paying') !!}';
                        break;
                    case 'audit' :
                        url = '{!! yzWebFullUrl('withdraw.records.audit') !!}';
                        break;
                    case 'initial' :
                        url = '{!! yzWebFullUrl('withdraw.records.initial') !!}';
                        break;
                    case 'all' :
                        url = '{!! yzWebFullUrl('withdraw.records.index') !!}';
                        break;
                }
                return url
            },
            detailNav(type, id) {
                let url = ''
                if (type === 'balance') {
                    url = '{!!yzWebFullUrl('finance.balance-withdraw.detail')!!}' + "&type=balance"
                } else if (type === 'auction_prepayment') {
                    url = '{!!yzWebFullUrl('finance.prepayment-withdraw.detail')!!}' + "&type=auction_prepayment"
                } else {
                    url = '{!!yzWebFullUrl('withdraw.detail.index')!!}'
                }
                window.location = url + "&id=" + id
            }
        },
    })
</script>
@endsection
