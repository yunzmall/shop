@extends('layouts.base')
@section('title', '审核列表')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<style>
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}
    .el-tabs__item,.is-top{font-size:16px}
    .el-tabs__active-bar { height: 3px;}
    .description .el-form-item__label{line-height:24px}
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-nav" style="margin-bottom:15px">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="基础设置" name="1"></el-tab-pane>
                <el-tab-pane label="评价列表" name="2"></el-tab-pane>
                <el-tab-pane label="审核列表" name="3"></el-tab-pane>
            </el-tabs>
        </div>
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">评论审核</div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input v-model="search_form.keyword" placeholder="商品标题"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select v-model="search_form.searchtime" clearable placeholder="是否搜索时间">
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
                    <el-table-column label="商品信息">
                        <template slot-scope="scope">
                            <div v-if="scope.row.goods" style="display:flex;align-items: center">
                                <img v-if="scope.row.goods.thumb" :src="scope.row.goods.thumb" onerror="this.src='{{static_url("resource/images/nopic.jpg")}}'; this.title='图片未找到.'" style="width:50px;height:50px"></img>
                                <div style="margin-left:10px">[[scope.row.goods.title]]</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="评价者">
                        <template slot-scope="scope">
                            <div style="display:flex;align-items: center">
                                <img v-if="scope.row.head_img_url" :src="scope.row.head_img_url" onerror="this.src='{{static_url("resource/images/nopic.jpg")}}'; this.title='图片未找到.'" style="width:50px;height:50px"></img>
                                <div style="margin-left:10px">[[scope.row.nick_name]]</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="评分等级" align="center">
                        <template slot-scope="scope">
                            <div>
                                <el-rate v-model="scope.row.level" disabled show-score></el-rate>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="时间" align="center" prop="created_at"></el-table-column>
                    
                    <el-table-column prop="refund_time" label="操作" align="center" width="320">
                        <template slot-scope="scope">
                            <el-link v-if="scope.row.audit_status==2" title="查看详情" :underline="false" :href="'{{ yzWebUrl('goods.comment.reply-view', array('page_type' => 'audit','id' => '')) }}'+[[scope.row.id]]" style="text-align: center;width:50px">
                                <i class="iconfont icon-all_watchroot"></i>
                            </el-link>
                            <el-link v-if="scope.row.audit_status==2" title="通过审核" :underline="false" @click="pass(scope.row.id)" style="text-align: center;width:50px">
                                <i class="iconfont icon-jk_record"></i>
                            </el-link>
{{--                            <el-link v-if="scope.row.status==0" title="驳回审核" :underline="false" @click="refundReject(scope.row.id,-1)" style="text-align: center;width:50px">--}}
{{--                                <i class="iconfont icon-ht_operation_reject"></i>--}}
{{--                            </el-link>--}}

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
                activeName:'3',
            }
        },
        created() {

        },
        mounted() {
            this.getData(1);
        },
        methods: {
            handleClick(val) {
                console.log(val.name)
                if(val.name == 1) {
                    window.location.href = `{!! yzWebFullUrl('goods.comment.index') !!}`;
                }
                else if(val.name == 2) {
                    window.location.href = `{!! yzWebFullUrl('goods.comment.list') !!}`;
                }
                else if(val.name == 3) {
                    window.location.href = `{!! yzWebFullUrl('goods.comment.audit') !!}`;
                }
            },
            getData(page) {
                console.log(this.times);
                
                let json = {
                    keyword:this.search_form.keyword,
                    fade:this.search_form.fade,
                    searchtime:this.search_form.searchtime,
                };
                if(this.times && this.times.length>0) {
                    json.starttime = this.times[0]/1000;
                    json.endtime = this.times[1]/1000
                }

                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.comment.comment-data') !!}',{page:page,search:json,type:'audit'}).then(function(response) {
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

            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },

            pass(id) {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.comment.changeAuditStatus') !!}',{comment_id:id}).then(function (response) {
                        if (response.data.result){
                            this.$message.success("操作成功");
                            this.search(this.current_page);
                        }
                        else {
                            this.$message.error( response.data.msg );
                        }
                        loading.close();
                    },function (response) {
                        this.$message.error(response.data.msg);
                        loading.close();
                    }
                );
            },

            
        },
    })
</script>
@endsection