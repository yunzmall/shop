@extends('layouts.base')
@section('title', '商品标签管理')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<style>
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-crumbs">
            <a @click="goParent">商品标签组列表</a> > 商品标签列表
        </div>
        <div class="vue-main">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">所属组：【<span style="color:#f00">[[parent.name]]</span>】</div>
                <div class="vue-main-title-button">
                    <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addModal">添加标签</el-button>
                </div>
            </div>
            <div class="vue-main-form">
                <el-table :data="list" style="width: 100%">
                    <el-table-column label="ID" align="center" prop="id"></el-table-column>
                    <el-table-column label="标签名称" align="center" prop="name"></el-table-column>
                    <el-table-column label="前端是否显示" align="center">
                        <template slot-scope="scope">
                            <el-switch
                                    v-model="scope.row.is_front_show"
                                    active-color="#13ce66"
                                    :active-value="1"
                                    :inactive-value="0"
                                    @change="setOpen(scope.row.id,scope.row.is_front_show)"
                            >
                            </el-switch>
                        </template>
                    </el-table-column>
                    <el-table-column prop="refund_time" label="操作" align="center" width="320">
                        <template slot-scope="scope">
                            <!-- <el-link type="warning" :underline="false" @click="gotoEdit(scope.row.id)" class="el-link-edit el-link-edit-middle">
                                编辑
                            </el-link>
                            <el-link type="warning" :underline="false" @click="del(scope.row.id,scope.$index)" class="el-link-edit el-link-edit-end">
                                删除
                            </el-link> -->
                            <el-link title="编辑标签" :underline="false" @click="gotoEdit(scope.row.id)" style="width:50px;">
                                <i class="iconfont icon-ht_operation_edit"></i>
                            </el-link>
                            <el-link title="删除" :underline="false" @click="del(scope.row.id,scope.$index)" style="width:50px;">
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
    let parent_id = {!! $parent_id?:'0' !!};
    console.log(parent_id)
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            return {
                list:[],
                parent_id:parent_id,
                parent:{},
                rules: {},
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
                let that = this;
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('filtering.filtering.filter-list') !!}',{parent_id:this.parent_id,page:page}).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.list.data;
                        this.current_page=response.data.data.list.current_page;
                        this.total=response.data.data.list.total;
                        this.per_page=response.data.data.list.per_page;
                        if(this.parent_id!=0) {
                            this.parent = response.data.data.parent;
                        }
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

            search(val) {
                this.getData(val);
            },
            // 添加新品牌
            addModal() {
                let link = `{!! yzWebFullUrl('filtering.filtering.edit-view') !!}`+`&parent_id=`+this.parent_id;
                console.log(link);
                window.location.href = link;
            },
            gotoEdit(id) {
                let link = `{!! yzWebFullUrl('filtering.filtering.edit-view') !!}`+`&parent_id=`+this.parent_id+`&id=`+id;
                window.location.href = link;
            },
            setOpen(id,show){
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('filtering.filtering.setOpen') !!}',{tag_id:id,show:show}).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});
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
            },
            del(id,index) {
                console.log(id,index)
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('filtering.filtering.del') !!}',{id:id}).then(function (response) {
                            if (response.data.result) {
                                this.list.splice(index,1);
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
            goParent() {
                window.location.href = `{!! yzWebFullUrl('filtering.filtering.index') !!}`;
            },
        },
    })
</script>
@endsection