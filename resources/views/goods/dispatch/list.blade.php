@extends('layouts.base')
@section('title', '配送模板管理')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<style>
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-main">
            <div class="vue-main-title">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">配送模板列表</div>
                <div class="vue-main-title-button">
                    <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addModal">新增配送模板</el-button>
                </div>
            </div>
            <div class="vue-main-form">
                <el-table :data="list" style="width: 100%">
                    <el-table-column prop="id" label="ID" align="center" width="90"></el-table-column>
                    <el-table-column label="显示顺序" align="center">
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
                        [[scope.row.display_order]]
                        </template>
                    </el-table-column>
                    <el-table-column prop="dispatch_name" label="配送方式名称" align="center"></el-table-column>
                    <el-table-column prop="id" label="计费方式" align="center">
                        <template slot-scope="scope">
                            <div>
                                [[scope.row.calculate_type==1?'按件计费':'按重量计费']]
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="id" label="首重(首件)价格" align="center">
                        <template slot-scope="scope">
                            <div>
                                [[scope.row.calculate_type==1?scope.row.first_piece_price:scope.row.first_weight_price]]
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="id" label="续重(续件)价格" align="center">
                        <template slot-scope="scope">
                            <div>
                                [[scope.row.calculate_type==1?scope.row.another_piece_price:scope.row.another_weight_price]]
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="状态" align="center">
                        <template slot-scope="scope">
                            <div>
                                <el-tooltip placement="top">
                                    <div slot="content">[[scope.row.enabled==1?'显示':'隐藏']]</div>
                                    <el-switch v-model="scope.row.enabled" @change="changeStatus(scope.row.id,scope.$index,'enabled',scope.row.enabled)" :active-value="1" :inactive-value="0"></el-switch>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="默认" align="center">
                        <template slot-scope="scope">
                            <div>
                                <el-tooltip placement="top">
                                    <div slot="content">[[scope.row.is_default==1?'是':'否']]</div>
                                    <el-switch v-model="scope.row.is_default" @change="changeStatus(scope.row.id,scope.$index,'is_default',scope.row.is_default)" :active-value="1" :inactive-value="0"></el-switch>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>

                    
                    <el-table-column prop="refund_time" label="操作" align="center" width="250">
                        <template slot-scope="scope">
                            <!-- <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('goods.dispatch.edit-save', array('id' => '')) }}'+[[scope.row.id]]" class="el-link-edit el-link-edit-middle">
                                编辑
                            </el-link>
                            <el-link type="warning" :underline="false" @click="del(scope.row.id,scope.$index)" class="el-link-edit el-link-edit-end">
                                删除
                            </el-link> -->
                            <el-link title="编辑模板" :underline="false"  :href="'{{ yzWebUrl('goods.dispatch.edit-save', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                <i class="iconfont icon-ht_operation_edit"></i>
                            </el-link>
                            <el-link title="删除模板" :underline="false" @click="del(scope.row.id,scope.$index)" style="width:50px;">
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
                change_sort:'',

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
                this.$http.post('{!! yzWebFullUrl('goods.dispatch.dispatch-data') !!}',{page:page}).then(function(response) {
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
            
            search(val) {
                this.getData(val);
            },
            // 添加新品牌
            addModal() {
                let link = `{!! yzWebFullUrl('goods.dispatch.edit-save') !!}`;
                console.log(link);
                window.location.href = link;
            },
            del(id,index) {
                console.log(id,index)
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.dispatch.delete') !!}',{id:id}).then(function (response) {
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

            // 编辑排序
            editTitle(index) {
                let that = this;
                that.change_sort = "";
                that.change_sort = that.list[index].display_order;
            },
            confirmChangeSort(id) {
                let that = this;
                if (!(/^\d+$/.test(that.change_sort))) {
                    that.$message.error('请输入正确数字');
                    return false;
                }
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                let json = {id: id, sort: that.change_sort};
                that.$http.post("{!! yzWebFullUrl('goods.dispatch.sort-v2') !!}", json).then(response => {
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
                        that.search(this.current_page);
                    } else {
                        that.$message.error(response.data.msg);
                    }
                    loading.close();

                }), function (res) {
                    console.log(res);
                    loading.close();

                };
            },
            // 快速修改
            changeStatus(id,index,type,value) {
                let that = this;
                let json = {id: id, type: type, status: value};
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                that.$http.post("{!! yzWebFullUrl('goods.dispatch.quick-edit') !!}", json).then(response => {
                    console.log(response);
                    if (response.data.result == 1) {
                        that.$message.success('操作成功！');
                    } else {
                        that.$message.error(response.data.msg);
                        that.list[index][type] == 1 ? 0 : 1;
                    }
                    that.search(this.current_page);
                    loading.close();
                }), function (res) {
                    console.log(res);
                    loading.close();

                };
            },
            
        },
    })
</script>
@endsection