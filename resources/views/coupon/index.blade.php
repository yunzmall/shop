@extends('layouts.base')
@section('title', '优惠券管理')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<style>
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
    .el-tabs__item,.is-top{font-size:16px}
    .el-tabs__active-bar { height: 3px;}
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
            <el-tab-pane label="会员优惠券" name="7"></el-tab-pane>
        </el-tabs>
        </div>
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">优惠券列表</div>
                <div class="vue-main-title-button">
                    <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addModal">新增优惠券</el-button>
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input v-model="search_form.keyword" placeholder="优惠券名称"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                    <el-form-item label="">
                        <el-select v-model="search_form.gettype" clearable placeholder="领取中心是否显示">
                            <el-option label="不显示" value="0"></el-option>
                            <el-option label="显示" value="1"></el-option>
                        </el-select>
                    </el-form-item>
                    
                    <el-form-item label="">
                        <el-select v-model="search_form.timesearchswtich" clearable placeholder="是否搜索时间">
                            <el-option label="不搜索时间" value="0"></el-option>
                            <el-option label="搜索时间" value="1"></el-option>
                        </el-select>
                    </el-form-item>
                    
                    <el-form-item label="">
                    <el-date-picker
                        v-model="times"
                        type="datetimerange"
                        value-format="timestamp"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        style="margin-left:5px;"
                        align="right">
                    </el-date-picker>
                    </el-form-item>
                    <el-form-item label="">
                        <el-button type="primary" @click="search(1)">搜索</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <div class="vue-main">
            <div class="vue-main-form">
                <el-table :data="list" style="width: 100%">
                    <el-table-column label="ID" align="center" prop="id" width="80"></el-table-column>
                    <el-table-column label="排序" align="center" prop="display_order" width="110"></el-table-column>
                    <el-table-column label="优惠券名称" align="center" prop="name"></el-table-column>
                    <el-table-column label="使用条件" align="center">
                        <template slot-scope="scope">
                            <div>
                                <div>[[scope.row.enough>0?'满'+scope.row.enough+'可用':'不限']]</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="优惠" align="center" prop="id">
                        <template slot-scope="scope">
                            <div v-if="scope.row.coupon_method==1">
                                立减[[scope.row.deduct?scope.row.deduct:'0']]元
                            </div>
                            <div v-else>
                                打[[scope.row.discount?scope.row.discount:'1']]折
                            </div>
                        </template>
                        
                    </el-table-column>
                    <el-table-column label="已使用" align="center" prop="usetotal" width="80"></el-table-column>
                    <el-table-column label="已发出" align="center" prop="gettotal" width="80"></el-table-column>
                    <el-table-column label="剩余数量" align="center" prop="id" width="80">
                        <template slot-scope="scope">
                            
                            <div v-if="scope.row.total==-1">
                                无限数量
                            </div>
                            <div v-else>
                                [[scope.row.lasttotal]]
                            </div>
                        </template>
                    </el-table-column>
                    
                    <el-table-column label="领取中心" align="center" width="80">
                        <template slot-scope="scope">
                            <div>
                                <div>[[scope.row.get_type==0?'不显示':'显示']]</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="创建时间" align="center" prop="created_at"></el-table-column>
                    
                    <el-table-column prop="refund_time" label="操作" align="center" width="300">
                        <template slot-scope="scope">
                            <!-- <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('coupon.coupon.coupon-view', array('id' => '')) }}'+[[scope.row.id]]" class="el-link-edit el-link-edit-middle">
                                编辑
                            </el-link>
                            <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('goods.comment.updated', array('id' => '')) }}'+[[scope.row.id]]" class="el-link-edit el-link-edit-middle">
                                删除
                            </el-link>
                            <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('coupon.coupon.log-view', array('id' => '')) }}'+[[scope.row.id]]" title="点击查看发放记录" class="el-link-edit el-link-edit-middle">
                                记录
                            </el-link>
                            <el-link type="warning" :underline="false" :href="'{{ yzWebUrl('coupon.send-coupon', array('id' => '')) }}'+[[scope.row.id]]" class="el-link-edit el-link-edit-end">
                                发放
                            </el-link> -->
                            <el-link title="编辑" :underline="false" :href="'{{ yzWebUrl('coupon.coupon.coupon-view', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                <i class="iconfont icon-ht_operation_edit"></i>
                            </el-link>
                            <el-link title="删除" :underline="false" @click="del(scope.row.id,scope.$index)" style="width:50px;">
                                <i class="iconfont icon-ht_operation_delete"></i>
                            </el-link>
                            <el-link title="查看发放记录" :underline="false" :href="'{{ yzWebUrl('coupon.coupon.log-view', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                <i class="iconfont icon-member-order"></i>
                            </el-link>
                            <el-link title="发放优惠券" :underline="false" :href="'{{ yzWebUrl('coupon.send-coupon', array('id' => '')) }}'+[[scope.row.id]]" style="width:50px;">
                                <i class="iconfont icon-supplier_release"></i>
                            </el-link>

                            <el-link title="添加优惠券" :underline="false" style="width:50px;">
                                <i v-if="scope.row.lasttotal > 0" class="el-icon-s-ticket" @click="couponAddCount(scope.row.id)"></i>
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
                activeName:'2',
                list:[],
                change_sort:'',
                times:[],
                options:[
                    {id:0,name:'全部评价类型'},
                    {id:1,name:'真实评价'},
                    {id:2,name:'模拟评价'},
                ],
                search_form:{

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
        },
        methods: {
            
            getData(page) {
                console.log(this.times);
                
                let json = {
                    page:page,
                    keyword:this.search_form.keyword,
                    gettype:this.search_form.gettype || '',
                    timesearchswtich:this.search_form.timesearchswtich,
                };
                if(this.times && this.times.length>0) {
                    json.time = [];
                    json.time = {start:this.times[0]/1000,end:this.times[1]/1000}
                    // json.starttime = this.times[0]/1000;
                    // json.endtime = this.times[1]/1000
                }
                console.log(json)
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('coupon.coupon.coupon-data') !!}',json).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.list.data;
                        this.list.forEach((item,index) => {
                            if(item.goods) {
                                item.goods.title = this.escapeHTML(item.goods.title);
                            }
                        });
                        this.current_page=response.data.data.list.current_page;
                        this.total=response.data.data.list.total;
                        this.per_page=response.data.data.list.per_page;
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
                let link = `{!! yzWebFullUrl('coupon.coupon.coupon-view') !!}`;
                console.log(link);
                window.location.href = link;
            },
            del(id,index) {
                console.log(id,index)
                this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('coupon.coupon.destory') !!}',{id:id}).then(function (response) {
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
            handleClick(val) {
                console.log(val.name)
                if(val.name == 1) {
                    window.location.href = `{!! yzWebFullUrl('coupon.base-set.see') !!}`;
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
                else if(val.name == 7) {
                    window.location.href = `{!! yzWebFullUrl('coupon.member-coupon.index') !!}`;
                }
            },
            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },
            couponAddCount(id) {
                const that = this
                this.$prompt('输入增加的优惠劵数量', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputPattern: /^[0-9]*$/,
                    inputErrorMessage: '请输入整型'
                }).then(({ value }) => {
                    let params = {
                        id: id,
                        total: value
                    }
                    that.$http.post("{!! yzWebFullUrl('coupon.coupon.add-coupon-count') !!}", params).then(response => {
                        if (response.data.result === 1) {
                            this.$message({
                                type: 'success',
                                message: '成功添加优惠券: ' + value + '张'
                            });
                            that.search(1);
                        }else{
                            this.$message({
                                type: 'fail',
                                message: '添加失败, 请重试'
                            });
                        }
                    }), function (res) {
                        console.log(res);
                    };

                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消优惠券添加'
                    });
                });
            }
            
        },
    })
</script>
@endsection