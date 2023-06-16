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
                    <li style="margin-bottom:10px;">
                        服务提供：
                        <el-button size="mini" @click="openService">
                            批量编辑
                        </el-button>
                    </li>
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
                        <el-button size="mini" @click="batchDeleteCategory()">
                            批量删除
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
                                <el-link title="创建子分类" :underline="false" v-if="scope.row.has_many_children && thirdShow && scope.row.level !== 3" @click="addChild(scope.row)" style="text-align: left;display: inline-block;font-size:26px;width:50px;">
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
        <el-dialog title="服务提供" :visible.sync="dialogTableVisible" center width="730px">
            <div style="overflow:auto">
                <el-form ref="service_form" style="width: 100%;height:auto;overflow:auto">
                    <el-form-item label="是否自动上下架">
                        <el-radio v-model="service_form.is_automatic" :label="1">是</el-radio>
                        <el-radio v-model="service_form.is_automatic" :label="0">否</el-radio>
                    </el-form-item>
                    <el-form-item label="时间方式">
                        <el-radio v-model="service_form.time_type" :label="0">固定</el-radio>
                        <el-radio v-model="service_form.time_type" :label="1">循环</el-radio>
                        <span style="display: flex">
                            <span class="tip" style="margin-right: 20px">固定：在设置的时间商品自动上下架时间</span>
                            <span class="tip">循环：在循环日期内商品每天在设置的时间点自动循环上下架</span>
                        </span>
                    </el-form-item>
                    <el-form-item label="上下架时间" v-if="service_form.time_type==0">
                        <el-date-picker
                                v-model="service_form.shelves_time"
                                type="datetimerange"
                                value-format="timestamp"
                                align="right"
                                unlink-panels
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                :picker-options="pickerOptions">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="循环日期" v-if="service_form.time_type==1">
                        <el-date-picker
                                v-model="service_form.loop_date"
                                type="daterange"
                                value-format="timestamp"
                                align="right"
                                unlink-panels
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                :picker-options="pickerOptions"
                        >
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="上架时间" v-if="service_form.time_type==1">
                        <el-time-select
                                v-model="service_form.loop_time_up"
                                value-format="timestamp"
                                :picker-options="{
                                    start: '00:00',
                                    step: '00:05',
                                    end: '24:00'
                                }"
                                placeholder="选择时间">
                        </el-time-select>
                        <span style="margin-left: 15px;margin-right: 8px">下架时间</span>
                        <el-time-select
                                v-model="service_form.loop_time_down"
                                value-format="timestamp"
                                :picker-options="{
                                    start: '00:00',
                                    step: '00:05',
                                    end: '24:00',
                                    minTime: service_form.loop_time_up
                                }"
                                placeholder="选择时间">
                        </el-time-select>
                    </el-form-item>
                    <el-form-item label="库存自动刷新" v-if="service_form.time_type==1">
                        <el-switch v-model="service_form.auth_refresh_stock" active-color="#13ce66" inactive-color="#ff4949" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">开启后，循环日期期间，每日重新上架时，库存商品数自动刷新为原始库存数</div>
                    </el-form-item>
                    <el-form-item label="原始库存" v-if="service_form.time_type==1">
                        <el-input v-model="service_form.original_stock" style="width:30%;"></el-input>
                    </el-form-item>
                    <span style="">
                        <el-button type="primary" @click="serviceSubmit">确 认</el-button>
                        <el-button @click="closeService">取 消</el-button>
                    </span>
                </el-form>
            </div>
        </el-dialog>
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
                batchRecommend:false,
                // 保存页码状态
                page:1,
                dialogTableVisible:false,
                service_form: {
                    is_automatic: 0,
                    time_type: 1,
                    auth_refresh_stock: 1,
                    shelves_time:[],
                    loop_date:[],
                },
                pickerOptions: {
                    shortcuts: [{
                        text: "最近一周",
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit("pick", [start, end]);
                        }
                    }, {
                        text: "最近一个月",
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                            picker.$emit("pick", [start, end]);
                        }
                    }, {
                        text: "最近三个月",
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                            picker.$emit("pick", [start, end]);
                        }
                    }]
                },
            }
        },
        created() {

        },
        mounted() {
            if(this.getParam('page')){
                this.getData(this.getParam('page'));
            }else{
                this.getData(this.page);
            }

        },
        methods: {
            getParam(name) {
                var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                var r = window.location.search.substr(1).match(reg);
                if (r != null) return unescape(r[2]);
                return null;
            },
            beforeunloadFn (e) {
                this.$http.post(`{!! yzWebFullUrl('goods.category.get-page') !!}`,{page:1}).then(response => {
                    if (response.data.result) {
                        console.log(response.data.msg);
                    } else {
                        console.log(response.data.msg);
                    }
                },response => {
                    console.log(response);
                });
            },
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
            batchDeleteCategory(){
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
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('goods.category.batchDeleteCategory') !!}',{ids}).then(function (response) {
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
            serviceSubmit(){
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
                if (this.service_form.time_type==0) {
                    console.log(this.service_form)
                    if (!this.service_form.shelves_time[0] || !this.service_form.shelves_time[1]) {
                        this.$message({message: '上下架时间不能为空', type: 'error'});return;
                    }
                    this.service_form.on_shelf_time = this.service_form.shelves_time[0] / 1000;
                    this.service_form.lower_shelf_time = this.service_form.shelves_time[1] / 1000;
                }
                if (this.service_form.time_type==1) {
                    if (!this.service_form.loop_date[0] || !this.service_form.loop_date[1]) {
                        this.$message({message: '循环时间不能为空', type: 'error'});return;
                    }
                    this.service_form.loop_date_start = this.service_form.loop_date[0] / 1000;
                    this.service_form.loop_date_end = this.service_form.loop_date[1] / 1000;
                }
                if (this.service_form.time_type==1) {
                    if (!this.service_form.loop_time_up || !this.service_form.loop_time_down) {
                        this.$message({message: '循环上下架时间不能为空', type: 'error'});return;
                    }
                }
                let json = {
                    ids:ids,
                    service_form:this.service_form,
                };
                this.$http.post("{!! yzWebFullUrl('goods.category.batchService') !!}",json).then(res=>{
                    if (res.data.result) {
                        this.$message({message: res.data.msg, type: 'success'});
                        location.reload();
                    } else {
                        this.$message({message: res.data.msg, type: 'error'});
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
                this.page = val
                if(this.keyword == "") {
                    this.getData(val);
                    return;
                }
                this.show_table = false;
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post("{!! yzWebFullUrl('goods.category.index') !!}",{page:val,keyword:this.keyword}).then(function(response) {
                    if (response.data.result) {
                        for (const item of response.data.data.data.data) {
                            if (item.has_many_children && item.has_many_children.length>0) {
                                for (const subItem of item.has_many_children) {
                                    subItem['is_home']=Boolean(subItem['is_home']);
                                    subItem['enabled']=Boolean(subItem['enabled']);
                                }
                            }
                            item['is_home']=Boolean(item['is_home']);
                            item['enabled']=Boolean(item['enabled']);
                        }
                        this.list = response.data.data.data.data;
                        this.current_page=response.data.data.current_page;
                        this.total=response.data.data.data.total;
                        this.per_page=response.data.data.data.per_page;
                        this.show_table = true;
                        this.thirdShow=response.data.data.thirdShow;

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
                let link = `{!! yzWebFullUrl('goods.category.category-info') !!}`+`&id=`+item.id+`&page=`+this.page;
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
            openService() {
                this.dialogTableVisible = true;
            },
            closeService() {
                this.dialogTableVisible = false;
            },
        },
    })
</script>
@endsection
