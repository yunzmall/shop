@extends('layouts.base')
@section('title', '优惠券领取发放记录')
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
                <div class="vue-main-title-content">优惠券领取发放记录</div>
                <div class="vue-main-title-button">
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input v-model="search_form.couponname" placeholder="优惠券名称"></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input v-model="search_form.nickname" placeholder="用户昵称"></el-input>
                    </el-form-item>

                    <el-form-item label="">
                        <el-select v-model="search_form.getfrom" clearable placeholder="领取还是发放">
                            <el-option label="用户领取" value="1"></el-option>
                            <el-option label="商城发放" value="0"></el-option>
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
                        <el-button type="primary" @click="dataExport()">导出</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <div class="vue-main">
            <div class="vue-main-form">
                <el-table :data="list" style="width: 100%">
                    <el-table-column label="ID" align="center" prop="id" width="80"></el-table-column>
                    <el-table-column label="优惠券名称" align="center" prop="coupon.name"></el-table-column>
                    <el-table-column label="用户名称" align="center" prop="member.nickname"></el-table-column>
                    <el-table-column label="获取途径" align="center" width="120">
                        <template slot-scope="scope">
                            <div>
                                <span v-if="scope.row.getfrom==1">领取</span>
                                <span v-else-if="scope.row.getfrom==4">购物赠送</span>
                                <span v-else-if="scope.row.getfrom==5">会员转赠</span>
                                <span v-else-if="scope.row.getfrom==6">签到奖励</span>
                                <span v-else-if="scope.row.getfrom==0">发放</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="创建时间" align="center" prop="createtime"></el-table-column>
                    <el-table-column label="日志详情" align="center" prop="logno"></el-table-column>
                    
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
    let id = {!! $id?:'0' !!};
    console.log(id);
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            return {
                activeName:'3',
                id:id,
                list:[],
                times:[],
                
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
                let that = this;
                let json = {
                    page:page,
                    couponname:this.search_form.couponname,
                    nickname:this.search_form.nickname,
                    getfrom:this.search_form.getfrom || '',
                    timesearchswtich:this.search_form.timesearchswtich,
                };
                if(this.search_form.couponname != ''){
                    json.id = this.id
                }
                if(this.times && this.times.length>0) {
                    json.time = [];
                    json.time = {start:this.times[0]/1000,end:this.times[1]/1000}
                }
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('coupon.coupon.log') !!}',json).then(function(response) {
                    if (response.data.result) {
                        this.list = response.data.data.list.data;
                        if(that.list){
                            that.list.forEach(item => {
                                if(item.createtime!=0){
                                    item.createtime = that.timeStyle(item.createtime);//时间格式转换
                                }
                            });
                        }
                        
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
            dataExport(){
                var that = this;
                var url = "{!! yzWebFullUrl('coupon.coupon.export') !!}";
                var couponDataUrl = "{!! yzWebFullUrl('coupon.coupon.log') !!}";
                that.search_form.id = that.id
                const search_form = that.search_form;

                if (search_form.couponname) {
                    url += "&couponname="+that.search_form.couponname;
                    couponDataUrl += "&couponname="+that.search_form.couponname;
                }

                if (search_form.getfrom) {
                    url += "&getfrom="+that.search_form.getfrom;
                    couponDataUrl += "&getfrom="+that.search_form.getfrom;
                }

                if (search_form.nickname) {
                    url += "&nickname="+that.search_form.nickname;
                    couponDataUrl += "&nickname="+that.search_form.nickname;
                }

                if (search_form.timesearchswtich) {
                    url += "&timesearchswtich="+that.search_form.timesearchswtich;
                    couponDataUrl += "&timesearchswtich="+that.search_form.timesearchswtich;
                }

                if (search_form.id) {
                    url += "&id="+search_form.id;
                    couponDataUrl += "&id="+search_form.id;
                }

                if(this.times && this.times.length>0) {
                    url += "&start=" + this.times[0]/1000;
                    url += "&end=" + this.times[1]/1000
                    couponDataUrl += "&start=" + this.times[0]/1000;
                    couponDataUrl += "&end=" + this.times[1]/1000
                }
                // console.log(that.search_form)
                this.$confirm('确认导出? (提示: 导出数据为当前搜索条件后的数据)', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(async () => {
                    let res = await this.$http.get(couponDataUrl)
                    let couponDataCount = res.body.data.list.data.length ? res.body.data.list.data.length : 0
                    if (couponDataCount > 0){
                        this.$message({
                            type: 'success',
                            message: '导出中~, 请稍后!'
                        });
                        window.location.href = url;
                    } else {
                        this.$message({
                            type: 'warning',
                            message: '数据为空, 无法执行导出, 请确认搜索条件是否有数据.'
                        });
                    }
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消导出'
                    });
                });
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
            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },
            add0(m) {
                return m<10?'0'+m:m
            },
            timeStyle(time) {
                let time1 = new Date(time*1000);
                let y = time1.getFullYear();
                let m = time1.getMonth()+1;
                let d = time1.getDate();
                let h = time1.getHours();
                let mm = time1.getMinutes();
                let s = time1.getSeconds();
                return y+'-'+this.add0(m)+'-'+this.add0(d)+' '+this.add0(h)+':'+this.add0(mm)+':'+this.add0(s);
            },
            
        },
    })
</script>
@endsection