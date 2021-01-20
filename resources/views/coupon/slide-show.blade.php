@extends('layouts.base')
@section('title', '优惠券使用记录')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<style>
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
    .el-tabs__item,.is-top{font-size:16px}
    .el-tabs__active-bar { height: 3px;}
    .edit-i{display:none;}
    .el-table_1_column_3:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-nav" style="margin-bottom:15px">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="优惠券设置" name="1"></el-tab-pane>
                <el-tab-pane label="优惠券列表" name="2"></el-tab-pane>
                <el-tab-pane label="领取发放记录" name="3"></el-tab-pane>
                <el-tab-pane label="分享领取记录" name="4"></el-tab-pane>
                <el-tab-pane label="使用记录" name="5"></el-tab-pane>
                <el-tab-pane label="领券中心幻灯片" name="6"></el-tab-pane>
            </el-tabs>
        </div>
        <div class="vue-main">
            <div class="vue-main-form">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">幻灯片列表</div>
                    <div class="vue-main-title-button">
                        <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addModal">添加幻灯片</el-button>
                    </div>
                </div>
                <el-table :data="list" style="width: 100%">
                    <el-table-column label="ID" align="center" prop="id" width="80"></el-table-column>
                    <el-table-column label="显示顺序" align="center" prop="sort">
                        <template slot-scope="scope">
                            <el-popover class="item" placement="top" effect="light">
                                <div style="text-align:center;">
                                    <el-input v-model="change_sort" size="small"
                                                style="width:100px;"></el-input>
                                    <el-button size="small"
                                                @click="confirmChangeSort(scope.row.id)">确定
                                    </el-button>
                                </div>
                                <a slot="reference">
                                    <i class="el-icon-edit edit-i" title="点击编辑排序"
                                        @click="editTitle(scope.$index)"></i>
                                </a>
                            </el-popover>
                            [[scope.row.sort]]
                        </template>
                    </el-table-column>
                    <el-table-column label="标题" align="center" prop="title"></el-table-column>
                    <el-table-column label="链接" align="center" prop="slide_link"></el-table-column>
                    <el-table-column label="状态" align="center" prop="">
                        <template slot-scope="scope">
                            <div>
                                <div>[[scope.row.status_name]]</div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="refund_time" label="操作" align="center" width="320">
                        <template slot-scope="scope">
                            <el-link title="编辑幻灯片" :underline="false"  :href="'{{ yzWebUrl('coupon.slide-show.edit', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                <i class="iconfont icon-ht_operation_edit"></i>
                            </el-link>
                            <el-link title="删除幻灯片" :underline="false" @click="del(scope.row.id,scope.$index)" style="width:50px;">
                                <i class="iconfont icon-ht_operation_delete"></i>
                            </el-link>
                        </template>
                    </el-table-column>
                    
                </el-table>
            </div>
        </div>
        <!-- 分页 -->
        <div class="vue-page" v-if="total>0">
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
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            return {
                activeName:'6',
                list:[],
                times:[],
                change_sort: "",//修改排序弹框赋值
                
                search_form:{

                },

                rules: {},
                current_page:1,
                total:0,
                per_page:1,
            }
        },
        created() {

        },
        mounted() {
            this.getData(1);
        },
        methods: {
            getData(page) {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('coupon.slide-show.search') !!}',{page:page}).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.data;
                        
                        this.current_page=response.data.data.current_page;
                        this.total=response.data.data.total;
                        this.per_page=response.data.data.per_page;

                        
                        loading.close();

                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                    loading.close();

                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                });
            },
            // 编辑排序
            editTitle(index, type) {
                let that = this;
                that.change_sort = "";
                that.change_sort = that.list[index].sort;
            },
            // 确认修改排序
            confirmChangeSort(id) {
                let that = this;
                if (!(/^\d+$/.test(that.change_sort))) {
                    that.$message.error('请输入正确数字');
                    return false;
                }
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                let json = {id: id, sort: that.change_sort};
                that.$http.post("{!! yzWebFullUrl('coupon.slide-show.edit-sort') !!}", json).then(response => {
                    console.log(response);
                    if (response.data.result == 1) {
                        that.$message.success('操作成功！');
                        // that.$refs.search_form.click();
                        if (document.all) {
                            document.getElementById('app').click();
                        } else {// 其它浏览器
                            var e = document.createEvent('MouseEvents')
                            e.initEvent('click', true, true)
                            document.getElementById('app').dispatchEvent(e)
                        }
                        that.search(1);
                    } else {
                        that.$message.error(response.data.msg);
                    }
                    loading.close();
                }), function (res) {
                    console.log(res);
                    loading.close();

                };
            },
            
            search(val) {
                this.getData(val);
            },
            addModal() {
                let link = `{!! yzWebFullUrl('coupon.slide-show.add') !!}`;
                console.log(link);
                window.location.href = link;
            },
            del(id,index) {
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.slide-show.del') !!}',{id:id}).then(function (response) {
                            if (response.data.result) {
                                this.$message({type: 'success',message: '删除成功!'});
                            }
                            else{
                                this.$message({type: 'error',message: response.data.msg});
                            }
                            loading.close();
                            this.search(this.current_page)
                        },function (response) {
                            this.$message({type: 'error',message: response.data.msg});
                            loading.close();
                        }
                    );
                }).catch(() => {
                    this.$message({type: 'info',message: '已取消删除'});
                });
            },
            
            handleClick(val) {
                console.log(val.name)
                if(val.name == 1) {
                    window.location.href = `{!! yzWebFullUrl('coupon.slide-show.add') !!}`;
                }
                else if(val.name == 2) {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.index') !!}`;
                }
                else if(val.name == 3) {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.log-view') !!}`;
                }
                else if(val.name == 4) {
                    window.location.href = `{!! yzWebFullUrl('coupon.share-coupon.log') !!}`;
                }
                else if(val.name == 5) {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon-use.index') !!}`;
                }
                else if(val.name == 6) {
                    window.location.href = `{!! yzWebFullUrl('coupon.slide-show') !!}`;
                }
            },
            
            
        },
    })
</script>
@endsection