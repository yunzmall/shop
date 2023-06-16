@extends('layouts.base')
@section('title', "余额明细")
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div class="all" style="margin-top: -10px">
        <div id="app" v-cloak>
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">余额明细</div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-row>
                            <el-form-item label="">
                                <el-input v-model="search_form.member_id" placeholder="会员ID"></el-input>
                            </el-form-item>
                            <el-form-item label="">
                                <el-input v-model="search_form.member" placeholder="昵称/姓名/手机号"></el-input>
                            </el-form-item>
                            <el-form-item label="">
                                <el-input v-model="search_form.order_sn" placeholder="订单号"></el-input>
                            </el-form-item>
                            <el-select v-model="search_form.member_level" style="margin-left:5px;" placeholder="会员等级" clearable>
                                <el-option v-for="item in member_levels" :key="item.id" :label="item.level_name" :value="item.id"></el-option>
                            </el-select>
                            <el-select v-model="search_form.member_group" style="margin-left:5px;" placeholder="会员分组" clearable>
                                <el-option v-for="item in member_groups" :key="item.id" :label="item.group_name" :value="item.id"></el-option>
                            </el-select>
                            <el-select v-model="search_form.source" style="margin-left:5px;" placeholder="业务类型" clearable>
                                <el-option v-for="item in source_name" :key="item.id" :label="item.value" :value="item.id"></el-option>
                            </el-select>
                            <el-select v-model="search_form.type" style="margin-left:5px;" placeholder="收入/支出" clearable>
                                <el-option v-for="item in type_list" :key="item.value" :label="item.label" :value="item.value"></el-option>
                            </el-select>
                        </el-row>
                        <el-row>
                            <el-form-item label="">
                                <el-date-picker v-model="search_form.search_time" value-format="timestamp" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                            </el-form-item>
                            <el-form-item label="">
                                <el-button @click="switchSearchTime(1)" plain>今</el-button>
                                <el-button @click="switchSearchTime(2)" plain>昨</el-button>
                                <el-button @click="switchSearchTime(3)" plain>近7天</el-button>
                                <el-button @click="switchSearchTime(4)" plain>近30天</el-button>
                                <el-button @click="switchSearchTime(5)" plain>近1年</el-button>
                            </el-form-item>
                            <el-form-item label="" style="float: right">
                                <el-button type="primary" @click="search(1)">搜索</el-button>
                                <el-button type="primary" @click="export_(1)">导出</el-button>
                            </el-form-item>
                        </el-row>

                    </el-form>
                </div>
            </div>
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">明细列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                            总数：[[ total ]]&nbsp;
                            金额合计：[[ amount ]]
                            </span></div>
                    </div>
                    <el-table :data="data_list" style="width: 100%">
                        <el-table-column label="时间" align="center" prop="created_at" width="auto"></el-table-column>
                        <el-table-column label="会员ID" align="center" prop="member_id" width="auto"></el-table-column>
                        <el-table-column label="粉丝" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <div v-if="scope.row.member">
                                    <div v-if="scope.row.member.avatar">
                                        <img :src="scope.row.member.avatar" alt="" style="width:40px;height:40px;border-radius:50%">
                                    </div>
                                    <div v-else>
                                        <img :src="head_img" alt="" style="width:40px;height:40px;border-radius:50%">
                                    </div>
                                    <div @click="gotoMember(scope.row.member.uid)" style="line-height:32px;color:#29BA9C;cursor: pointer;" class="vue-ellipsis">[[scope.row.member.nickname]]</div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="余额" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span style="background-color: #fff7e6;color: #efb43c;padding: 5px">
                                余额：[[ scope.row.new_money ]]
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="业务类型" align="center" prop="service_type_name" width="auto"></el-table-column>
                        <el-table-column label="收入\支出" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <div>[[ scope.row.change_money ]]</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="" label="操作" align="center" >
                            <template slot-scope="scope">
                                <el-button @click="detail(scope.row.id)">查看详情</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page" >
                <el-row>
                    <el-col align="right">
                        <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                                       :page-size="per_page" :current-page="current_page" background
                        ></el-pagination>
                    </el-col>
                </el-row>
            </div>
        </div>
    </div>

    <script>
        let member_levels = {!! $member_levels ?: '' !!};
        let member_groups = {!! $member_groups ?: '' !!};
        let source_name = {!! $source_name ?: '' !!};
        let head_img = '{!! $head_img ?: '' !!}';
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    data_list: [],
                    member_levels:member_levels,
                    member_groups:member_groups,
                    source_name:source_name,
                    head_img:head_img,
                    type_list: [
                        {
                            value: 1,
                            label: '收入'
                        },
                        {
                            value: 2,
                            label: '支出'
                        },
                    ],
                    search_form: {
                        member_id:"",
                        member:"",
                        order_sn:"",
                        member_level:"",
                        member_group:"",
                        source:"",
                        type:'',
                        search_time:[],
                    },
                    amount:0.00,
                    current_page: 1,
                    total: 1,
                    per_page: 1,
                }
            },
            created() {

            },
            mounted() {
                this.search_form.search_time = this.switchSearchTime(4);
            },
            methods: {
                getData(page) {
                    let json = {
                        page: page,
                        search: {
                            member:this.search_form.member,
                            member_id:this.search_form.member_id,
                            order_sn:this.search_form.order_sn,
                            member_level:this.search_form.member_level,
                            member_group:this.search_form.member_group,
                            source:this.search_form.source,
                            type:this.search_form.type,
                            search_time:this.search_form.search_time,
                        },
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('finance.balance-records.search') !!}',json).then(function(response) {
                        if (response.data.result) {
                            let list = response.data.data.list;
                            this.data_list = list.data;
                            this.current_page = list.current_page;
                            this.total = list.total;
                            this.per_page = list.per_page;
                            this.amount = response.data.data.amount;
                            loading.close();
                        } else {
                            this.$message({message: response.data.msg, type: 'error'});
                        }
                        loading.close();
                    }, function(response) {
                        this.$message({message: response.data.msg, type: 'error'});
                        loading.close();
                    });
                },
                search(val) {
                    this.getData(val);
                },
                export_(){
                    this.$confirm('是否确定导出数据?', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                        var url = "{!! yzWebFullUrl('finance.balance-records.export') !!}";
                        if (this.search_form.member_id) {
                            url += "&search[member_id]="+this.search_form.member_id;
                        }
                        if (this.search_form.member) {
                            url += "&search[member]="+this.search_form.member;
                        }
                        if (this.search_form.order_sn) {
                            url += "&search[order_sn]="+this.search_form.order_sn;
                        }
                        if (this.search_form.source) {
                            url += "&search[source]="+this.search_form.source;
                        }
                        if (this.search_form.member_level) {
                            url += "&search[member_level]="+this.search_form.member_level;
                        }
                        if (this.search_form.member_group) {
                            url += "&search[member_group]="+this.search_form.member_group;
                        }
                        if (this.search_form.type) {
                            url += "&search[type]="+this.search_form.type;
                        }
                        if (this.search_form.search_time) {
                            url += "&search[search_time]="+this.search_form.search_time;
                        }
                        window.location.href = url;
                    }).catch(() => {
                        this.$message({type: 'info', message: '已取消导出'});
                    });
                },
                gotoMember(id) {
                    window.location.href = `{!! yzWebFullUrl('member.member.detail') !!}`+`&id=`+id;
                },
                detail(id) {
                    window.location.href = `{!! yzWebFullUrl('finance.balance.lookBalanceDetail') !!}`+'&id='+id;
                },
                getStartEndTime (num = 1) {
                    // 一天的毫秒数
                    const MillisecondsADay = 24*60*60*1000 * num
                    // 今日开始时间戳
                    const todayStartTime = new Date(new Date().setHours(0, 0, 0, 0)).getTime()
                    // 今日结束时间戳
                    const todayEndTime = new Date(new Date().setHours(23,59,59,999)).getTime()
                    // 昨日开始时间戳
                    const yesterdayStartTime = todayStartTime - MillisecondsADay
                    // 昨日结束时间戳
                    const yesterdayEndTime = todayEndTime - MillisecondsADay
                    return [parseInt(yesterdayStartTime),parseInt(num > 1 ? todayEndTime : yesterdayEndTime)]
                },
                switchSearchTime(timeType) {
                    switch (timeType) {
                        //今天
                        case 1:
                            timeArr = this.getStartEndTime(0)
                            break;
                        //昨天
                        case 2:
                            timeArr = this.getStartEndTime(1)
                            break;
                        //近7天
                        case 3:
                            timeArr = this.getStartEndTime(7)
                            break;
                        //近30天
                        case 4:
                            timeArr = this.getStartEndTime(30)
                            break;
                        //近1年
                        case 5:
                            timeArr = this.getStartEndTime(365)
                            break;
                    }
                    this.$nextTick(() => {
                        this.search_form.search_time = timeArr;
                        this.getData(1);
                    })
                },
            },
        })
    </script>
@endsection