@extends('layouts.base')
@section('title', '转账审核列表')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div id="app-remittance-audits" xmlns:v-bind="http://www.w3.org/1999/xhtml">
        <div class="block">
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">转账审核</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <el-select v-model="searchParams.status_id" size="small" clearable placeholder="全部"
                           style="width: 120px" @change="search" v-loading="loading">
                    <el-option
                            v-for="v in allStatus"
                            :key="v.id"
                            :label="v.name"
                            :value="v.id">
                    </el-option>
                </el-select>
            </div>
        </div>
        <div class="block">
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">
                            审核列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;">
                               总数：[[data.total]] &nbsp;
                               金额总合计：[[amount]]
                            </span>
                        </div>
                    </div>
                    <el-table
                            :data="data.data"
                            style="width: 100%">
                        <el-table-column
                                align="center"
                                prop="id"
                                label="id"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="支付单号">
                            <template slot-scope="scope">
                                <a v-bind:href="'{{ yzWebUrl('orderPay.detail', array('order_pay_id' => '')) }}'+[[scope.row.remittance_record.order_pay.id]]"
                                   target="_blank">[[scope.row.remittance_record.order_pay.pay_sn]]</a>
                            </template>
                        </el-table-column>

                        <el-table-column
                                align="center"
                                prop="remittance_record.order_pay.amount"
                                label="金额">
                        </el-table-column>
                        <el-table-column
                                align="center"
                                prop="member.nickname"
                                label="用户">
                            <template slot-scope="scope">
                                <div v-if="scope.row.remittance_record == null && scope.row.remittance_record.member == null">
                                    会员([[scope.row.remittance_record.order_pay.uid]])已被删除
                                </div>
                                <a v-if="scope.row.remittance_record != null && scope.row.remittance_record.member != null"
                                   v-bind:href="'{{ yzWebUrl('member.member.detail', array('id' => '')) }}'+[[scope.row.remittance_record.order_pay.uid]]"
                                   target="_blank"><img v-bind:src="scope.row.remittance_record.member.avatar_image"
                                                        style='width:30px;height:30px;padding:1px;border:1px solid #ccc'><br/>[[scope.row.remittance_record.member.nickname]]</a>
                            </template>
                        </el-table-column>

                        <el-table-column
                                align="center"
                                prop="status_name"
                                label="状态">
                        </el-table-column>

                        <el-table-column
                                align="center"
                                prop="created_at"
                                label="创建时间">
                        </el-table-column>
                        <el-table-column
                                align="center"
                                fixed="right"
                                label="操作"
                                width="100">
                            <template slot-scope="scope">
                                <el-tooltip class="item" effect="dark" content="查看" placement="top-start">
                                    <a v-bind:href="'{{ yzWebUrl('remittanceAudit.detail', array('id' => '')) }}'+[[scope.row.id]]"
                                       target="_blank"><i style="font-size: 20px;color: #cccccc" class="el-icon-view"></i></a>
                                </el-tooltip>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="float: right">
                        <el-pagination
                                background
                                layout="prev, pager, next"
                                :total="data.total"
                                :page-size="data.pagesize"
                                @current-change="handleCurrentChange"
                                v-loading="pageLoading">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .el-dropdown-menu__item {
            padding: 0;
        }

        .el-dropdown-menu {
            padding: 0;
        }

        .el-table .warning-row {
            background: oldlace;
        }

        .el-table .success-row {
            background: #f0f9eb;
        }
    </style>
@endsection('content')
@section('js')
    <script>
        var app = new Vue({
            el: '#app-remittance-audits',
            delimiters: ['[[', ']]'],

            data() {
                let data = eval({!! $data !!});
                return {
                    data: data.remittanceAudits,

                    allStatus: [
                        {id: null, name: "全部"},
                        ...data.allStatus

                    ],

                    searchParams: {
                        ...data.searchParams,
                        "keywords": "",
                        "status_id": ""
                    },
                    loading: false,
                    pageLoading: false,
                    amount:data.amount
                }
            },
            mounted: function () {
            },
            methods: {
                search() {
                    this.loading = true;

                    this.$http.post("{!! yzWebUrl('finance.remittance-audit.ajax')!!}", {...this.searchParams}).then(response => {
                        this.data = response.data.data.remittanceAudits;
                        this.loading = false;
                    }, response => {
                        console.log(response);
                        this.loading = false;
                    });
                },
                handleCurrentChange(val) {
                    this.pageLoading = true;

                    //this.$Loading.start();
                    this.$http.post("{!! yzWebUrl('finance.remittance-audit.ajax')!!}", {
                        ...this.searchParams,
                        page: val,
                        pagesize: this.data.pagesize
                    }).then(response => {
                        console.log(response);
                        //this.$Loading.finish();
                        this.pageLoading = false;

                        this.data = response.data.data.remittanceAudits;
                    }, response => {
                        this.pageLoading = false;

                        //this.$Loading.error();
                        console.log(response);
                    });
                }
            }
        });
    </script>
@endsection('js')
