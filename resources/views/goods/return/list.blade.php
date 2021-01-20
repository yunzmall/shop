@extends('layouts.base')

@section('content')
@section('title', trans('退货地址列表'))
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-main">
            <div class="vue-main-title">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">退货地址列表</div>
                <div class="vue-main-title-button">
                    <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addBrand">添加退货地址列表</el-button>
                </div>
            </div>
            <div class="vue-main-form">
                <el-table :data="list" style="width: 100%">
                    <el-table-column prop="id" label="ID"></el-table-column>
                    <el-table-column prop="address_name" label="退货地址名称"></el-table-column>
                    <el-table-column prop="contact" label="联系人"></el-table-column>
                    <el-table-column prop="mobile" label="联系方式"></el-table-column>
                    <el-table-column prop="address" label="地址"></el-table-column>
                    <el-table-column label="默认退货地址" align="center">
                        <template slot-scope="scope">
                            <div>
                                <el-tooltip placement="top">
                                    <div slot="content">[[scope.row.is_default==1?'是':'否']]</div>
                                    <el-switch v-model="scope.row.is_default" @change="changeStatus(scope.row.id,scope.row.is_default)" :active-value="1" :inactive-value="0"></el-switch>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="refund_time" label="操作" align="center">
                        <template slot-scope="scope">
                            <!-- <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('goods.return-address.edit-view', array('id' => '')) }}'+[[scope.row.id]]" class="el-link-edit el-link-edit-middle">
                                修改
                            </el-link>
                            <el-link type="warning" :underline="false" @click="del(scope.row.id,scope.$index)" class="el-link-edit el-link-edit-end">
                                删除
                            </el-link> -->
                            <el-link title="修改" :underline="false" :href="'{{ yzWebUrl('goods.return-address.edit-view', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
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
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            return {
                list:[],
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
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.return-address.return-address-list') !!}',{page:page}).then(function(response) {
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

            changeStatus(id,status){
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.return-address.is-default') !!}',{id:id ,status,status}).then(function(response) {
                    console.log(response.data.msg,5555)
                    if (response.data.result) {
                        this.getData(1);
                        this.$message({
                            message: response.data.msg,
                            type: 'success'
                        });

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
            addBrand() {
                let link = `{!! yzWebFullUrl('goods.return-address.edit-view') !!}`;
                console.log(link);
                window.location.href = link;
            },
            del(id,index) {
                console.log(id,index)
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.return-address.delete') !!}',{id:id}).then(function (response) {
                            console.log(response.data);
                            if (response.data.result) {
                                this.list.splice(index,1);
                                this.$message({type: 'success',message: '删除成功!'});
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

