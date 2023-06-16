@extends('layouts.base')
@section('title', '商品标签管理')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css?time='.time())}}" />
    <!-- 引入样式 -->
    <!-- 引入组件库 -->
    <link rel="stylesheet" href="{{static_url('../resources/views/goods/assets/css/common.css?time='.time())}}">
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <style>
        .vue-main-set{

            background-color: red;
        }
    </style>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-head goods-page_header">
            <div class="goods-page_header-buttons">
                <el-button type="text" :class="currentShowPage==1?'goods-page_header-current-button':''" @click="currentShowPage=1">商品标签列表</el-button>
                <el-button type="text" :class="currentShowPage==2?'goods-page_header-current-button':''" @click="currentShowPage=2">商品标签设置</el-button>
            </div>
        </div>
        <div v-if="currentShowPage==1">
            <div class="vue-main">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">商品标签组列表</div>
                    <div class="vue-main-title-button">
                        <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addModal">添加标签组</el-button>
                    </div>
                </div>
                <div class="vue-main-form">
                    <el-table :data="list" style="width: 100%">
                        <el-table-column label="ID" align="center" prop="id"></el-table-column>
                        <el-table-column label="标签组名称" align="center" prop="name"></el-table-column>
                        <el-table-column label="拥有标签个数" align="center" prop="filter_num"></el-table-column>
                        <el-table-column label="是否启用" align="center">
                            <template slot-scope="scope">
                                <div>
                                    [[scope.row.is_show==1?'不启用':'启用']]
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="创建时间" align="center" prop="created_at"></el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">

                                <el-link title="查看标签" :underline="false" :href="'{{ yzWebUrl('filtering.filtering.filter-value', array('parent_id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                    <i class="iconfont icon-ht_operation_tag"></i>
                                </el-link>
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
        <div v-if="currentShowPage==2">
            <div class="vue-main">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">商品标签设置</div>
                </div>
                <el-row :gutter="20">
                    <el-col :span="10" :offset="5">
                        <el-form ref="form">
                            <el-form-item label="开启前端搜索标签">
                                <el-switch v-model="set.is_search_show"
                                           active-color="#13ce66"
                                           :active-value="1"
                                           :inactive-value="0"
                                ></el-switch>
                            </el-form-item>
                            <el-form-item size="large">
                                <el-button type="primary" @click="saveSet()">保存</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </el-row>
            </div>
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
                currentShowPage:1,
                list:[],
                change_sort:'',
                times:[],
                
                search_form:{

                },
                set:{
                    is_search_show:0,
                },
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
            this.getSet();
        },
        methods: {
            getData(page) {
                let that = this;
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('filtering.filtering.filtering-data') !!}',{page:page}).then(function(response) {
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
                let link = `{!! yzWebFullUrl('filtering.filtering.edit-view') !!}`+`&partent_id=0`;
                window.location.href = link;
            },
            gotoEdit(id) {
                let link = `{!! yzWebFullUrl('filtering.filtering.edit-view') !!}`+`&partent_id=0&id=`+id;
                window.location.href = link;
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
            saveSet(){
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('filtering.filtering.saveSet') !!}',this.set).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});
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
            },
            getSet(){
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('filtering.filtering.getSet') !!}').then(function (response) {
                        if (response.data.result) {
                            this.set = response.data.data;
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
            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },
            
        },
    })
</script>
@endsection