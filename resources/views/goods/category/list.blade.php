@extends('layouts.base')
@section('title', '商品分类管理')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">商品分类</div>
                <div class="vue-main-title-button">
                    <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addFirst">添加一级分类</el-button>
                </div>
            </div>
            <div class="vue-search">
                <el-input v-model="keyword" style="width:40%"></el-input>
                <el-button type="primary" @click="search(1)">搜索</el-button>
                <el-link  class="el-link-assist" :underline="false" @click="openAll">
                    全部展开
                </el-link>
                <el-link  class="el-link-assist" :underline="false" @click="closeAll" style="margin-left:20px;">
                    全部折叠
                </el-link>
            </div>
        </div>
        
        <div class="vue-main">
            
            
            <div class="vue-main-form">
                <el-table
                    v-if="show_table"
                    :data="list" 
                    row-key="id"
                    ref="table"
                    default-expand-all
                    :tree-props="{children: 'has_many_children'}"
                    style="width: 100%"
                >
                    <el-table-column prop="name" label="分类名称"></el-table-column>
                    <el-table-column prop="refund_time" label="操作" align="left" width="400">
                        <template slot-scope="scope">
                            <div style="text-align:left">
                                <el-link title="创建子分类" :underline="false" v-if="scope.row.has_many_children && thirdShow" @click="addChild(scope.row)" style="text-align: left;display: inline-block;font-size:26px;width:50px;">
                                    <i class="iconfont icon-ht_operation_add"></i>
                                </el-link>
                                <el-link title="创建子分类" :underline="false" v-else-if="!thirdShow &&scope.row.parent_id==0" @click="addChild(scope.row)" style="text-align: left;display: inline-block;font-size:26px;width:50px;">
                                    <i class="iconfont icon-ht_operation_add"></i>
                                </el-link>
                                <el-link title="编辑分类" :underline="false" @click="editChild(scope.row)" style="text-align: left;display: inline-block;font-size:26px;width:50px;">
                                    <i class="iconfont icon-ht_operation_edit"></i>
                                </el-link>
                                <el-link title="删除分类" :underline="false" @click="del(scope.row.id,scope.$index)" style="text-align: left;display: inline-block;font-size:26px;width:50px;">
                                    <i class="iconfont icon-ht_operation_delete"></i>
                                </el-link>
                            </div>
                            
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>
        <!-- 分页 -->
        <div class="vue-page">
            <el-row v-if="total>0">
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
                list:[],
                keyword:'',
                rules: {},
                thirdShow:true,
                show_table:true,
                current_page:1,
                total:1,
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
                this.show_table = false;
                this.$http.post('{!! yzWebFullUrl('goods.category.index') !!}',{page:page,keyword:this.keyword}).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.data.data;
                        this.current_page=response.data.data.data.current_page;
                        this.total=response.data.data.data.total;
                        this.per_page=response.data.data.data.per_page;
                        this.thirdShow=response.data.data.thirdShow;
                        loading.close();
                        this.show_table = true;

                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        this.show_table = true;

                    }
                    loading.close();
                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                    this.show_table = true;
                });
            },
            
            search(val) {
                if(this.keyword == "") {
                    this.getData(val);
                    return;
                }
                this.show_table = false;
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.category.index') !!}',{page:val,keyword:this.keyword}).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.data;
                        this.list = response.data.data.data;
                        this.current_page=response.data.data.current_page;
                        this.total=response.data.data.total;
                        this.per_page=response.data.data.per_page;
                        this.show_table = true;

                        this.$forceUpdate()
                        // this.thirdShow=response.data.thirdShow;
                        loading.close();

                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        this.show_table = false;

                    }
                    loading.close();

                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                    this.show_table = false;
                });
            },
            openAll() {
                this.list.forEach((item,index) => {
                    // this.$refs.table.toggleRowExpansion(item, true)
                    if(this.list[index].has_many_children && this.list[index].has_many_children.length>0) {
                        this.$refs.table.toggleRowExpansion(item, true)
                    }
                })
                
            },
            closeAll() {
                this.list.forEach((item,index) => {
                    if(this.list[index].has_many_children && this.list[index].has_many_children.length>0) {
                        this.$refs.table.toggleRowExpansion(item, false)
                    }
                })
            },
            // 添加一级分类
            addFirst() {
                let link = `{!! yzWebFullUrl('goods.category.category-info') !!}`+`&level=1`;
                window.location.href = link;
            },
            // 添加子分类
            addChild(item) {
                let link = '';
                if(item.parent_id == 0) {
                    link = `{!! yzWebFullUrl('goods.category.category-info') !!}`+`&parent_id=`+item.id+`&level=2`;
                }
                else {
                    link = `{!! yzWebFullUrl('goods.category.category-info') !!}`+`&parent_id=`+item.id+`&level=3`;
                }
                window.location.href = link;
            },
            // 编辑子分类
            editChild(item) {
                let link = `{!! yzWebFullUrl('goods.category.category-info') !!}`+`&id=`+item.id;
                window.location.href = link;
            },
            del(id,index) {
                console.log(id,index)
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.category.deleted-category') !!}',{id:id}).then(function (response) {
                            console.log(response.data);
                            if (response.data.result) {
                                // this.list.splice(index,1);
                                this.$message({type: 'success',message: '删除成功!'});
                                window.location.reload();
                            }
                            else{
                                this.$message({type: 'error',message: response.data.msg});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({type: 'error',message: response.data.msg});
                            loading.close();
                        }
                    );
                }).catch(() => {
                    this.$message({type: 'info',message: '已取消删除'});
                });
            },
            
        },
    })
</script>
@endsection
