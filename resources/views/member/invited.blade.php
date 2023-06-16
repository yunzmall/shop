@extends('layouts.base')
@section('title', '会员邀请码')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
<style scoped>
    /* 设置input宽度 */
    .input-w {
        width: 180px;
        margin-right: 20px;
    }

    .input-w-s {
        width: 225px;
    }

    .el-input__inner {
        /* height: 35px; */
        border: 1px solid #ccc;
    }

    .input_box,
    .select_box,
    .button_box {
        display: inline-block;
        margin-bottom: 13px;
    }

    .button_box {
        margin-left: 15px;
    }

    .input_box {
        padding-left: 14px;
    }

    /* 邀请码列表 */
    .vue-title-content span {
        opacity: .6;
        margin-left: 14px;
    }

    .el-table .cell {
        color: #333;
        font-family: inherit;
    }

    .el-table td div {
        font-family: "微软雅黑";
        font-weight: 400;
    }

    /* 无数据显示 */
    .not {
        width: 40px;
        height: 22px;
        color: #fff;
        line-height: 22px;
        text-align: center;
        margin: 22px auto;
        cursor: default;
        font-weight: 600;
        font-family: "微软雅黑";
        background-color: #999999;
    }

    .marTop {
        margin-top: 5px;
        line-height: 0px;
    }

    .marTop>img {
        margin-bottom: 15px;
    }

    /* 分页 */
    .pagination-right {
        margin: 50px auto;
        text-align: center;
    }

    .input_btn {
        height: 42px;
        border-radius: 4px;
    }

    /* 搜索 */
    .search_box {
        width: 102px;
    }

    /* 导出 */
    .export_box {
        width: 102px;
        color: #29ba9c;
        border: solid 1px #29ba9c;
    }

    [v-cloak] {
        display: none;
    }
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="total-head">
            <el-form>
                <!-- 会员邀请码 -->
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">会员邀请码</div>
                    </div>
                </el-form-item>
                <!-- 邀请码输入 -->
                <el-form-item>
                    <div class="input_box">
                        <el-input clearable class="input-w" v-model="invite.code" placeholder="邀请码"></el-input>
                        <el-input clearable class="input-w input-w-s" v-model="invite.id" @input="invite.id=invite.id.replace(/[^\d]/g,'')" placeholder="可搜索邀请人id或被邀请人id">
                        </el-input>
                    </div>
                    <div class="select_box">
                        <!-- 日期和时间 -->
                        <el-date-picker value-format="timestamp"  type="datetimerange" v-model="invite.date" align="right" unlink-panels range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" :picker-options="invite.pickerOptions">
                        </el-date-picker>
                    </div>
                    <div class="button_box">
                        <el-button @click="searchBtn" @keyup.enter="searchBtn" type="primary" class="input_btn search_box">搜索</el-button>
                        <el-button @click="deriveEvent" class="input_btn export_box">导出</el-button>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <!-- 邀请码列表 -->
        <div class="total-floo" style="padding-bottom:1px;">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">邀请码记录<span>总数:&nbsp;[[total]]</span></div>
            </div>
            <!-- 列表+分页查询 -->
            <el-table v-loading="loading" style="width:100%;margin:20px 0 0 6.5px;" :data="recordList" :header-cell-style='{"text-align":"center"}' :cell-style='{"text-align":"center"}'>
                <el-table-column label="ID">
                    <template slot-scope="scope">
                        <p>[[scope.row.id]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="使用人">
                    <template slot-scope="scope">
                        <div class="marTop" v-if="isTypeNull(scope.row.yz_member)">
                            <img v-if="scope.row.yz_member.has_one_member && scope.row.yz_member.has_one_member.avatar" width="30" height="30" :src="scope.row.yz_member.has_one_member.avatar">
                            <p v-if="scope.row.yz_member.has_one_member && scope.row.yz_member.has_one_member.nickname">
                                [[scope.row.yz_member.has_one_member.nickname]]</p>
                            <p v-else class="not">暂定</p>
                        </div>
                        <div class="not" v-else>暂无</div>
                    </template>
                </el-table-column>
                <el-table-column label="推荐人">
                    <template slot-scope="scope">
                        <div class="marTop" v-if="isTypeNull(scope.row.has_one_mc_member)">
                            <img width="30" height="30" v-if="scope.row.has_one_mc_member.has_one_member && scope.row.has_one_mc_member.has_one_member.avatar" :src="scope.row.has_one_mc_member.has_one_member.avatar" alt="">
                            <p v-if="scope.row.has_one_mc_member.has_one_member && scope.row.has_one_mc_member.has_one_member.nickname">
                                [[scope.row.has_one_mc_member.has_one_member.nickname]]</p>
                            <p v-else class="not">暂定</p>
                        </div>
                        <div class="not" v-else>暂无</div>
                    </template>
                </el-table-column>
                <el-table-column label="邀请码">
                    <template slot-scope="scope">
                        <p>[[scope.row.invitation_code]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="使用时间">
                    <template slot-scope="scope">
                        <p>[[scope.row.created_at]]</p>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div v-if="recordList.length!==0" class="fixed total-floo">
            <div class="fixed_box">
                <el-pagination background style="text-align: right;" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size="pagesize" layout="prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</div>
<script>
    const vm = new Vue({
        el: "#app",
        name: "invited",
        // 防止后端冲突,修改ma语法符号
        delimiters: ['[[', ']]'],
        data() {
            return {
                invite: {
                    code: "",
                    id: "",
                    select_info: [],
                    time: "1",
                    date: "",
                    start: "",
                    end: "",
                    times: [{
                        value: '1',
                        label: '搜索时间'
                    }, {
                        value: '0',
                        label: '时间不限'
                    }],
                    pickerOptions: {
                        shortcuts: [{
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
                        }]
                    },
                },
                recordList: [],
                currentPage: 1, //当前的页码
                pagesize: 5, //每页显示的行数
                total: 0, //总数
                //记录请求
                isi: true,
                loading: true,
                inviteArr: []
            }
        },
        created() {
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.03;
                all.style.paddingBottom = h + "px";
            }
            g = this;
            //请求当前列表数据
            if (this.isi) {
                this.isi = false
                this.invite.time = [];
                this.postInvited(1);
            }
            //全局监听searchBtnt enter事件
            document.onkeydown = (e) => {
                let key = window.event.keyCode;
                if (key == 13) {
                    g.searchBtn();
                }
            }
        },
        updated() {
            //  console.log(this.invite);
        },
        watch: {
            invite: {
                handler(newVal) {
                    this.inviteArr["code"] = this.invite.code;
                    this.inviteArr["mid"] = this.invite.id;
                    this.inviteArr["searchtime"] = this.invite.time;
                    this.inviteArr["times"] = [this.invite.start];
                    this.inviteArr["times"].push(this.invite.end);
                },
                immediate: true,
                deep: true
            }
        },
        methods: {
            isTypeNull(str) {
                // 如果对象 或者为空则返回
                if (str !== null) {
                    return true
                } else {
                    return false;
                }
            },
            //页面切换的方式
            handleCurrentChange(page) {
                this.postInvited(page)
            },
            postInvited(page) {
                //邀请码记录列表
                this.$http.post("{!!yzWebFullUrl('member.member_invited.show')!!}", {
                    page: page,
                    search: {
                        code: this.invite.code,
                        mid: this.invite.id,
                        // searchtime: this.invite.time,
                        times: {
                            start: parseInt(this.invite.start),
                            end: parseInt(this.invite.end)
                        }
                    }
                }).then(res => {
                    if (res.status === 200) {
                        setTimeout(() => {
                            this.loading = false;
                        }, 100)
                        let {
                            data: list,
                            current_page: page,
                            per_page: per,
                            total: total
                        } = res.body.data.list;
                        console.log(list);
                        if (list !== null && list.length !== 0) {
                            //列表数据
                            this.recordList = list;
                        } else {
                            this.recordList = [];
                        }
                        //当前页码
                        this.currentPage = page;
                        //总数
                        this.total = total;
                        //一页显示几行数据
                        this.pagesize = per;
                    } else {
                        this.$message.error("获取数据失败");
                    }
                })
            },
            //点击搜索
            searchBtn() {
                if (!this.invite.date) {
                    this.invite.date = [];
                }
                // console.log(this.invite.date[0]);
                // console.log(this.invite.date[1]);
                this.invite.start = this.invite.date[0] / 1000;
                this.invite.end = this.invite.date[1] / 1000;
                //请求搜索指定数据
                this.postInvited(this.currentPage);
            },
            //时间的转换
            timeDate(date) {
                let d = new Date(date);
                let resDate = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();;
                return resDate;
            }, //导出
            deriveEvent() {
                // console.log(this.inviteArr,4545465);
                // console.log(this.inviteArr.code,49999);
                console.log(this.inviteArr["times"][0], this.inviteArr["times"][1], 99999);
                let start = "";
                let end = "";
                if (this.inviteArr["times"][0] && this.inviteArr["times"][1]) {
                    start = isNaN(this.inviteArr["times"][0]) ? "" : parseInt(this.inviteArr["times"][0]);
                    end = isNaN(this.inviteArr["times"][1]) ? "" : parseInt(this.inviteArr["times"][1]);
                }
                console.log(start, end);
                let url = `{!! yzWebFullUrl('member.member_invited.export')!!}&search[code]=` +
                    this.inviteArr.code + "&search[mid]=" + this.inviteArr.mid + "&search[times][start]=" + start + "&search[times][end]=" + end;
                console.log(url)
                window.location.href = url;
            },
        },
    })
</script>@endsection