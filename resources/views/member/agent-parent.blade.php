@extends('layouts.base')
@section('title', '上级会员')
@section('content')
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

    .btn_follow {
        height: 22px;
        line-height: 22px;
        color: #fff;
        font-weight: 600;
        font-size: 12px;
        text-align: center;
        font-family: arial, "Hiragino Sans GB", "Microsoft Yahei", 微软雅黑, 宋体, 宋体, Tahoma, Arial, Helvetica, STHeiti;
        margin: 0 auto;
        border-radius: 4px;

    }

    .audit,
    .is_follow {
        width: 60px;
        background-color: #13c7a7;
    }

    .un_follow {
        width: 70px;
        background-color: #ffb025;
    }

    .no_follow {
        width: 70px;
        background-color: #999999;
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
    .straight_table p {
        font-size: 12px;
        margin-top: 5px;
        font-family: SourceHanSansCN-Medium;
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 0px;
        /* color: #333333; */
    }

    .cell {
        /* border:1px solid red; */
        text-align: center;
    }

    /* 优化进来显示 */
    [v-cloak] {
        display: none;
    }

    .flexBox {
        display: flex;
        /* border:1px solid red; */
        height:180px;
        padding-bottom: 0px;
    }

    .solid_e7 {
        width: 1px;
        height: 140px;
        background-color: #e7e7e7;
        margin: 10px 50px 0 25px;
    }

    .search_box {
        /* border:1px solid red; */
        /* width:calc(100% - 200px); */
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
        width:15%;
    }
    .index_02 {
        /* width: 180px; */
        width:19%;
    }

    .index_btn {
        width: 102px;
        height: 42px;
        border-radius: 4px;
        margin-right: 10px;
    }

    .index_btn01 {
        background-color: #29ba9c;
    }

    .index_btn02 {
        width: 102px;
        color: #29ba9c;
        border: solid 1px #29ba9c;
    }

    .index_btn03 {
        width: 160px;
        color: #29ba9c;
        border: solid 1px #29ba9c;
    }

    .el-button+.el-button {
        margin: 0;
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
                        <el-input clearable v-model="mid" placeholder="请输入搜索ID" class="index_pub index_01"></el-input>
                        <el-input clearable v-model="keyword" placeholder="可搜索昵称/姓名/手机号" class="index_pub index_02">
                        </el-input>
                        <el-select clearable v-model="followed" placeholder="是否关注" class="index_pub index_01">
                            <el-option label="未关注" value="2">
                            </el-option>
                            <el-option label="已关注" value="1">
                            </el-option>
                            <el-option label="取消关注" value="0">
                            </el-option>
                        </el-select>
                        <el-button class="index_btn index_btn01" type="primary" @click="searchEvent" @keyup.enter="searchEvent">搜索</el-button>
                        <el-button style="margin-right:10px;" class="index_btn index_btn02" @click="excelEvent">导出 Excel</el-button>
                        <el-button class="index_btn index_btn03" @click="excelBossEvent">导出 直推上级Excel</el-button>
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
                    </div>
                </div>
            </div>
        </div>
        <div class="total-floo">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content" style="flex:none;margin-right:24px;">客户列表</div>
                <div style="font-size:14px;color: #999999;">总数:[[total]]</div>
            </div>
            <el-table class="straight_table" :data="tableData" style="width: 100%;">
                <el-table-column label="会员ID">
                    <template slot-scope="scope">
                        <p>[[ scope.row.parent_id ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="上级">
                    <template slot-scope="scope">
                        <p>[[ scope.row.level ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="经销商等级">
                    <template slot-scope="scope">
                        <!-- <p v-if="scope.row.has_one_team_dividend !==null && scope.row.has_one_team_dividend.has_one_level !==null && scope.row.has_one_team_dividend.has_one_level.level_name ">[[ scope.row.has_one_team_dividend.has_one_level.level_name ]]</p>
                        <p v-else>不是经销商</p> -->
                        <p>[[isType(1,scope.row.has_one_team_dividend)]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="粉丝">
                    <template slot-scope="scope">
                        <div>
                            <p v-if="scope.row.has_one_member!==null &&  scope.row.has_one_member && scope.row.has_one_member.avatar">
                                <img style="width:37px;height:37px;border-radius: 50%;" :src="scope.row.has_one_member.avatar" alt="">
                            </p>
                            <p class="name-over" v-if="scope.row.has_one_member!==null &&  scope.row.has_one_member && scope.row.has_one_member.fans_item">[[scope.row.has_one_member.fans_item]]</p>
                            <p v-else>未更新</p>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="姓名">
                    <template slot-scope="scope">
                        <p v-if="scope.row.has_one_member !==null &&  scope.row.has_one_member && scope.row.has_one_member.realname">[[ scope.row.has_one_member.realname ]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <p v-if="scope.row.has_one_member !==null && scope.row.has_one_member && scope.row.has_one_member.mobile">[[ scope.row.has_one_member.mobile ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="注册时间">
                    <template slot-scope="scope">
                        <p v-if="scope.row.has_one_member !==null && scope.row.has_one_member && scope.row.has_one_member.createtime">[[ timeDate(scope.row.has_one_member.createtime*1000) ]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="关注">
                    <template slot-scope="scope">
                        <div v-if="!scope.row.has_one_fans">
                            <p class="btn_follow no_follow">未关注</p>
                        </div>
                        <div v-else>
                            <div v-if="scope.row.has_one_fans.uid && scope.row.has_one_fans.follow == 1">
                                <p class="btn_follow is_follow">已关注</p>
                            </div>
                            <div v-else>
                                <p class="btn_follow un_follow">取消关注</p>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-popover placement="top" width="150" trigger="click">
                            <el-link :href="'{{yzWebUrl('member.member.detail', array('id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-edit"></i> 会员详情
                            </el-link>

                            <el-link :href="'{{yzWebUrl('order.order-list.index', array('member_id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-list"></i>
                                会员订单</el-link>

                            <el-link :href="'{{yzWebUrl('point.recharge.index', array('id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-credit-card"></i>
                                充值积分</el-link>

                            <el-link :href="'{{yzWebUrl('balance.recharge.index', array('member_id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-money"></i>
                                充值余额
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent-old', array('id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                直推客户
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent', array('id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                团队客户
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent-parent', array('id' => ''))}}'+[[scope.row.parent_id]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                上级会员
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.black', array('black'=>0,'id' => ''))}}'+[[scope.row.parent_id]]" v-if="scope.row.is_back" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-minus-circle"></i> 取消黑名单</el-link>

                            <el-link :href="'{{yzWebUrl('member.member.black', array('black'=>1,'id' => ''))}}'+[[scope.row.parent_id]]" v-else :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-minus-circle"></i> 设置黑名单</el-link>

                            <el-button slot="reference" size="mini">操作
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
                avatar: "",
                nickname: "",
                realname: "",
                mobile: "",
                credit2: "",
                credit1: "",
                mid: '',
                keyword: '',
                followed: '',
                tableData: [],
                itemList: [],
                currentPage: 1,
                pagesize: 6,
                total: 0,
                row_id: '',
            }
        },
        created() {
            g = this;
             //优化在不同设备固定定位挡住的现象设置父元素的内边距
             window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.03;
                all.style.paddingBottom = h + "px";
            }
            this.itemList = this.tableData;
            let id = this.getParam('id')
            if (this.getParam('id') && id !== -1) {
                this.row_id = Number(this.getParam('id'));
                this.postVipInfoList(1);
            }
            //全局监听enter事件
            document.onkeydown = (e) => {
                let key = window.event.keyCode;
                if (key == 13) {
                    g.searchEvent();
                }
            }
        },
        computed: {
            isType() {
                return (id, obj) => {
                    // console.log(id, obj);
                    if (id == 1) {
                        if (obj !== null && obj) {
                            if (obj.has_one_level !== null && obj.has_one_level) {
                                if (obj.has_one_level.level_name != null && obj.has_one_level.level_name) {
                                    return obj.has_one_level.level_name
                                } else {
                                    return "不是经销商"
                                }
                            } else {
                                return "不是经销商"
                            }
                        } else {
                            return "不是经销商"
                        }
                    }

                }
            }
        },
        methods: {
            getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },
            //导出excel
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

                let url = `{!! yzWebFullUrl('member.member.agent-parent-export') !!}` + '&id=' + this.row_id+"&member_id="+this.mid+"&member="+this.keyword+"&followed="+this.followed;
                window.location.href = url;
                console.log(url);
            },
            //导出上级excel
            excelBossEvent() {
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

                let url = `{!! yzWebFullUrl('member.member.first-agent-export') !!}` + '&id=' + this.row_id;
                window.location.href = url;
            },
            //会员信息列表
            postVipInfoList(page) {
                this.$http.post("{!!yzWebFullUrl('member.member.agent-parent-show')!!}", {
                    id: this.row_id,
                    page,
                    member_id: this.mid,
                    member: this.keyword,
                    followed: this.followed
                }).then(res => {
                    let {
                        member,
                        list
                    } = res.body.data;
                    //会员头像
                    this.avatar = member.avatar;
                    //昵称
                    this.nickname = member.nickname;
                    //真实姓名
                    this.realname = member.realname;
                    //手机号码
                    this.mobile = member.mobile;
                    //余额
                    this.credit2 = member.credit2;
                    //积分
                    this.credit1 = member.credit1;
                    // 列表
                    let {
                        data,
                        current_page,
                        total,
                        per_page
                    } = list;
                    if(data!==null && data.length!==0){
                        this.tableData = data;
                    }else{
                        this.tableData = [];
                    }
                    this.currentPage = current_page
                    this.pagesize = per_page
                    this.total = total
                })
            },
            //当前页码
            handleCurrentChange(page) {
                this.postVipInfoList(page)
            },
            //搜索
            searchEvent() {
                this.postVipInfoList(this.currentPage);
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