@extends('layouts.base')
@section('title', '团队客户')
@section('content')
<!-- <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/> -->
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />

<style>
    .vue-title {
        display: flex;
        margin: 5px 0;
        line-height: 32px;
        font-size: 16px;
        color: #333;
        font-weight: 600;
    }

    .vue-title-left {
        width: 4px;
        height: 18px;
        margin-top: 6px;
        background: #29ba9c;
        display: inline-block;
        margin-right: 10px;
    }

    .vue-title-content {
        font-size: 14px;
        flex: 1;
    }

    .member-info {
        display: flex;
        margin: 38px;
        justify-content: space-between;
    }

    .search-top {
        display: flex;
        height: 200px;
    }

    .top-item1 {
        flex: 1;
    }

    .top-item2 {
        width: 540px;
    }

    .audit,
    .is_follow {
        width: 60px;
        height: 22px;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #13c7a7;
    }

    .un_follow {
        width: 70px;
        height: 22px;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #ffb025;
    }

    .name-over {
        line-height: 37px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .el-table_1_column_2 .cell {
        /* display: flex; */
    }

    /* 分页 */
    .pagination-right {
        text-align: center;
        margin: 50px auto;
    }

    /* 列表 */
    p {
        font-size: 12px;
        margin-top: 5px;
    }

    .cell {
        /* border:1px solid red; */
        text-align: center;
    }

    .keep {
        width: 75px;
        color: white;
        font-weight: 600 !important;
        margin: 0 auto;
        background: #ff9800;
    }

    .keep-0 {
        background: #13c7a7;
    }

    [v-cloak] {
        display: none;
    }

    .flexBox {
        display: flex;
        min-height: 250px;
        padding-bottom: 0px;
    }

    .solid_e7 {
        width: 1px;
        height: 140px;
        background-color: #e7e7e7;
        margin: 10px 50px 0 0;
    }

    .search_box {
        flex: 1;
        padding-left: 13px;
    }

    .index_pub {
        height: 42px;
        border-radius: 4px;
        margin-right: 20px;
        margin-bottom: 20px;
    }

    .index_01 {
        /* width: 180px; */
        width: 17%;
    }

    .index_02 {
        /* width: 300px; */
        width: 17.5%;
    }

    .index_03 {
        width: 202px;
    }

    .index_btn {
        width: 102px;
        height: 42px;
        border-radius: 4px;
    }

    .index_btn01 {
        background-color: #29ba9c;
    }

    .index_btn02 {
        width: 102px;
        color: #29ba9c;
        border: solid 1px #29ba9c;
    }

    .sum_text {
        font-family: SourceHanSansCN-Regular;
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 1px;
        color: #999999;
    }

    /* 列表 */
    .straight_table p {
        font-size: 12px;
        margin-top: 5px;
        font-family: SourceHanSansCN-Medium;
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 0px;
    }

    /* 操作按钮 */
    .operate {
        width: 70px;
        height: 30px;
        border-radius: 4px;
        border: solid 1px #a2a2a2;
    }

    .search_box {
        flex: 1;
        padding-left: 13px;
    }
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="total-head flexBox">
            <div class="search-top">
                <div class="top-item1">
                    <div class="vue-title" style="margin-bottom:20px;">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">搜索筛选</div>
                    </div>
                    <div class="search_box">


                        <el-input clearable v-model="mid" placeholder="请输入搜索ID" class="index_pub index_01" @keyup.enter.native="searchEvent(1)"></el-input>

                        <el-input clearable v-model="parent_id" placeholder="推荐人会员ID" class="index_pub index_01" @keyup.enter.native="searchEvent(1)"></el-input>

                        <el-input clearable v-model="keyword" placeholder="可搜索昵称/姓名/手机号" class="index_pub index_02" @keyup.enter.native="searchEvent(1)">
                        </el-input>
                        <el-select clearable v-model="followed" placeholder="是否关注" class="index_pub index_01">
                            <el-option label="已关注" value="1">
                            </el-option>
                            <el-option label="未关注" value="-1">
                            </el-option>
                        </el-select>
                        <el-select clearable v-model="isBack" placeholder="黑名单状态" class="index_pub index_01">
                            <el-option label="否" value="0">
                            </el-option>
                            <el-option label="是" value="1">
                            </el-option>
                        </el-select>
                        <el-select v-if="team_dividend_open" clearable v-model="team_dividend_level_id" placeholder="经销商等级" class="index_pub index_01">
                            <el-option v-for="(v,k) in team_dividend_levels" :label="v.level_name" :value="v.id">
                            </el-option>
                        </el-select>
{{--                        <el-select clearable v-model="tier" placeholder="层级" class="index_pub index_01">--}}
{{--                            <el-option v-for="(item,i) in tierList" :key="i" :value="item.level">--}}
{{--                                [[item.level]] 级 ([[item.total]])--}}
{{--                            </el-option>--}}
{{--                        </el-select>--}}


                        <el-input placeholder="最小值" style="width: 200px" v-model="min_amount">
                            <template slot="prepend">业绩区间</template>
{{--                            <template slot="append">至</template>--}}
                        </el-input>
                        至
                        <el-input placeholder="最大值" style="width: 100px" v-model="max_amount">
                        </el-input>

                        <br>
                            <el-select clearable v-model="time_search" placeholder="搜索时间类型">
                                <el-option
                                        v-for="(item,index) in ts_arr"
                                        :key="index"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>



                                <span class="demonstration"></span>
                                <el-date-picker
                                        :clearable=false
                                        v-model="time_arr"
                                        type="datetimerange"
                                        :picker-options="pickerOptions"
                                        range-separator="至"
                                        start-placeholder="开始日期"
                                        end-placeholder="结束日期"
                                        value-format="timestamp"
                                        align="right">
                                </el-date-picker>


                        <el-button class="index_btn index_btn01" type="primary" @click="searchEvent(1)" >搜索</el-button>
                        <el-button class="index_btn index_btn02" @click="excelEvent">导出 Excel</el-button>
                    </div>
                </div>
            </div>
            <div class="solid_e7"></div>
            <div class="top-item2">
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">会员信息</div>
                </div>
                <div style="display:flex;margin-top:20px;">
                    <div>
                        <img style="width:90px;height:90px;" :src="avatar" alt="">
                    </div>
                    <div style="margin-left:10px;display:flex;flex-direction: column;justify-content: space-between;">
                        <div style="color: #333333;font-weight:600"><span>姓名：[[realname]]</span><span style="margin-left: 30px;">手机号：[[mobile]]</span></div>
                        <div style="color: #868686;">昵称：[[nickname]]</div>
                        <div style="color: #868686;">余额：<span style="color:red">[[credit2]]</span> / 积分：<span style="color:red">[[credit1]]</span></div>
                        <div style="color: #868686;">团队业绩：[[sum_amount]]</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="total-floo">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content" style="flex:none;margin-right:24px;">客户列表</div>
                <div class="sum_text">总数:[[total]]</div>
                <template v-for="(v,k) in team_dividend_levels">
                    <div class="sum_text">
                        &nbsp;&nbsp;[[v.level_name]]:[[v.count]]
                    </div>
                </template>
            </div>
            <el-table class="straight_table" :data="tableData" style="width: 100%;">
                <el-table-column label="会员ID">
                    <template slot-scope="scope">
                        <p>[[ scope.row.member_id ]]</p>
                    </template>
                </el-table-column>

                <el-table-column v-if="team_dividend_open==1" label="推荐人">
                    <template slot-scope="scope">
                        <div v-if="scope.row.has_one_child_yz_member&&scope.row.has_one_child_yz_member.has_one_parent">
                            <el-avatar shape="square" :size="40"
                                       :src="scope.row.has_one_child_yz_member.has_one_parent.avatar_image"></el-avatar>
                            <p class="name-over">([[scope.row.has_one_child_yz_member.has_one_parent.uid]])[[scope.row.has_one_child_yz_member.has_one_parent.nickname]]</p>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="粉丝">
                    <template slot-scope="scope">
                        <div>
                            <p v-if="scope.row.has_one_child_member && scope.row.has_one_child_member.avatar"><img style="width:37px;height:37px;border-radius: 50%;" :src="scope.row.has_one_child_member.avatar" alt=""></p>
                            <p class="name-over">[[ scope.row.has_one_child_member.fans_item ? scope.row.has_one_child_member.fans_item : "未更新"]]</p>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="team_dividend_level_name" v-if="team_dividend_open==1" label="经销商等级">
                </el-table-column>

                <el-table-column label="姓名">
                    <template slot-scope="scope">
                        <p>[[ scope.row.has_one_child_member.realname]]</p>
                    </template>
                </el-table-column>

                <el-table-column prop="order_price_sum" label="业绩">
                </el-table-column>

                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <p>[[ scope.row.has_one_child_member.mobile]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="客户人数">
                    <template slot-scope="scope">
                        <p v-if="scope.row.has_one_lower && scope.row.has_one_lower!=null">[[ scope.row.has_one_lower.first ? scope.row.has_one_lower.first : 0 ]]</p>
                        <p v-else>0</p>
                    </template>
                </el-table-column>
                <el-table-column label="注册时间">
                    <template slot-scope="scope">
                        <p>[[timeDate(scope.row.has_one_child_member.createtime*1000)]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="关注">
                    <template slot-scope="scope">
                        <p class="keep keep-0" v-if="scope.row.has_one_child_fans!==null && scope.row.has_one_child_fans.follow">已关注</p>
                        <p class="keep" v-else>未关注</p>
                    </template>
                </el-table-column>

                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-popover placement="top" width="150" trigger="click">
                            <el-link :href="'{{yzWebUrl('member.member.detail', array('id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-edit"></i> 会员详情
                            </el-link>

                            <el-link :href="'{{yzWebUrl('order.order-list.index', array('member_id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-list"></i>
                                会员订单</el-link>

                            <el-link :href="'{{yzWebUrl('point.recharge.index', array('id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-credit-card"></i>
                                充值积分</el-link>

                            <el-link :href="'{{yzWebUrl('balance.recharge.index', array('member_id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-money"></i>
                                充值余额
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent-old', array('id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                直推客户
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent', array('id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                团队客户
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent-parent', array('id' => ''))}}'+[[scope.row.member_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                上级会员
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.black', array('black'=>0,'id' => ''))}}'+[[scope.row.member_id]]" v-if="scope.row.is_black==1" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-minus-circle"></i> 取消黑名单</el-link>

                            <el-link :href="'{{yzWebUrl('member.member.black', array('black'=>1,'id' => ''))}}'+[[scope.row.member_id]]" v-else :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-minus-circle"></i> 设置黑名单</el-link>
                            <el-button class="operate" slot="reference" size="mini">操作
                            </el-button>
                        </el-popover>
                    </template>
                </el-table-column>

            </el-table>
        </div>
        <!-- 分页 -->
        <div v-if="tableData.length!==0" class="fixed total-floo">
            <div class="fixed_box">
                <el-pagination background style="text-align: right;" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size="pagesize" layout="prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</div>

@include("finance.balance.verifyPopupComponent")

<script>
    var vm = new Vue({
        el: '#app',
        delimiters: ['[[', ']]'],
        data() {
            return {
                sum_amount:'',
                team_dividend_open:0,
                team_dividend_levels:[],
                time_arr: [new Date().getTime() - 3600 * 1000 * 24 * 365, new Date().getTime()],
                time_search:'',
                team_dividend_level_id:'',
                parent_id:'',
                max_amount:'',
                min_amount:'',
                avatar: "",
                nickname: "",
                realname: "",
                mobile: "",
                credit2: "",
                credit1: "",
                mid: '',
                keyword: '',
                followed: '',
                tier: [],
                tierList: [],
                isBack: '',
                tableData: [],
                currentPage: 1,
                pagesize: 6,
                row_id: '',
                total: 0,
                pickerOptions: {
                    shortcuts: [
                        {
                            text: '今天',
                            onClick(picker) {
                                const nowDate = parseInt((new Date().getTime()).toString());
                                const start = new Date();
                                const end = new Date();
                                start.setTime(new Date(new Date().setDate(new Date().getDate())).setHours(0, 0, 0, 0));
                                end.setTime(new Date(new Date().setDate(new Date().getDate())).setHours(23, 59, 59, 999));
                                picker.$emit('pick', [start, end]);
                            }
                        },
                        {
                            text: '昨天',
                            onClick(picker) {
                                const nowDate = parseInt((new Date().getTime()).toString());
                                const start = new Date();
                                const end = new Date();
                                start.setTime(new Date(new Date().setDate(new Date().getDate() - 1)).setHours(0, 0, 0, 0));
                                end.setTime(new Date(new Date().setDate(new Date().getDate() - 1)).setHours(23, 59, 59, 999));
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: '最近一周',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: '最近一个月',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: '最近三个月',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                                picker.$emit('pick', [start, end]);
                            }
                        },
                        {
                            text: '最近一年',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 365);
                                picker.$emit('pick', [start, end]);
                            }
                        },
                    ]
                },
                ts_arr: [
                    {value: 'create_time', label: '订单创建时间'},
                    {value: 'finish_time', label: '订单完成时间'},
                ],
            }
        },
        created() {
            g = this;
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.04;
                all.style.paddingBottom = h + "px";
            }
            let id = this.getParam('id')
            if (this.getParam('id') && id !== -1) {
                this.row_id = Number(this.getParam('id'));
                this.postVipInfoList(1);
                this.getLevels();
            }
            //全局监听 enter事件
            // document.onkeydown = (e) => {
            //     let key = window.event.keyCode
            //     if (key == 13) {
            //         g.searchEvent()
            //     }
            // }
        },
        methods: {
            getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            //导出
            excelEvent() {
                let isSubmied = false;
                if (verifyed && (expireTime === 0 || expireTime * 1000 < Date.now())) {
                    showGetVerifyCodePopup();
                    return false;
                }
                if (isSubmied) {
                    return false;
                } else {
                    isSubmied = true;
                }
                if(this.time_arr[0]){
                    var start_time = Math.floor(this.time_arr[0]/1000);
                }else{
                    var start_time = 0;
                }
                if(this.time_arr[1]){
                    var end_time = Math.floor(this.time_arr[1]/1000);
                }else{
                    var end_time = 0;
                }

                let url = `{!! yzWebFullUrl('member.member.agent-export') !!}&min_amount=${this.min_amount}&max_amount=${this.max_amount}&team_dividend_level_id=${this.team_dividend_level_id}&parent_id=${this.parent_id}&time_search=${this.time_search}&end_time=${end_time}&start_time=${start_time}&id=${this.row_id}&aid=${this.mid}&keyword=${this.keyword}&followed=${this.followed}&isblack=${this.isBack}&level=${this.tier}`
                window.location.href = url
                console.log(url);
            },
            //搜索层级
            getLevels() {
                this.$http.post("{!!yzWebFullUrl('member.member.getLevels')!!}", {
                    id: this.row_id,
                }).then(res => {
                    let {data}=res.body;
                    //层级
                    this.tierList =data
                    console.log(this.tierList);
                })
            },
            //会员信息列表
            postVipInfoList(page) {
                if(!this.time_arr[0]){
                    var start_time = 0;
                }else{
                    var start_time = Math.floor(this.time_arr[0]/1000);
                }
                if(!this.time_arr[1]){
                    var end_time = 0;
                }else{
                    var end_time = Math.floor(this.time_arr[1]/1000);
                }
                this.$http.post("{!!yzWebFullUrl('member.member.agent-show')!!}", {
                    id: this.row_id,
                    page: page,
                    aid: this.mid,
                    keyword: this.keyword,
                    followed: this.followed,
                    isblack: this.isBack,
                    level: this.tier,
                    team_dividend_level_id:this.team_dividend_level_id,
                    start_time:start_time,
                    end_time:end_time,
                    parent_id:this.parent_id,
                    max_amount:this.max_amount,
                    min_amount:this.min_amount,
                    time_search:this.time_search,
                }).then(res => {
                    let {
                        member,
                        list,
                        level_total,
                        current_page,
                        per_page,
                        total,
                        team_dividend_open,
                        sum_amount,
                        team_dividend_levels,
                    } = res.body.data
                    //当前页码
                    this.currentPage = list.current_page
                    //一页显示几条数据
                    this.pagesize = list.per_page
                    //总数据
                    this.total = list.total
                    //会员头像
                    this.avatar = member.avatar
                    //昵称
                    this.nickname = member.nickname
                    //真实姓名
                    this.realname = member.realname
                    //手机号码
                    this.mobile = member.mobile
                    //余额
                    this.credit2 = member.credit2
                    //积分
                    this.credit1 = member.credit1
                    //经销商管理是否开启
                    this.team_dividend_open = team_dividend_open;
                    //总业绩
                    this.sum_amount = sum_amount;
                    //经销商等级统计列表
                    this.team_dividend_levels = team_dividend_levels;
                    let {
                        data
                    } = list
                    if (data !== null && data.length !== 0) {
                        this.tableData = data
                    } else {
                        this.tableData = [];
                    }

                })
            },
            //当前页码
            handleCurrentChange(page) {
                this.postVipInfoList(page)
            },
            //搜索
            searchEvent(page) {
                this.postVipInfoList(page)
            },
            //时间的转换
            timeDate(date) {
                let d = new Date(date);
                let resDate = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();;
                return resDate;
            }
        }
    })
</script>

@endsection