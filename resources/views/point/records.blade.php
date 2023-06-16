@extends('layouts.base')
@section('title', '积分明细')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
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
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            @include('layouts.vueTabs')
        </div>
        <div class="block">
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">积分明细</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="会员ID/会员姓名/昵称/手机号"
                                    v-model="search_form.member"
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
                            <el-select clearable v-model="search_form.source" placeholder="业务类型">
                                <el-option
                                        v-for="(item,index) in source_comment"
                                        :key="index"
                                        :label="item"
                                        :value="index">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.income_type" placeholder="收入/支出">
                                <el-option
                                        v-for="(item,index) in income_type_select"
                                        :key="index"
                                        :label="item.name"
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
                            记录列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]]
                            </span>
                        </div>
                    </div>

                    <el-table :data="record_list.data" style="width: 100%">
                        <el-table-column label="id" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="会员ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span>[[scope.row.member_id]]</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="粉丝" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image v-if="scope.row.member.uid"
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
                        <el-table-column label="会员信息/手机号" align="center" prop="">
                            <template slot-scope="scope">
                                [[scope.row.member.realname]] <br>
                                [[scope.row.member.mobile]]
                            </template>
                        </el-table-column>
                        <el-table-column label="时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.created_at]]
                            </template>
                        </el-table-column>
                        <el-table-column label="业务类型" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.source_name]]
                            </template>
                        </el-table-column>
                        <el-table-column label="积分" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.after_point]]
                            </template>
                        </el-table-column>
                        <el-table-column label="收入支出" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.point]]
                            </template>
                        </el-table-column>
                        <el-table-column label="备注" align="center" prop="" width="auto">
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
                        group_id: '',
                        level_id: '',
                        source: '',
                        income_type: '',
                        member: '',
                        time: {
                            start: 0,
                            end: 0,
                        }
                    },
                    member_id: '',
                    activeName: 'point_detailed',
                    record_list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
                    amount: 0,
                    search_time: [],
                    tab_list: [],
                    member_level: [],
                    member_group: [],
                    source_comment: [],
                    income_type_select: [
                        {
                            value: -1,
                            name: '支出'
                        },
                        {
                            value: 1,
                            name: '收入'
                        }
                    ]

                }
            },
            created() {
                this.member_id = this.getParam('member_id')
                this.getData(1)
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },
                getData(page) {
                    let search = this.search_form
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    if (typeof this.search_time[0] != 'undefined' && typeof this.search_time[1] != 'undefined') {
                        search.time.start = this.search_time[0] / 1000
                        search.time.end = this.search_time[1] / 1000
                    }
                    this.$http.post('{!! yzWebFullUrl('point.records.index') !!}', {
                        search: search,
                        member_id: this.member_id,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            this.record_list = response.data.data.pageList
                            this.total = response.data.data.pageList.total
                            this.per_page = response.data.data.pageList.per_page
                            this.current_page = response.data.data.pageList.current_page
                            this.member_group = response.data.data.memberGroup
                            this.amount = response.data.data.amount
                            this.member_level = response.data.data.memberLevel
                            this.source_comment = response.data.data.sourceComment
                            this.tab_list = response.data.data.tab_list
                            this.search_form = response.data.data.search
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
                    let json = this.search_form;
                    let url = '{!! yzWebFullUrl('point.export.index') !!}';


                    for (let i in json) {
                        if (json[i]) {
                            if (i === 'time') {
                                url += "&search[" + i + "][start]=" + json[i]['start']
                                url += "&search[" + i + "][end]=" + json[i]['end']
                            } else {
                                url += "&search[" + i + "]=" + json[i]
                            }
                        }
                    }
                    window.location.href = url + '&export=1';
                },
                memberNav(uid) {
                    let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                    window.open(url + "&id=" + uid)
                },
                handleClick() {
                    window.location.href = this.getUrl()
                },
                getUrl() {
                    let url = ''
                    switch (this.activeName) {
                        case 'member_point' :
                            url = '{!! yzWebFullUrl('finance.point-member.index') !!}';
                            break;
                        case 'basic_set' :
                            url = '{!! yzWebFullUrl('finance.point-set.index') !!}';
                            break;
                        case 'recharge_record' :
                            url = '{!! yzWebFullUrl('point.recharge-records.index') !!}';
                            break;
                        case 'point_detailed' :
                            url = '{!! yzWebFullUrl('point.records.index') !!}';
                            break;
                        case 'point_queue' :
                            url = '{!! yzWebFullUrl('point.queue.index') !!}';
                            break;
                        case 'queue_detailed' :
                            url = '{!! yzWebFullUrl('point.queue-log.index') !!}';
                            break;
                        case 'superior_queue' :
                            url = '{!! yzWebFullUrl('point.queue-log.parentIndex') !!}';
                            break;
                    }
                    return url
                },
            },
        })
    </script>

@endsection
