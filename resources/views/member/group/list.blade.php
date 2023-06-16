@extends('layouts.base')
@section('会员分组')
@section('content')
<!-- 全局样式 -->
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

    .btnClass {
        font-size: 14px;
        width: 100px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        border: 1px solid #29ba9c;
        border-radius: 10px;
        color: #29ba9c;
        cursor: pointer;
    }

    .vue-title .el-link--inner {
        color: #29ba9c;
    }

    .el-table {
        /* margin-left:55px; */
    }

    .cell {
        color: black;
        opacity: .9;
        font-size: 13px;
        font-family: inherit;
        text-align: center;
    }

    /* 小窗超出隐藏 */
    .el-table--scrollable-x .el-table__body-wrapper {
        overflow: hidden;
    }
</style>
<div class="all">
    <div id="app">
        <div class="total-head" style="padding-bottom:50px">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">会员分组</div>
                <el-link :underline="false" class="btnClass" href="{{ yzWebUrl('member.member-group.form') }}">+添加新分组</el-link>
            </div>
            <el-table v-loading="loading" :data="tableList" style="width: 100%">
                <el-table-column label="分组名称">
                    <template slot-scope="scope">
                        <span style="margin-left: 10px">[[scope.row.group_name]]</span>
                    </template>
                </el-table-column>

                <el-table-column label="会员数">
                    <template slot-scope="scope">
                        <p>[[ scope.row.member.count ? scope.row.member.count : 0 ]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-link :underline='false' :href="'{{yzWebUrl('member.member.index', array('search' => '5','groupid' => ''))}}'+[[scope.row.id]]">
                            <i class="el-icon-s-custom" style="font-size: 20px;cursor: pointer;margin-right: 10px;"></i>
                        </el-link>
                        <el-link :underline='false' :href="'{{yzWebUrl('member.member-group.form', array('id' => ''))}}'+[[scope.row.id]]">

                            <i class="el-icon-edit" style="font-size: 20px;cursor: pointer;margin-right: 10px;"></i>
                        </el-link>
                        <i class="el-icon-delete" @click="handleDelete(scope.$index, scope.row)" style="font-size: 20px;cursor: pointer;"></i>
                    </template>
                </el-table-column>
            </el-table>
            <!-- <div style="margin-top:50px">
                <el-pagination layout="prev, pager, next,jumper" background style="text-align:center" :page-size="pagesize" :current-page="currentPage" :total="total" @current-change="handleCurrentChange">
                </el-pagination>
            </div> -->
        </div>
        <!-- 分页 -->
        <div class="fixed total-floo">
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
        delimiters: ['[[', ']]'],
        data() {
            return {
                tableData: [{
                    id: "1",
                    groupName: '分组一',
                    memberNum: '12',
                }, {
                    id: "2",
                    groupName: '分组二',
                    memberNum: '12',
                }, {
                    id: "3",
                    groupName: '分组三',
                    memberNum: '12',
                }, {
                    id: "4",
                    groupName: '分组四',
                    memberNum: '12',
                }],
                tableList: [],
                //分页
                total: 1,
                pagesize: 1,
                currentPage: 1,
                loading: false
            }
        },
        created() {
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.04;
                all.style.paddingBottom = h + "px";
            }
            this.postGroupData(1);
        },
        methods: {
            //列表数据请求
            postGroupData(page) {
                this.$http.post("{!!yzWebFullUrl('member.member-group.show')!!}", {
                    page: page
                }).then(res => {
                    if (res.status === 200) {
                        // this.$message.success("列表请求成功")
                        this.loading = true;
                        setTimeout(() => {
                            this.loading = false;
                        }, 500)
                        console.log(res);
                        let {
                            data: data,
                            current_page: page,
                            per_page: per,
                            total: total,
                        } = res.body.data.groupList;
                        //数据列表
                        this.tableList = data;
                        //总页数
                        this.total = total;
                        //一页显示多少条数据
                        this.pagesize = per;
                        //当前页码
                        this.currentPage = page;
                    }
                })
            },
            //分组事件
            handleCurrentChange(page) {
                // console.log(page);
                this.postGroupData(page);
            },
            handleEdit(index, row) {
                console.log(index, row);
            },
            handleDelete(index, row) {
                // this.tableList = this.tableList.filter(item => row.id != item.id)
                console.log(index);
                console.log(row);
                this.$confirm('确定删除当前分组?, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    //点击确定的操作(调用接口)
                    this.$http.post("{!!yzWebFullUrl('member.member-group.destroy')!!}", {
                        group_id: row.id
                    }).then(res => {
                        if (res.status == 200) {
                            this.$message.success("删除成功")
                            this.postGroupData(this.currentPage);
                        } else {
                            this.$message.error("删除失败")
                        }
                    })
                })
            },
        }
    })
</script>@endsection