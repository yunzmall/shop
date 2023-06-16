@extends('layouts.base')
@section('title', '会员合并记录')
@section('content')
    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <style scoped>
        /* 设置input宽度 */
        .input-w {
            width: 200px;
            margin-right: 22px;
        }

        .input_box,
        .button_box {
            display: inline-block;
        }

        /* 会员合并记录列表 */
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

        /* 分页 */
        .pagination-right {
            text-align: center;
            margin: 50px auto;
        }

        /* 选项卡部分 */
        .nav {
            cursor: default;
            user-select: none;
        }

        .new {
            display: flex;
            width: 1000px;
            margin-left: 0px;
        }

        .nav-btn {
            cursor: pointer;
            color: #666666;
            font-size: 14px;
            font-family: SourceHanSansCN-Regular;
            padding: 9px;
            border-radius: 5px;
            margin-right: 10px;
            background-color: white;
            font-weight: normal;
            font-stretch: normal;
            letter-spacing: 2px;
        }

        .nav-btn-back {
            color: #FFF;
            background-color: #29BA9C;
        }
    </style>
    <div class="all">
        <div id="app">
            <div class="nav total-head">
                <ul class="new">
                    <li @click="btnEvent(i)" class="nav-btn " :class="{'nav-btn-back': isIndex===i}" v-for='(item,i) in navlist' :key="i">[[item]]</li>
                </ul>
            </div>
            <div class="total-head">
                <el-form>
                    <!-- 会员合并记录-->
                    <el-form-item>
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">会员合并记录</div>
                        </div>
                    </el-form-item>
                    <el-form-item>
                        <div class="input_box">
                            <el-input clearable class="input-w" v-model="merge.beforeId" placeholder="合并前会员id"></el-input>
                            <el-input clearable class="input-w" v-model="merge.afterId" placeholder="合并后会员id">
                            </el-input>
                        </div>
                        <div class="button_box">
                            <el-button @click="submit" @keyup.enter="submit" type="primary">搜索</el-button>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <!-- 邀请码列表 -->
            <div class="total-floo">
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">合并记录<span>总数:&nbsp;[[total]]</span></div>
                </div>
                <!-- 列表+分页查询 -->
                <el-table v-loading="loading" style="width:100%; padding-bottom:1px;" :data="mergeList" :header-cell-style='{"text-align":"center"}' :cell-style='{"text-align":"center"}'>
                    <el-table-column label="ID">
                        <template slot-scope="scope">
                            <p>[[scope.row.id]]</p>

                        </template>
                    </el-table-column>
                    <el-table-column label="合并前会员id">
                        <template slot-scope="scope">
                            <p v-if="isIndex==0">[[scope.row.member_id]]</p>
                            <p v-else-if="isIndex==1">[[scope.row.old_uid]]</p>
                            <p v-else>[[scope.row.mark_member_id]]</p>
                        </template>
                    </el-table-column>
                    <el-table-column label="合并后会员id">
                        <template slot-scope="scope">
                            <p v-if="isIndex==0">[[scope.row.member_id_after]]</p>
                            <p v-else-if="isIndex==1">[[scope.row.new_uid]]</p>
                            <p v-else>[[scope.row.member_id]]</p>
                        </template>
                    </el-table-column>
                    <el-table-column label="合并时间">
                        <template slot-scope="scope">
                            <p>[[scope.row.created_at | createdAt]]</p>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <!-- 分页 -->
            <div v-if="mergeList.length!==0" class="fixed total-floo">
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
            name: "auth",
            delimiters: ["[[", "]]"],
            data() {
                return {
                    merge: {
                        beforeId: "",
                        afterId: ""
                    },
                    mergeList: [],
                    //总人数
                    mergeListSum: 0,
                    currentPage: 1, //当前的页码
                    pagesize: 5, //每页显示的行数
                    total: 0, //总数
                    //导航头部标签
                    navlist: ["自动合并记录", "绑定手机合并记录", "点击合并记录"],
                    //切换选项卡标识
                    isIndex: 0,
                    loading: true
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
                //获取当前数据总数
                this.mergeListSum = this.mergeList.length;
                //全局监听searchBtnt enter事件
                document.onkeydown = (e) => {
                    let key = window.event.keyCode;
                    if (key == 13) {
                        g.submit();
                    }
                }
                if (this.isIndex == 0) {
                    this.postAuto(1);
                } else if (i == 1) {
                    this.postBind(1);
                } else {
                    this.postMerge(1);
                }
            },
            filters: {
                createdAt(date) {
                    if (date !== null && date) {
                        if (g.isIndex == 1) {
                            return g.timeDate(date * 1000)
                        } else {
                            return date
                        }
                    } else {
                        return ""
                    }
                }
            },
            methods: {
                //点击选项卡函数
                btnEvent(i) {
                    //点击切换清空数据
                    this.merge.beforeId = "";
                    this.merge.afterId = "";
                    this.mergeList = [];
                    this.isIndex = i;
                    window.sessionStorage.setItem("iMerge", this.isIndex);
                    if (i === 0) {
                        this.postAuto(1);
                    } else if (i === 1) {
                        this.postBind(1);
                    } else {
                        this.postMerge(1);
                    }
                },
                //自动合并记录
                postAuto(page) {
                    this.$http.post("{!!yzWebFullUrl('member.merge-log.auth-merge')!!}", {
                        page: page,
                        search: {
                            member_id: this.merge.beforeId,
                            mark_member_id: this.merge.afterId
                        }
                    }).then(res => {
                        this.infoAll(res)
                    })
                },
                //绑定手机合并记录
                postBind(page) {
                    this.$http.post("{!!yzWebFullUrl('member.merge-log.bind-tel')!!}", {
                        page: page,
                        search: {
                            member_id: this.merge.beforeId,
                            mark_member_id: this.merge.afterId
                        }
                    }).then(res => {
                        this.infoAll(res)
                    })
                },
                //点击合并记录
                postMerge(page) {
                    this.$http.post("{!!yzWebFullUrl('member.merge-log.click-merge')!!}", {
                        page: page,
                        search: {
                            member_id: this.merge.beforeId,
                            mark_member_id: this.merge.afterId
                        }
                    }).then(res => {
                        this.infoAll(res)
                    })
                },
                infoAll(res) {
                    if (res.data.result == 1) {
                        this.loading = true;
                        setTimeout(() => {
                            this.loading = false;
                        }, 300)
                        let {
                            data,
                            current_page,
                            per_page,
                            total
                        } = res.body.data.list;
                        if (data !== null && data.length !== 0) {
                            this.mergeList = data;
                        } else {
                            this.mergeList = [];
                        }
                        this.currentPage = current_page;
                        this.pagesize = per_page;
                        this.total = total;
                    } else {
                        this.$message.error(res.data.msg);
                    }
                },
                isTypeNull(str) {
                    // 如果对象 或者为空则返回
                    if (str === null) {
                        return false
                    } else {
                        return true;
                    }
                },
                //页面切换的方式
                handleCurrentChange(page) {
                    if (this.isIndex === 0) {
                        this.postAuto(page);
                    } else if (this.isIndex === 1) {
                        this.postBind(page);
                    } else {
                        this.postMerge(page)
                    }
                },
                //请求当前合并列表
                submit() {
                    if (this.isIndex == 0) {
                        this.postAuto(this.currentPage);
                    } else if (this.isIndex == 1) {
                        this.postBind(this.currentPage);
                    } else {
                        this.postMerge(this.currentPage)
                    }
                },
                //时间的转换
                timeDate(date) {
                    let d = new Date(date);
                    let resDate = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();;
                    return resDate;
                }
            }
        })
    </script>@endsection