@extends('layouts.base')
@section('title', '优惠券使用记录')
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
        </el-tabs>
        </div>
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">优惠券使用记录</div>
                <div class="vue-main-title-button">
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input v-model="search_form.coupon_name" placeholder="优惠券名称"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.member" placeholder="会员ID/昵称/姓名/手机号"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select v-model="search_form.use_type" clearable placeholder="使用类型">
                            <el-option v-for="(item,index) in use_type" :key="index" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="">
                        <el-select v-model="search_form.is_time" clearable placeholder="是否搜索时间">
                            <el-option label="不搜索时间" value="0"></el-option>
                            <el-option label="搜索时间" value="1"></el-option>
                        </el-select>
                    </el-form-item>
                    
                    <el-form-item label="">
                    <el-date-picker
                        v-model="times"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
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
                    <el-form-item label="">
                        <el-button type="success" @click="export1()">导出</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <div class="vue-main">
            <div class="vue-main-form">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">使用列表</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <el-table :data="list" style="width: 100%">
                    <el-table-column label="ID" align="center" prop="id" width="80"></el-table-column>
                    <el-table-column label="使用时间" align="center" prop="created_at"></el-table-column>
                    <el-table-column label="优惠券名称" align="center" prop="has_one_coupon.name">
                        <template slot-scope="scope">
                            <div v-if="scope.row.has_one_coupon">
                                <div>[[scope.row.has_one_coupon.name]]</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="会员" align="center" width="150">
                        <template slot-scope="scope">
                            <div v-if="scope.row.belongs_to_member">
                                <el-image v-if="scope.row.belongs_to_member&&scope.row.belongs_to_member.avatar" :src="scope.row.belongs_to_member.avatar" style="width:50px;height:50px"></el-image>
                                <div v-if="scope.row.belongs_to_member&&scope.row.belongs_to_member.nickname">[[scope.row.belongs_to_member.nickname]]</div>
                            </div>
                            <div v-else>
                                未更新
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="使用类型" align="center" width="150">
                        <template slot-scope="scope">
                            <div>
                                <div>[[scope.row.type_name]]</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="详情" align="center" prop="detail"></el-table-column>
                    
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
    let use_type = [];
    let obj = {!! $use_type?:'{}' !!};
    for(let i in obj) {
        use_type.push({id:i,name:obj[i]})
    }
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            return {
                activeName:'5',
                list:[],
                times:[],
                
                search_form:{

                },
                use_type:use_type,

                rules: {},
                current_page:1,
                total:0,
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
                let that = this;
                let json = {
                    coupon_name:this.search_form.coupon_name,
                    member:this.search_form.member,
                    use_type:this.search_form.use_type,
                    is_time:this.search_form.is_time,
                };
                if(this.times && this.times.length>0) {
                    json.time = [];
                    json.time = {start:this.times[0],end:this.times[1]}
                }
                console.log(json)
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('coupon.coupon-use.log') !!}',{page:page,search:json}).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.list.data;
                        
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
            export1(){
                let url = "{!! yzWebFullUrl('coupon.coupon-use.export') !!}";
                for(let i in this.search_form) {
                    if(this.search_form[i]) {
                        url += "&search["+i+"]="+this.search_form[i]
                    }
                }
                if (this.search_form.is_time == 1) {
                    url += "&search[time_start]="+this.times[0]/1000+"&search[time_end]="+this.times[1]/1000
                }
                console.log(url)

                window.location.href = url;


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
            },
            
            
        },
    })
</script>
@endsection