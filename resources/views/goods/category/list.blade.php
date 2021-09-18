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
                <ul style="margin-bottom:10px;">
                    <li>
                        一键显示：
                        <el-button size="mini" @click="patchDisplayCategory(1)">
                            开启
                        </el-button>
                        <el-button size="mini" @click="patchDisplayCategory(0)">
                            关闭
                        </el-button>
                    </li>
                    <li style="display:block;margin-top:5px;">
                         一键推荐：
                        <el-button size="mini" @click="patchRecommendCategory(1)">
                            开启
                        </el-button>
                        <el-button size="mini" @click="patchRecommendCategory(0)">
                            关闭
                        </el-button>
                    </el-switch>
                    </li>
                </ul>
                <el-table
                    v-if="show_table"
                    :data="list"
                    row-key="id"
                    ref="table"
                    default-expand-all
                    :tree-props="{children: 'has_many_children'}"
                    style="width: 100%"
                    row-key="id" border @select="select" @select-all="selectAll"
                >
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop="name" label="分类名称"></el-table-column>
                    <el-table-column label="显示" width="100">
                        <template slot-scope="scope">
                            <el-switch v-model="scope.row.enabled" @change="displayCategory(scope.row)"></el-switch>
                        </template>
                    </el-table-column>
                    <el-table-column label="推荐" width="100">
                        <template slot-scope="scope">
                            <el-switch v-model="scope.row.is_home" @change="recommendCateogry(scope.row)"></el-switch>
                        </template>
                    </el-table-column>
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
                selectedCategoies:[],
                batchDisplay:false,
                batchRecommend:false
            }
        },
        created() {

        },
        mounted() {
            this.getData(1);
        },
        methods: {
            setChildren(children, type) {
                // 编辑多个子层级
                children.map((j) => {
                    this.toggleSelection(j, type)
                    if (j.has_many_children) {
                    this.setChildren(j.has_many_children, type)
                    }
                })
            },
            // 选中父节点时，子节点一起选中取消
            select(selection, row) {
                this.selectedCategoies=selection;
                if (
                    selection.some((el) => {
                    return row.id === el.id
                    })
                ) {
                    if (row.has_many_children) {
                    // 解决子组件没有被勾选到
                    this.setChildren(row.has_many_children, true)
                    }
                } else {
                    if (row.has_many_children) {
                    this.setChildren(row.has_many_children, false)
                    }
                }
            },
            toggleSelection(row, select) {
            if (row) {
                this.$nextTick(() => {
                    this.$refs.table && this.$refs.table.toggleRowSelection(row, select)
                })
            }
            },
            // 选择全部
            selectAll(selection) {
                this.selectedCategoies=selection;
                // tabledata第一层只要有在selection里面就是全选
                const isSelect = selection.some((el) => {
                    const tableDataIds = this.list.map((j) => j.id)
                    return tableDataIds.includes(el.id)
                })
                // tableDate第一层只要有不在selection里面就是全不选
                const isCancel = !this.list.every((el) => {
                    const selectIds = selection.map((j) => j.id)
                    return selectIds.includes(el.id)
                })
                if (isSelect) {
                    selection.map((el) => {
                        if (el.has_many_children) {
                            // 解决子组件没有被勾选到
                            this.setChildren(el.has_many_children, true)
                        }
                    })
                }
                if (isCancel) {
                    this.list.map((el) => {
                        if (el.has_many_children) {
                            // 解决子组件没有被勾选到
                            this.setChildren(el.has_many_children, false)
                        }
                    })
                }
            },
            recommendCateogry(row){
                this.$http.post("{!! yzWebFullUrl('goods.category.batch-recommend') !!}",{
                    ids:[row.id],
                    is_home:row.is_home?1:0
                }).then(res=>{
                    if(res.result==0){
                        this.$toast(res.msg);
                        row.is_home=!Boolean(row.is_home);
                        return;
                    }
                })
            },
            patchRecommendCategory(flag){
                let ids=[];
                for (const item of this.selectedCategoies) {
                    if (item.has_many_children && item.has_many_children.length > 0){
                        for (const itemChildren of item.has_many_children) {
                            ids.push(itemChildren.id)
                            if (itemChildren.has_many_children && itemChildren.has_many_children.length > 0){
                                for (const itemChildrenLower of itemChildren.has_many_children) {
                                    ids.push(itemChildrenLower.id)
                                }
                            }
                        }
                    }
                    ids.push(item.id)
                }
                this.$http.post("{!! yzWebFullUrl('goods.category.batch-recommend') !!}",{
                    ids,
                    is_home:flag
                }).then(res=>{
                    if(res.result==0){
                        this.$toast(res.msg);
                        return;
                    }
                    for (const item of this.selectedCategoies) {
                        item.is_home=Boolean(flag);
                        if (item.has_many_children && item.has_many_children.length > 0){
                            for (const itemChildren of item.has_many_children) {
                                itemChildren.is_home=Boolean(flag)
                                if (itemChildren.has_many_children && itemChildren.has_many_children.length > 0){
                                    for (const itemChildrenLower of itemChildren.has_many_children) {
                                        itemChildrenLower.is_home=Boolean(flag)
                                    }
                                }
                            }
                        }
                    }
                })
            },
            displayCategory(row){
                this.$http.post("{!! yzWebFullUrl('goods.category.batch-display') !!}",{
                    ids:[row.id],
                    enabled:row.enabled?1:0
                }).then(res=>{
                    if(res.result==0){
                        this.$toast(res.msg);
                        row.enabled=!Boolean(row.enabled);
                        return;
                    }
                })
            },
            patchDisplayCategory(flag){
                let ids=[];
                for (const item of this.selectedCategoies) {
                    if (item.has_many_children && item.has_many_children.length > 0){
                        for (const itemChildren of item.has_many_children) {
                            ids.push(itemChildren.id)
                            if (itemChildren.has_many_children && itemChildren.has_many_children.length > 0){
                                for (const itemChildrenLower of itemChildren.has_many_children) {
                                    ids.push(itemChildrenLower.id)
                                }
                            }
                        }
                    }
                    ids.push(item.id)
                }
                this.$http.post("{!! yzWebFullUrl('goods.category.batch-display') !!}",{
                    ids,
                    enabled:flag
                }).then(res=>{
                    if(res.result==0){
                        this.$toast(res.msg);
                        row.enabled=!Boolean(row.enabled);
                        return;
                    }
                    for (const item of this.selectedCategoies) {
                        item.enabled=Boolean(flag);
                        if (item.has_many_children && item.has_many_children.length > 0) {
                            for (const itemChildren of item.has_many_children) {
                                itemChildren.enabled=Boolean(flag);
                                if (itemChildren.has_many_children && itemChildren.has_many_children.length > 0) {
                                    for (const itemChildrenLower of itemChildren.has_many_children) {
                                        itemChildrenLower.enabled=Boolean(flag);
                                    }
                                }
                            }
                        }
                    }
                })
            },
            getData(page) {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.show_table = false;
                this.$http.post("{!! yzWebFullUrl('goods.category.index') !!}",{page:page,keyword:this.keyword}).then(function(response) {
                    if (response.data.result) {
                        for (const item of response.data.data.data.data) {
                            if(item.has_many_children && item.has_many_children.length>0){
                                for (const subItem of item.has_many_children) {
                                    subItem['is_home']=Boolean(subItem['is_home']);
                                    subItem['enabled']=Boolean(subItem['enabled']);
                                    if(subItem.has_many_children && subItem.has_many_children.length>0){
                                        for (const subItemChildren of subItem.has_many_children) {
                                            subItemChildren['is_home']=Boolean(subItemChildren['is_home']);
                                            subItemChildren['enabled']=Boolean(subItemChildren['enabled']);
                                        }
                                    }
                                }
                            }
                            item['is_home']=Boolean(item['is_home']);
                            item['enabled']=Boolean(item['enabled']);
                        }
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
                this.$http.post("{!! yzWebFullUrl('goods.category.index') !!}",{page:val,keyword:this.keyword}).then(function(response) {
                    if (response.data.result) {
                        for (const item of response.data.data.data) {
                            for (const subItem of item.has_many_children) {
                                subItem['is_home']=Boolean(subItem['is_home']);
                                subItem['enabled']=Boolean(subItem['enabled']);
                            }
                            item['is_home']=Boolean(item['is_home']);
                            item['enabled']=Boolean(item['enabled']);
                        }
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
