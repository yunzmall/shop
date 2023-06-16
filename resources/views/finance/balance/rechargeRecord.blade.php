@extends('layouts.base')
@section('title', '充值记录')
@section('content')
    <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .el-form-item__label {
            width: 300px;
            text-align: right;
        }

        .alert.alert-warning {
            border: 1px;
            color: red;
            border-radius: 3px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4);
            background-color: #fcf4f4;
        }

        .note-span {
            width: 290px;
            text-align: right;
            margin-right: 10px;
        }

        .el-radio-group {
            display: inline-block;
            line-height: 1;
            vertical-align: inherit;
        }

        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .con {
            padding-bottom: 40px;
            position: relative;
            border-radius: 8px;
            min-height: 100vh;
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

        .con .confirm-btn {
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

        .el-checkbox__inner {
            border: solid 1px #56be69 !important;
        }
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">充值记录</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    placeholder="充值单号"
                                    v-model="search_form.ordersn"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    placeholder="姓名/昵称/手机号"
                                    v-model="search_form.realname"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.level_id" placeholder="会员等级">
                                <el-option
                                        v-for="item in member_level"
                                        :key="item.id"
                                        :label="item.level_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.group_id" placeholder="会员分组">
                                <el-option
                                        v-for="item in member_group"
                                        :key="item.id"
                                        :label="item.group_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.pay_type" placeholder="充值方式">
                                <el-option
                                        v-for="(item,index) in pay_type"
                                        :key="index"
                                        :label="item"
                                        :value="index">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.status" placeholder="充值状态">
                                <el-option
                                        v-for="(item,index) in pay_status"
                                        :key="index"
                                        :label="item.label"
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
                            充值记录列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]] &nbsp;
                               金额合计：[[amount]]
                            </span>
                        </div>
                    </div>

                    <el-table :data="record_list.data" style="width: 100%">
                        <el-table-column label="充值单号" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.ordersn]]
                            </template>
                        </el-table-column>
                        <el-table-column label="会员ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.member_id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="粉丝" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image v-if="scope.row.member"
                                              style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.member.avatar"
                                              alt="">

                                    </el-image>
                                </div>
                                <div>
                                    <el-button type="text" @click="memberNav(scope.row.member_id)">
                                        [[scope.row.member.nickname]]
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column label="会员信息/电话号码" align="center" prop="">
                            <template slot-scope="scope">
                                [[scope.row.member.realname]] <br>
                                [[scope.row.member.mobile]]
                            </template>
                        </el-table-column>
                        <el-table-column label="等级/分组" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.member.yz_member.level ? scope.row.member.yz_member.level.level_name :
                                shopSet.level_name]]
                                <br>
                                [[scope.row.member.yz_member.group ? scope.row.member.yz_member.group.group_name : '']]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.created_at]]
                            </template>
                        </el-table-column>
                        <el-table-column label="充值方式" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span v-if="scope.row.type==0"
                                      class='label label-default'>[[ scope.row.type_name ]]</span>
                                <span v-else-if="scope.row.type==1" class='label label-success'>[[ scope.row.type_name ]]</span>
                                <span v-else-if="scope.row.type==2" class='label label-warning'>[[ scope.row.type_name ]]</span>
                                <span v-else-if="scope.row.type==9 || scope.row.type==10" class='label label-info'>[[ scope.row.type_name ]]</span>
                                <span v-else="scope.row.type" class='label label-primary'>[[scope.row.type_name]]</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="充值金额状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.money]] <br>
                                <span v-if="scope.row.status==1"
                                      class='label label-success'>充值成功</span>
                                <span v-else-if="scope.row.status==-1" class='label label-warning'>充值失败</span>
                                <span v-else="scope.row.status" class='label label-default'>申请中</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="备注信息" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                    [[scope.row.remark]]
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
                        realname: '',
                        level_id: '',
                        group_id: '',
                        pay_type: '',
                        status: '',
                        time: {
                            start: 0,
                            end: 0,
                        }
                    },
                    member_level: [],
                    member_group: [],
                    pay_type: [],
                    pay_status: [{
                        value: '1',
                        label: '充值成功'
                    }, {
                        value: '-1',
                        label: '充值失败'
                    }],
                    record_list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
                    shopSet: {},
                    amount: 0,
                    search_time: []
                }
            },
            created() {
                this.getData(1)
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                getData(page) {
                    let search = this.search_form
                    search.time.start = this.search_time[0] ? this.search_time[0] : ''
                    search.time.end = this.search_time[1] ? this.search_time[1] : ''
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('finance.balance-recharge-records.get-data') !!}', {
                        search: search,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            console.log(response.data.data)
                            this.record_list = response.data.data.recordList
                            this.member_level = response.data.data.memberLevel
                            this.member_group = response.data.data.memberGroup
                            this.total = response.data.data.recordList.total
                            this.per_page = response.data.data.recordList.per_page
                            this.current_page = response.data.data.recordList.current_page
                            this.shopSet = response.data.data.shopSet
                            this.amount = response.data.data.amount
                            this.pay_type = response.data.data.payType
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
                    this.getData(page)
                },
                exportList() {
                    let that = this;
                    console.log(that.search_form);

                    let json = {
                        realname: that.search_form.realname,
                        level_id: that.search_form.level_id,
                        group_id: that.search_form.group_id,
                        pay_type: that.search_form.pay_type,
                        status: that.search_form.status,
                        time: [this.search_time[0] ? this.search_time[0] : '', this.search_time[1] ? this.search_time[1] : '']
                    };

                    let url = '{!! yzWebFullUrl('finance.balance-recharge-records.export') !!}';


                    if (that.code) {
                        url += "&code=" + that.code
                    }

                    for (let i in json) {
                        if (json[i]) {
                            url += "&search[" + i + "]=" + json[i]
                        }
                    }

                    console.log(url);
                    window.location.href = url;
                },
                memberNav(uid) {
                    let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                    window.open(url + "&id=" + uid)
                }
            },
        })
    </script>
@endsection
