@extends('layouts.base')
@section('title', '修改记录')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
<style scoped>
    /* 修改记录列表 */
    .total-head {
        padding-bottom: 1px;
    }

    .vue-title-content span {
        opacity: .6;
        margin-left: 14px;
    }

    .el-table th>.cell {
        font-family: SourceHanSansCN-Medium;
        font-size: 14px;
        font-weight: 600;
        font-stretch: normal;
        letter-spacing: 0px;
        color: #666666;
    }

    .table_box p {
        font-family: SourceHanSansCN-Medium;
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 0px;
        color: #333333;
    }

    /* 分页 */
    .pagination-right {
        margin: 50px auto;
        text-align: center;
    }
</style>
<div class="all">
    <div id="app">
        <!-- 修改记录列表 -->
        <div class="total-head">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">修改记录</div>
            </div>
            <!-- 列表+分页查询 -->
            <el-table class="table_box" v-loading="loading" style="width:100%;margin:20px 0 0 6.5px;" :data="updatedList" :header-cell-style='{"text-align":"center"}' :cell-style='{"text-align":"center"}'>
                <el-table-column label="ID">
                    <template slot-scope="scope">
                        <p>[[scope.row.id]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="会员id">
                    <template slot-scope="scope">
                        <p>[[scope.row.uid]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="修改前上级id">
                    <template slot-scope="scope">
                        <p>[[scope.row.parent_id]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="修改后上级id">
                    <template slot-scope="scope">
                        <p>[[scope.row.after_parent_id]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="状态">
                    <template slot-scope="scope">
                        <p v-if="scope.row.status_value==0">修改失败</p>
                        <p v-else>[[scope.row.status_value]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="修改时间">
                    <template slot-scope="scope">
                        <p>[[scope.row.created_at]]</p>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div v-if="updatedList.length!==0" class="fixed total-floo">
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
        name: "record",
        // 防止后端冲突,修改ma语法符号
        delimiters: ['[[', ']]'],
        data() {
            return {
                updatedList: [],
                currentPage: 1, //当前的页码
                pagesize: 5, //每页显示的行数
                total: 1, //总页数
                loading: true
            }
        },
        created() {
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.04;
                all.style.paddingBottom = h + "px";
            }
            this.postRecord(1);
        },
        methods: {
            //关系修改记录列表
            postRecord(page) {
                this.$http.post("{!!yzWebFullUrl('member.member.record-datas')!!}", {
                    page: page
                }).then(res => {
                    console.log(res);
                    if (res.data.result == 1) {
                        this.loading = true;
                        setTimeout(() => {
                            this.loading = false;
                        }, 300)
                        const {
                            data,
                            current_page: page,
                            per_page: per,
                            total
                        } = res.body.data.list;
                        console.log(res);
                        // console.log(data);
                        // console.log(page);
                        // console.log(per);
                        // console.log(total);
                        if(data!==null && data.length!==0){
                            this.updatedList = data;
                        }else{
                            this.updatedList = [];
                        }
                        //当前的页码
                        this.currentPage = page;
                        //每页显示的条数
                        this.pagesize = per;
                        //总页数
                        this.total = total;
                    } else {
                        this.$message.error(res.data.msg)
                    }
                })
            },
            //当前切换的页码
            handleCurrentChange(page) {
                this.postRecord(page)
            }
        },
    })
</script>@endsection