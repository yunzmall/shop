@extends('layouts.base')
@section('title', '商品品牌管理')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-main">
            <div class="vue-main-title">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">商品品牌</div>
                <div class="vue-main-title-button">
                    <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addBrand">添加新品牌</el-button>
                </div>
            </div>

            <div class="form-list">
                <el-form :inline="true" :model="search_form" ref="search_form" style="margin-left:10px;">
                    <el-row>
                        <el-form-item label="" prop="id">
                            <el-input v-model="search_form.id" placeholder="请输入品牌ID"></el-input>
                        </el-form-item>

                        <el-form-item label="" prop="name">
                            <el-input v-model="search_form.name" placeholder="请输入品牌名称"></el-input>
                        </el-form-item>

                        <a href="#">
                            <el-button type="primary" icon="el-icon-search" @click="search()">搜索
                            </el-button>
                        </a>
                        </el-col>
                    </el-row>
                </el-form>
            </div>


            <div class="vue-main-form">
                <el-button size="mini" @click="patchRecommendBranch">批量开启</el-button>
                <el-button size="mini" @click="patchCancelRecommendBranch">批量关闭</el-button>
                <el-table :data="list" style="width: 100%"  @selection-change="selectedBranch">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop="id" label="ID" align="center" width="120"></el-table-column>
                    <el-table-column label="品牌">
                        <template slot-scope="scope">
                            <div>
                                <img v-if="scope.row.logo_url" :src="scope.row.logo_url" onerror="this.src='/addons/yun_shop/static/resource/images/nopic.jpg'; this.title='图片未找到.'" style="width:50px;height:50px"></img>
                                [[scope.row.name]]
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="推荐">
                        <template slot-scope="scope">
                            <el-switch @change="recommendBranch(scope.row)" v-model="scope.row.is_recommend"></el-switch>
                        </template>
                    </el-table-column>

                    <el-table-column prop="refund_time" label="操作" align="center" width="320">
                        <template slot-scope="scope">

                            <!-- <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('goods.brand.edit-viwe', array('id' => '')) }}'+[[scope.row.id]]" class="el-link-edit el-link-edit-middle">
                                修改
                            </el-link>
                            <el-link type="warning" :underline="false" @click="del(scope.row.id,scope.$index)" class="el-link-edit el-link-edit-end">
                                删除
                            </el-link> -->
                            <el-link title="编辑品牌" :underline="false"  :href="'{{ yzWebUrl('goods.brand.edit-viwe', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                <i class="iconfont icon-ht_operation_edit"></i>
                            </el-link>
                            <el-link title="删除品牌" :underline="false" @click="del(scope.row.id,scope.$index)" style="width:50px;">
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
                id:'',
                name:'',
                search_form:{},
                selectedBranchs:[]
            }
        },
        created() {

        },
        mounted() {
            this.getData(1);
        },
        methods: {
            selectedBranch(branchs){
                this.selectedBranchs=branchs;
            },
            patchRecommendBranch(){
                let ids=[];
                for (const item of this.selectedBranchs) {
                    ids.push(item.id);
                }
                this.$http.post("{!! yzWebFullUrl('goods.brand.batch-recommend') !!}",{
                    ids,
                    is_recommend:1
                }).then(res=>{
                    if(res.result==0){
                        this.$toast(res.msg);
                        return;
                    }
                    this.selectedBranchs.forEach(item=>{
                        item['is_recommend']=true;
                    })
                });
            },
            recommendBranch(branchItem){
                this.$http.post("{!! yzWebFullUrl('goods.brand.batch-recommend') !!}",{
                    ids:[branchItem.id],
                    is_recommend:branchItem.is_recommend?1:0
                }).then(res=>{
                    if(res.result==0){
                        branchItem.is_recommend=branchItem.is_recommend==1?0:1;
                        this.$message(res.msg);
                        return;
                    }
                });
            },
            patchCancelRecommendBranch(){
                let ids=[];
                for (const item of this.selectedBranchs) {
                    ids.push(item.id);
                }
                this.$http.post("{!! yzWebFullUrl('goods.brand.batch-recommend') !!}",{
                    ids,
                    is_recommend:0
                }).then(res=>{
                    if(res.result==0){
                        this.$toast(res.msg);
                        return;
                    }
                    this.selectedBranchs.forEach(item=>{
                        item['is_recommend']=false;
                    })
                });
            },
            getData(page,json) {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.brand.brand-data') !!}',{page:page,search:json}).then(function(response) {
                    if (response.data.result) {
                        for (const item of response.data.data.data) {
                            item['is_recommend']=Boolean(item['is_recommend']);
                        }
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
                var that = this;
                let json = {
                    id: that.search_form.id,
                    name: that.search_form.name,
                };
                console.log(json)
                this.getData(val,json);
            },
            // 添加新品牌
            addBrand() {
                let link = `{!! yzWebFullUrl('goods.brand.edit-viwe') !!}`;
                console.log(link);
                window.location.href = link;
            },
            del(id,index) {
                console.log(id,index)
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.brand.deleted-brand') !!}',{id:id}).then(function (response) {
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