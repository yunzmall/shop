@extends('layouts.base')
@section('title', '资格申请')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
<style scoped>
    .form-item-w {
        /* border: 1px solid red; */
        width: 100%;
        margin-top: 20px;
        padding-left: 15px;
    }

    .input-w {
        width: 22%;
        margin-right: 20px;
        margin-bottom: 20px;
    }

    .el-input__inner {
        /* height: 35px; */
        border: 1px solid #ccc;
    }

    /* 选项下拉 输入框 按钮  */
    .select-box {
        margin-right: 12px;
        display: flex;
    }

    .el-date-editor .el-range-separator {
        /* width: 13%; */
        line-height: 38px;
    }

    .select-w {
        margin-top: 20px;
        width: 150px;
    }

    .el-input--suffix .el-input__inner {
        /* border-radius: 9px; */
        /* height: 30px; */
    }

    .el-date-editor--daterange.el-input__inner {
        width: 252px;
        /* height: 35px; */
    }

    .el-date-editor--timerange.el-input__inner {
        width: 252px;
        /* height: 35px; */
    }

    .el-range-editor.el-input__inner {
        padding: 1px 10px;
    }

    .el-date-editor.el-input {
        width: 120px;
    }

    .el-button {
        /* height: 35px; */
        /* line-height: 0; */
    }

    .button_box {
        margin-left: 20px;
    }

    /* 申请列表 */
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
        margin-top: 20px;
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

    [v-cloak] {
        display: none;
    }

    .keep {
        width: 75px;
        color: white;
        font-weight: bold;
        margin: 0 auto;
        background: #ff9800;
    }

    .keep_0 {
        background: #9c27b0;
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
</style>

<div class="all">
    <div id="app" v-cloak>
        <!-- 资格申请 -->
        <div class="total-head">
            <el-form>
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">申请资格</div>
                </div>
                <el-form-item class="form-item-w">
                    <el-input clearable class="input-w" v-model="quali.member_id" placeholder="会员id"></el-input>
                    <el-input clearable class="input-w" v-model="quali.member_pet" placeholder="会员昵称/姓名/手机号"></el-input>
                    <el-select clearable class="input-w" v-model="quali.member_referrer" placeholder="请选择邀请人">
                        <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                        </el-option>
                    </el-select>
                    <el-input clearable class="input-w" v-model="quali.referrer_name" placeholder="推荐人昵称/姓名/手机号"></el-input>
                    <div class="select-box">
                        <!-- 日期和时间 -->
                        <el-date-picker value-format="timestamp" style="margin-right:5px;"  type="datetimerange" v-model="quali.date" align="right" unlink-panels range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" :picker-options="pickerOptions">
                        </el-date-picker>
                        <div class="button_box">
                            <el-button class="input_btn search_box" @click="searchEvent" @keyup.enter="searchEvent" type="primary">搜索</el-button>
                            <el-button class="input_btn export_box" @click="deriveEvent">导出</el-button>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <!-- 申请列表 -->
        <div class="total-floo" style="padding-bottom:1px">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">申请列表<span>总数:&nbsp;[[total]]</span></div>
            </div>
            <!-- 列表+分页查询 -->
            <el-table v-loading="loading" style="width: 100%;" :data="apalyList" :header-cell-style='{"text-align":"center"}' :cell-style='{"text-align":"center"}'>
                <el-table-column label="ID">
                    <template slot-scope="scope">
                        <p>[[scope.row.uid]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="推荐人">
                    <template slot-scope="scope">
                        <div class="marTop" v-if="scope.row.yz_member.parent_id!==null && scope.row.yz_member.parent_id">
                            <div v-if="scope.row.yz_member.agent!==null && scope.row.yz_member.agent">
                                <img v-if="scope.row.yz_member.agent.avater" width="30" height="30" :src="scope.row.yz_member.agent.avater">
                                <p v-if="scope.row.yz_member.agent.nickname">[[scope.row.yz_member.agent.nickname]]</p>
                                <p v-else>未更新</p>
                            </div>
                        </div>
                        <div v-else>
                            <div class="keep keep_0" v-if="scope.row.yz_member.is_agent==1">总店</div>
                            <div class="not" v-else>暂无</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="粉丝">
                    <template slot-scope="scope">
                        <div class="marTop" v-if="scope.row.avatar!==null && scope.row.avatar">
                            <img width="30" height="30" :src="scope.row.avatar">
                            <p v-if="scope.row.fans_item">[[scope.row.fans_item]]</p>
                            <p v-else>未更新</p>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="姓名">
                    <template slot-scope="scope">
                        <p>[[scope.row.realname]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <p v-if="scope.row.mobile">[[scope.row.mobile]]</p>
                        <!-- <p v-else>无</p> -->
                    </template>
                </el-table-column>
                <el-table-column label="申请时间">
                    <template slot-scope="scope">
                        <p v-if="typeof scope.row.yz_member !== 'undefined'">[[scope.row.yz_member.apply_time | timeDates]]</p>
                        <!-- <p v-else>无</p> -->
                    </template>
                </el-table-column>
                <el-table-column label="详情">
                    <template slot-scope="scope">
                        <el-link type="primary" :underline="false" style="font-size: 12px; font-weight: normal" @click="detailsLink(scope.row.uid)">
                            <i class="el-icon-view" style="font-size:18px;color: #333;"></i>
                        </el-link>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="pass(scope.row.uid)">通过</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <!-- 分页 -->
        <div v-if="apalyList.length!==0" class="fixed total-floo">
            <div class="fixed_box">
                <el-pagination background style="text-align: right;" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size="pagesize" layout="prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: '#app',
        // 防止后端冲突,修改ma语法符号
        delimiters: ['[[', ']]'],
        data() {
            return {
                quali: {
                    member_id: "",
                    member_pet: "",
                    member_referrer: "1",
                    referrer_name: "",
                    search_time: [],
                    date: [],
                },
                options: [{
                    value: '1',
                    label: '推荐人'
                }, {
                    value: '0',
                    label: '总店'
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
                //总人数
                apalyListSum: 0,
                //申请列表
                apalyList: [],
                currentPage: 1, //当前的页码
                pagesize: 4, //每页显示的行数
                total: 1, //总数
                isCount: 0,
                loading: true
            }
        },
        created() {
            g = this
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.04;
                all.style.paddingBottom = h + "px";
            }
            //获取当前数据总数
            this.apalyListSum = this.apalyList.length
            //请求当前网络数据
            this.postRelation(1)
            //全局监听 enter事件
            document.onkeydown = (e) => {
                let key = window.event.keyCode;
                if (key == 13) {
                    g.searchEvent();
                }
            }
        },
        //定义全局的方法
        beforeCreate() {
            that = this
        },
        filters: {
            timeDates(date) {
                if (date!==null && date) {
                    return that.timeDate(date * 1000)
                } else {
                    return ""
                }
            }
        },
        methods: {
            //申请列表
            postRelation(page) {
                this.$http.post("{!!yzWebFullUrl('member.member-relation.apply-show')!!}", {
                    page: page,
                    search: this.isCount >= 1 ? {
                        uid: this.quali.member_id,
                        member: this.quali.member_pet,
                        referee: this.quali.member_referrer,
                        referee_info: this.quali.referrer_name,
                        times: this.quali.date !== null ? {
                            start: parseInt(this.quali.date[0] / 1000),
                            end: parseInt(this.quali.date[1] / 1000)
                        } : {
                            start: "",
                            end: ""
                        }
                    } : ""
                }).then(res => {
                    this.loading = true;
                    if (res.data.result == 1) {
                        setTimeout(() => {
                            this.loading = false;
                        }, 200)
                        let {
                            data: data,
                            current_page: current,
                            per_page: per,
                            total: total
                        } = res.body.data.list;
                        console.log(res);
                        // console.log(current, per, total, 95684465);
                        console.log(data);
                        //当前页码
                        this.currentPage = current;
                        //每页显示的行数
                        this.pagesize = per;
                        //总页数
                        this.total = total;
                        console.log(this.pagesize);
                        if(data!==null && data.length!==0){
                        //申请列表
                        this.apalyList = data;
                        }else{
                            this.apalyList = []
                        }
                        //总人数
                        // this.apalyListSum = this.apalyList.length * this.total;
                    }
                })
            },
            isTypeNull(str) {
                // 如果对象 或者为空则返回
                if (str == null) {
                    return false
                } else {
                    return true;
                }
            },
            //页面切换的方式
            handleCurrentChange(page) {
                this.postRelation(page);
            },
            //时间的转换
            timeDate(date) {
                let d = new Date(date);
                let resDate = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();;
                return resDate;
            },
            //搜索
            searchEvent() {
                this.isCount++
                this.postRelation();
            },
            //导出
            deriveEvent() {
                let start = isNaN(parseInt(this.quali.date[0] / 1000)) ? "" : parseInt(this.quali.date[0] / 1000);
                let end = isNaN(parseInt(this.quali.date[1] / 1000)) ? "" : parseInt(this.quali.date[1] / 1000);
                let url = `{!! yzWebFullUrl('member.member-relation.export') !!}` + '&search[uid]=' + this.quali.member_id + '&search[member]=' + this.quali.member_pet + '&search[referee]=' + this.quali.member_referrer + '&search[referee_info]=' + this.quali.referrer_name + '&search[times][start]=' + start + '&search[times][end]=' + end;
                window.location.href = url;
                // console.log(url);
            },
            //详情
            detailsLink(id) {
                //传递参数跳转到会员详情
                let link = `{!! yzWebUrl('member.member.detail') !!}` + '&id=' + id;
                window.location.href = link;
            },
            //通过
            pass(id) {
                console.log(id);
                this.$http.post("{!!yzWebFullUrl('member.member-relation.chk-apply')!!}", {
                    id: id
                }).then(res => {
                    console.log(res);
                    // 审核通过成功
                    if (res.data.result == 1) {
                        this.$message.success(res.data.msg)
                        history.go(0);
                    } else {
                        this.$message.error(res.data.msg)
                    }
                })
            }
        },
    })
</script>
@endsection