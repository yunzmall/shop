@extends('layouts.base')
@section('title', '会员等级')
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

    .btnClass {
        font-size: 14px;
        width: 100px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        border: 1px solid;
        border-radius: 10px;
        color: #29ba9c;
        border-color: #29ba9c;
        cursor: pointer;
    }

    .vue-title .el-link--inner {
        color: #29ba9c;
    }
</style>

<div class="all">
    <div id="app">
        <div class="total-head" style="padding-bottom:50px;">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">会员等级</div>
                <el-link class="btnClass" :underline="false" href="{{ yzWebUrl('member.member-level.form') }}">+添加新等级</el-link>
            </div>
            <el-table v-loading="loading" :data="tableList" style="width: 100%">
                <el-table-column label="等级权重" align="center">
                    <template slot-scope="scope">
                        <span style="margin-left: 10px">[[scope.row.level]]</span>
                    </template>
                </el-table-column>

                <el-table-column label="等级名称" align="center">
                    <template slot-scope="scope">
                        <p>[[ scope.row.level_name ]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="升级条件" align="center">
                    <template slot-scope="scope">
                        <div v-if="level_type!==null && level_type !== 0">
                            <div v-if="level_type == 1">
                                <p v-if="scope.row.order_count!==null && scope.row.order_count>0">
                                    订单数量满[ [[scope.row.order_count]] ]个
                                </p>
                                <p v-else>不自动升级</p>
                            </div>
                            <div v-else-if="level_type == 2">
                                <p v-if="scope.row.goods_id">
                                    购买商品[ID: [[scope.row.goods_id]] ] [[scope.row.goods!==null ? scope.row.goods.title : "" ]]升级
                                </p>
                                <p v-else>不自动升级</p>
                            </div>
                            <div v-else-if="level_type == 3">
                                <p>
                                    团队业绩满 [ [[scope.row.team_performance ? scope.row.team_performance : 0]] ] 元升级
                                </p>
                            </div>
                            <div v-else-if="level_type == 4">
                                <p>
                                    余额一次性充值满[ [[scope.row.balance_recharge ? scope.row.balance_recharge : 0]] ]元升级
                                </p>
                            </div>
                        </div>
                        <div v-else>
                            <p v-if="scope.row.order_money!==null && scope.row.order_money>0">
                                订单金额满[ [[scope.row.order_money]] ]元
                            </p>
                            <p v-else>不自动升级</p>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center">
                    <template slot-scope="scope">
                        <!-- 更新 -->
                        <el-link :underline='false' :href="'{{yzWebUrl('member.member-level.form', array('id' => ''))}}'+[[scope.row.id]]">
                            <i class="el-icon-edit" style="font-size: 20px;cursor: pointer;margin-right: 10px;"></i>
                        </el-link>
                        <!-- 删除 -->
                        <i class="el-icon-delete" @click="handleDelete(scope.$index, scope.row)" style="font-size: 20px;cursor: pointer;"></i>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <!-- 分页 -->
        <div v-if="tableList.length!==0" class="fixed total-floo">
            <div class="fixed_box">
                <el-pagination background style="text-align: right;" @current-change="handleCurrentChange" :current-page.sync="currentPage" :page-size="pagesize" layout="prev, pager, next, jumper" :total="total">
                </el-pagination>
            </div>
        </div>
    </div>
</div>

<script>
    var vm = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        data() {
            return {
                tableData: [],
                tableList: [],
                //页码数
                currentPage: 1,
                //一页显示数据
                pagesize: 1,
                //总页数
                total: 1,
                //加载
                loading: false,
                level_type: null
            }
        },
        created() {
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.04;
                all.style.paddingBottom = h + "px";
            }
            this.postLevel(1);
        },
        methods: {
            postLevel(page) {
                this.$http.post("{!!yzWebFullUrl('member.member-level.show')!!}", {
                    page: page
                }).then(res => {
                    if (res.status === 200) {
                        // this.$message.success("请求列表成功")
                        // this.loading = true;
                        // setTimeout(() => {
                        //     this.loading = false;
                        // }, 500)
                        let {
                            data: data,
                            total: total,
                            per_page: per,
                            current_page: page
                        } = res.body.data.levelList;
                        console.log(res);
                        console.log(data);
                        if(data!==null && data.length!==0){
                            //等级列表数据
                            this.tableList = data;
                        }else{
                            this.tableList = [];
                        }
                        //总数
                        this.total = total;
                        //一页显示多少条数据
                        this.pagesize = per;
                        //当前页码
                        this.currentPage = page
                        //判断购买条件
                        let {
                            level_type
                        } = res.body.data;
                        this.level_type = Number(level_type);
                        // console.log(this.level_type,84654);
                    } else {
                        this.$message.error(res.data.msg)
                    }
                })
            },
            handleCurrentChange(page) {
                console.log(page);
                this.postLevel(page);
            },
            handleEdit(index, row) {
                console.log(index, row);
            },
            //删除当前会员等级
            handleDelete(index, row) {
                console.log(row.id);
                this.$confirm('确定删除当前等级?, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    console.log(row.id,99999);
                    //点击确定的操作(调用接口)
                    this.$http.post("{!!yzWebFullUrl('member.member-level.destroy')!!}", {
                        id: row.id
                    }).then(res => {
                        if (res.data.result === 1) {
                            this.loading = true;
                            this.$message.success("删除成功")
                            setTimeout(() => {
                                this.loading = false;
                            }, 300)
                            this.postLevel(this.currentPage);
                            // history.go(0);
                        } else {
                            this.$message.error("删除失败")
                        }
                    })
                })
            }
        }
    })
</script>
@endsection