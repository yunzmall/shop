@extends('layouts.base')
@section('title', '直推客户')
@section('content')
<!-- <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/> -->
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />

<style scoped>
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
        /* width: 742px; */
    }

    .top-item1 {
        flex: 1;
    }

    .top-item2 {
        width: 740px;
    }

    .audit,
    .is_follow {
        width: 60px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #13c7a7;
    }

    .un-audit {
        width: 60px;
        height: 22px;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #a5a5a4;
    }

    .client_static {
        width: 110px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #13c7a7;
    }

    .un_follow {
        width: 70px;
        height: 30px;
        text-align: center;
        line-height: 30px;
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
        text-align: center;
    }

    .keep {
        width: 80px;
        height: 30px;
        border-radius: 4px;
        line-height: 30px;
        font-family: MicrosoftYaHei;
        font-size: 14px;
        font-weight: 600 !important;
        font-stretch: normal;
        letter-spacing: 1px;
        color: #ffffff;
        margin: 0 auto;
    }

    .keep_already {
        background-color: #13c7a7;
    }

    .keep_cancel {
        background-color: #ffb025;
    }

    .keep_not {
        background-color: #a5a5a4;
    }

    .audit,
    .client_static {
        font-weight: 600 !important;
    }

    .btn_follow {
        height: 22px;
        line-height: 22px;
        color: #fff;
        font-weight: 600 !important;
        font-size: 12px;
        text-align: center;
        font-family: arial, "Hiragino Sans GB", "Microsoft Yahei", 微软雅黑, 宋体, 宋体, Tahoma, Arial, Helvetica, STHeiti;
        margin: 0 auto;
        border-radius: 4px;

    }

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

    [v-cloak] {
        display: none;
    }

    .flexBox {
        height: 180px;
        display: flex;
        padding-bottom: 0px;
    }

    .search_box {
        /* border:1px solid red; */
        /* width:calc(100% - 200px); */
        flex: 1;
        padding-left: 13px;
    }

    .solid_e7 {
        width: 1px;
        height: 140px;
        background-color: #e7e7e7;
        margin: 10px 50px 0 0;
    }

    .index_pub {
        height: 42px;
        border-radius: 4px;
        margin-right: 20px;
        margin-bottom: 10px;
    }

    .index_01 {
        /* width: 180px; */
        width: 20%;
    }

    .index_02 {
        /* width: 300px; */
        width: 30%;
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

    .el-table th>.cell {
        font-family: SourceHanSansCN-Medium;
        font-size: 14px;
        font-weight: 600;
        font-stretch: normal;
        letter-spacing: 0px;
        color: #666666;
    }

    .operate {
        width: 70px;
        height: 30px;
        border-radius: 4px;
        border: solid 1px #a2a2a2;
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
                        <el-input class="index_pub index_01" v-model="mid" placeholder="请输入搜索ID" clearable></el-input>
                        <el-input class="index_pub index_02" v-model="keyword" placeholder="可搜索昵称/姓名/手机号" clearable>
                        </el-input>
                        <el-select class="index_pub index_01" v-model="followed" placeholder="是否关注" clearable>
                            <el-option label="取消关注" value="0">
                            </el-option>
                            <el-option label="已关注" value="1">
                            </el-option>
                            <el-option label="未关注" value="2">
                            </el-option>
                        </el-select>
                        <el-select class="index_pub index_01" v-model="status" placeholder="状态" clearable>
                            <el-option label="未审核" value="0">
                            </el-option>
                            <el-option label="已审核" value="2">
                            </el-option>
                        </el-select>
                        <el-select class="index_pub index_01" v-model="isBack" placeholder="黑名单状态" clearable>
                            <el-option label="否" value="0">
                            </el-option>
                            <el-option label="是" value="1">
                            </el-option>
                        </el-select>
                        <el-button class="index_btn" type="primary" @click="search" @keyup.enter="search">搜索</el-button>
                        <el-button class="index_btn index_btn02" @click="exports">导出
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
                <div class="sum_text">总数:[[total]]</div>
            </div>
            <el-table class="straight_table" :data="tableData" style="width: 100%;">
                <el-table-column width="80px" label="会员ID">
                    <template slot-scope="scope">
                        <p>[[ scope.row.uid ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="推荐人">
                    <template slot-scope="scope">
                        <div v-if="scope.row.yz_member.parent_id">
                            <p><img style="width:37px;height:37px;border-radius: 50%;" :src="scope.row.yz_member.agent.avatar" alt=""></p>
                            <p class="name-over">[[ scope.row.yz_member.agent.nickname ]]</p>
                        </div>
                        <div v-else>
                            <p v-if="scope.row.yz_member.is_agent===1" class="is_follow">总店</p>
                            <p v-else class="un_follow">暂无</p>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="粉丝">
                    <template slot-scope="scope">
                        <div>
                            <p v-if="scope.row.avatar"><img style="width:37px;height:37px;border-radius: 50%;" :src="scope.row.avatar" alt=""></p>
                            <p v-if="scope.row.fans_item" class="name-over">[[ scope.row.fans_item ]]</p>
                            <p v-else class="name-over">未更新</p>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="姓名">
                    <template slot-scope="scope">
                        <p>[[ scope.row.realname]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <p>[[ scope.row.mobile ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="状态">
                    <template slot-scope="scope">
                        <div v-if="scope.row.yz_member.is_agent!==null && scope.row.yz_member.is_agent==1">
                            <p v-if="scope.row.yz_member.status==0" class="un-audit">未审核</p>
                            <p v-else-if="scope.row.yz_member.status==1" class="un-audit">未审核</p>
                            <p v-else class="audit">已审核</p>
                        </div>
                        <div v-else>-</div>
                    </template>
                </el-table-column>
                <el-table-column label="客户状态" width="200">
                    <template slot-scope="scope">
                        <p class="client_static" v-if="scope.row.yz_member.inviter===0">暂定用户</p>
                        <p class="client_static" v-else>锁定关系客户</p>
                    </template>
                </el-table-column>

                <el-table-column label="注册时间">
                    <template slot-scope="scope">
                        <p>[[timeDate(scope.row.createtime*1000)]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="关注">
                    <template slot-scope="scope">
                        <div v-if="scope.row.has_one_fans && scope.row.has_one_fans!==null">
                            <p class="keep keep_already" v-if="scope.row.has_one_fans.followed == 1">已关注</p>
                            <p class="keep keep_cancel" v-else>取消关注</p>
                        </div>
                        <div v-else>
                            <p class="keep keep_not">未关注</p>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-popover placement="top" width="150" trigger="click">
                            <el-link :href="'{{yzWebUrl('member.member.detail', array('id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-edit"></i> 会员详情
                            </el-link>
                            <el-link :href="'{{yzWebUrl('order.order-list.index', array('member_id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-list"></i>
                                会员订单</el-link>

                            <el-link :href="'{{yzWebUrl('point.recharge.index', array('id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-credit-card"></i>
                                充值积分</el-link>

                            <el-link :href="'{{yzWebUrl('balance.recharge.index', array('member_id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-money"></i>
                                充值余额
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent-old', array('id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                直推客户
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent', array('id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                团队客户
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.agent-parent', array('id' => ''))}}'+[[scope.row.uid]]" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-exchange"></i>
                                上级会员
                            </el-link>

                            <el-link :href="'{{yzWebUrl('member.member.black', array('black'=>0,'id' => ''))}}'+[[scope.row.uid]]" v-if="scope.row.yz_member.is_black==1" :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-minus-circle"></i> 取消黑名单</el-link>

                            <el-link :href="'{{yzWebUrl('member.member.black', array('black'=>1,'id' => ''))}}'+[[scope.row.uid]]" v-else :underline="false" style="height:30px;line-height:30px;"><i class="fa fa-minus-circle"></i> 设置黑名单</el-link>

                            <el-button class="operate" slot="reference" size="mini" @click="handleEdit(scope.$index, scope.row)">操作
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
                status: '',
                isBack: '',
                tableData: [],
                itemList: [],
                currentPage: 1,
                pagesize: 6,
                total: 0,
                row_id: '',
                isAttentionClass: 1
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
            let id = this.getParam('id');
            if (this.getParam('id') && id !== -1) {
                this.row_id = Number(this.getParam('id'))
                this.postVipInfoList(1);
            }
            //全局监听searchBtnt enter事件
            document.onkeydown = (e) => {
                let key = window.event.keyCode;
                if (key == 13) {
                    g.search();
                }
            }
        },
        updated() {
            console.log(this.isAttentionClass);
        },
        methods: {
            getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },
            handleEdit(index, row) {

            },
            //会员信息列表
            postVipInfoList(page) {
                this.$http.post("{!!yzWebFullUrl('member.member.agent-old-show')!!}", {
                    page,
                    id: this.row_id,
                    aid: this.mid,
                    keyword: this.keyword,
                    followed: this.followed,
                    status: this.status,
                    isblack: this.isBack

                }).then(res => {
                    let {
                        member,
                        list,

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
                    console.log(res);
                    console.log(member);
                    let {
                        current_page,
                        per_page,
                        total
                    } = list;
                    if (list.data !== null && list.data.length !== 0) {
                        //客户列表
                        this.tableData = list.data;
                    }else{
                        this.tableData = [];
                    }
                    // console.log(this.tableData);
                    console.log(current_page, per_page, total);
                    //当前页
                    this.currentPage = current_page;
                    //每页显示几行
                    this.pagesize = per_page;
                    //总数量
                    this.total = total;
                })
            },
            //当前页码
            handleCurrentChange(page) {
                this.postVipInfoList(page)
            },
            //搜索
            search() {
                //搜索当前粉丝
                this.postVipInfoList(this.currentPage);
            },
            //时间的转换
            timeDate(date) {
                let d = new Date(date);
                let resDate = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
                return resDate;
            },
            //导出
            exports() {
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

                // 不能换行
                let url = `{!! yzWebFullUrl('member.member.do-export')!!}&id=${this.row_id}` + "&aid=" + this.mid + "&keyword=" + this.keyword + "&followed=" + this.followed + "&status=" + this.status + "&isblack=" + this.isBack;
                window.location.href = url;
                // console.log(url);
            },
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            }
        },
        filters: {
            filtersData(data) {
                if (data.has_one_fans !== null && data.has_one_fans !== "") {
                    if (data.has_one_fans.followed !== null) {
                        if (data.has_one_fans.followed == 0) {
                            return "取消关注"
                        } else if (data.has_one_fans.followed == 1) {
                            return "已关注"
                        } else if (data.has_one_fans.followed == 2) {
                            return "未关注"
                        }
                    }
                } else {
                    return "已关注"
                }
            }
        }
    })
</script>

@endsection